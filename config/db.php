<?php
declare(strict_types=1);

// Production Environment Error Hardening
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

require_once __DIR__ . '/../includes/security.php';
if (!defined('WAQT_SKIP_SESSION') || WAQT_SKIP_SESSION !== true) {
    start_secure_session();
}
date_default_timezone_set('Asia/Kolkata');

if (!function_exists('load_env_file')) {
    function load_env_file(string $path): void
    {
        if (!is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, "\"'");

           if ($key !== '') {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
            }
        }
    }
}

load_env_file(__DIR__ . '/../.env');

if (!function_exists('env_value')) {
    function env_value(string $key, ?string $default = null): ?string
    {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }
}

if (!function_exists('required_env')) {
    function required_env(string $key): string
    {
        $value = env_value($key);
        if ($value === null || $value === '') {
            throw new RuntimeException('Missing required environment variable: ' . $key);
        }

        return $value;
    }
}

if (!defined('SYNC_API_KEY')) {
    define('SYNC_API_KEY', env_value('SYNC_API_KEY', '') ?? '');
}

if (!function_exists('db')) {
    function db(): mysqli
    {
        static $connection = null;

        if ($connection instanceof mysqli) {
            return $connection;
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $host = env_value('DB_HOST', 'localhost') ?? 'localhost';
        $user = env_value('DB_USER', 'root') ?? 'root';
        $password = env_value('DB_PASS', '') ?? '';
        $database = env_value('DB_NAME', 'waqt-finance') ?? 'waqt-finance';
        $port = (int) (env_value('DB_PORT', '3306') ?? '3306');

        try {
            $connection = new mysqli($host, $user, $password, $database, $port);
        } catch (Throwable $e) {
            if ($host === 'localhost' || $host === '127.0.0.1') {
                try {
                    $connection = new mysqli('127.0.0.1', 'root', '', 'waqtmoney_payday', 3306);
                } catch (Throwable $fallbackErr) {
                    throw $e;
                }
            } else {
                throw $e;
            }
        }
        $connection->set_charset('utf8mb4');
        $connection->query("SET time_zone = '+05:30'");

        return $connection;
    }
}

if (!function_exists('json_response')) {
    function json_response(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('require_post')) {
    function require_post(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            json_response(['status' => 'error', 'message' => 'Invalid request method.'], 405);
        }
    }
}

if (!function_exists('clean_name')) {
    function clean_name(string $value): string
    {
        return preg_replace('/\s+/', ' ', trim($value)) ?? '';
    }
}

if (!function_exists('normalize_email')) {
    function normalize_email(string $value): string
    {
        return strtolower(trim($value));
    }
}

if (!function_exists('otp_digest')) {
    function otp_digest(string $otp): string
    {
        return hash_hmac('sha256', $otp, required_env('OTP_HASH_SECRET'));
    }
}

if (!function_exists('report_exception')) {
    function report_exception(Throwable $e): void
    {
        error_log($e->getMessage());
        json_response([
            'status' => 'error',
            'message' => 'Server error. Please try again later.'
        ], 500);
    }
}

if (session_status() === PHP_SESSION_ACTIVE && empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function sendWhatsAppOTP($mobile, $otp)
{
    $authkey = required_env('AUTH_KEY');



    // ✅ YOUR TEMPLATE WID (IMPORTANT)
    $wid = "32517"; // screenshot se confirm

    // ✅ Build URL
    $url = "https://api.authkey.io/request?" . http_build_query([
        "authkey" => $authkey,
        "mobile" => $mobile,
        "country_code" => "91",
        "wid" => $wid,
        "1" => $otp // variable {{1}}
    ]);

    // 🔥 API Call
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log("WA CURL ERROR: " . curl_error($ch));
    }

    curl_close($ch);

    return $response;
}

try {
    $conn = db();
} catch (Throwable $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $isLocal = in_array($host, ['localhost', '127.0.0.1'], true) || str_contains($host, 'localhost');
    if (!$isLocal) {
        if (PHP_SAPI === 'cli') {
            throw $e;
        }
        http_response_code(500);
        die('Server error. Please try again later.');
    }
}

if (!function_exists('waqt_generate_sitemap_file')) {
    function waqt_generate_sitemap_file(): bool
    {
        $base_url = "https://waqtfinance.com/";
        $current_date = date('c');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
        $xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

        $static_pages = [
            "" => "1.00",
            "loan-in-delhi-ncr" => "0.90",
            "loan-in-delhi" => "0.80",
            "loan-in-noida" => "0.80",
            "loan-in-gurugram" => "0.80",
            "about-us" => "0.80",
            "contact-us" => "0.80",
            "products" => "0.80",
            "faqs" => "0.80",
            "repay" => "0.80",
            "policies" => "0.80",
            "partner" => "0.80",
            "pay-online" => "0.80",
            "free-credit-score" => "0.80",
            "emi-calculator" => "0.80",
            "edi-loan" => "0.80",
            "pay-day-loan" => "0.80",
            "personal-loan" => "0.80",
            "business-loan" => "0.80",
            "vehicle-loan" => "0.80",
            "shopkeeper-business-loan" => "0.80",
            "blog" => "0.80",
            "grievance-redressal-policy" => "0.70",
            "fair-practice-code" => "0.70",
            "interest-rate-policy" => "0.70",
            "privacy-policy" => "0.70",
            "terms-condition" => "0.70",
            "refund-cancellation" => "0.70",
            "anti-money-laundering" => "0.70"
        ];

        foreach ($static_pages as $path => $priority) {
            $file = $path === '' ? 'index.php' : $path . '.php';
            $full_path = __DIR__ . '/../' . $file;
            $lastmod = is_file($full_path) ? date('c', filemtime($full_path)) : $current_date;

            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($base_url . $path, ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>" . $lastmod . "</lastmod>\n";
            $xml .= "    <priority>" . $priority . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $sitemap_products = ['personal-loan', 'business-loan', 'pay-day-loan', 'edi-loan', 'vehicle-loan', 'loan-against-property'];
        $sitemap_cities = ['delhi-ncr', 'delhi', 'noida', 'gurugram'];
        $template_path = __DIR__ . '/../includes/location-page-template.php';
        $dynamic_lastmod = is_file($template_path) ? date('c', filemtime($template_path)) : $current_date;

        foreach ($sitemap_products as $p_key) {
            foreach ($sitemap_cities as $c_key) {
                $path = $p_key . "-in-" . $c_key;
                $xml .= "  <url>\n";
                $xml .= "    <loc>" . htmlspecialchars($base_url . $path, ENT_XML1, 'UTF-8') . "</loc>\n";
                $xml .= "    <lastmod>" . $dynamic_lastmod . "</lastmod>\n";
                $xml .= "    <priority>0.80</priority>\n";
                $xml .= "  </url>\n";
            }
        }

        try {
            $conn = db();
            $res = $conn->query("SELECT slug, updated_at FROM blogs WHERE status = 'published' ORDER BY updated_at DESC");
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $slug = $row['slug'];
                    $mod_date = !empty($row['updated_at']) ? date('c', strtotime($row['updated_at'])) : $current_date;
                    
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>" . htmlspecialchars($base_url . "blog/" . $slug, ENT_XML1, 'UTF-8') . "</loc>\n";
                    $xml .= "    <lastmod>" . $mod_date . "</lastmod>\n";
                    $xml .= "    <priority>0.64</priority>\n";
                    $xml .= "  </url>\n";
                }
            }
        } catch (Exception $e) {
            // Fail silently
        }

        $xml .= "</urlset>\n";

        return (bool) file_put_contents(__DIR__ . '/../sitemap.xml', $xml);
    }
}
