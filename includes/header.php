<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/seo.php';
require_once __DIR__ . '/../config/db.php';
send_security_headers();
$csrf_token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

<script>
window.WAQT_CSRF_TOKEN = "<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>";

// Lucide Deferred Execution Shim (prevents inline script ReferenceError crashes)
window.lucide = window.lucide || {
    createIcons: function() {
        document.addEventListener("DOMContentLoaded", function() {
            if (window.lucide && typeof window.lucide.createIcons === 'function' && window.lucide.createIcons !== arguments.callee) {
                window.lucide.createIcons();
            }
        });
    }
};
</script>

<?php
// Path Prefix Support (for subdirectories)
$prefix = isset($path_prefix) ? $path_prefix : "";

if (!function_exists('get_asset_version')) {
    function get_asset_version($file_path) {
        $full_path = __DIR__ . '/../' . $file_path;
        if (file_exists($full_path)) {
            return filemtime($full_path);
        }
        return time();
    }
}

// Global Default Meta Values
$site_title = "Waqt Finance - Instant Loans Online in India";
$site_desc = "Waqt Finance is a registered NBFC offering personal loans, business loans, payday loans, EDI loans, vehicle loans and loan against property services in India.";

// Dynamic Assignments
$final_title = isset($page_title) ? $page_title : $site_title;
$final_desc = isset($meta_description) ? $meta_description : $site_desc;
$final_keywords = isset($meta_keywords) ? $meta_keywords : "loan in India, personal loan online, business loan online, instant payday loan, NBFC loan, quick loan approval";

// Canonical URL Logic
$base_url = waqt_seo_base_url();
$current_page_name = basename($_SERVER['PHP_SELF']);
$current_slug = preg_replace('/\.php$/i', '', $current_page_name) ?? $current_page_name;
$final_canonical = isset($canonical_url) ? waqt_seo_url((string) $canonical_url) : waqt_seo_url($current_page_name == 'index.php' ? '' : $current_slug);

$utility_noindex_pages = [
    'aadhar-varification.php',
    'application-status.php',
    'bank-details.php',
    'basic-details-form.php',
    'company-details.php',
    'customer-dashboard.php',
    'customer-login.php',
    'mobile-otp.php',
    'pan-verification.php',
    'reference-details.php',
    'salary-slip-upload.php',
    'thanks.php',
    'test.php',
];
$final_robots = isset($meta_robots)
    ? (string) $meta_robots
    : (in_array($current_page_name, $utility_noindex_pages, true) ? 'noindex, nofollow' : 'index, follow, max-image-preview:large');

$default_meta_image = waqt_seo_url("assets/images/logo1.webp");
$final_meta_image = isset($meta_image) ? waqt_seo_url((string) $meta_image) : $default_meta_image;
$final_meta_image_alt = isset($meta_image_alt) ? (string) $meta_image_alt : "Waqt Finance loan services in India";

$breadcrumb_label = isset($breadcrumb_label)
    ? (string) $breadcrumb_label
    : trim((string) preg_replace('/\s+\|.*$/', '', $final_title));
$default_breadcrumb_items = [
    ['name' => 'Home', 'url' => ''],
    ['name' => $breadcrumb_label !== '' ? $breadcrumb_label : 'Current Page', 'url' => $final_canonical],
];
$final_breadcrumb_items = isset($breadcrumb_items) && is_array($breadcrumb_items)
    ? $breadcrumb_items
    : ($current_page_name === 'index.php' ? [['name' => 'Home', 'url' => '']] : $default_breadcrumb_items);

$waqt_schema_items = [];
if (!isset($disable_default_schema) || !$disable_default_schema) {
    $waqt_schema_items[] = waqt_seo_organization_schema();
    $waqt_schema_items[] = waqt_seo_website_schema();
}
if (!isset($disable_breadcrumb_schema) || !$disable_breadcrumb_schema) {
    $waqt_schema_items[] = waqt_seo_breadcrumb_schema($final_breadcrumb_items);
}
if (isset($seo_schema) && is_array($seo_schema)) {
    if (waqt_seo_is_list($seo_schema)) {
        foreach ($seo_schema as $schema_item) {
            if (is_array($schema_item)) {
                $waqt_schema_items[] = $schema_item;
            }
        }
    } else {
        $waqt_schema_items[] = $seo_schema;
    }
}
?>

<title><?php echo waqt_e($final_title); ?></title>
<meta name="description" content="<?php echo waqt_e($final_desc); ?>">
<meta name="keywords" content="<?php echo waqt_e($final_keywords); ?>">
<meta name="robots" content="<?php echo waqt_e($final_robots); ?>">
<meta name="author" content="Waqt Finance">
<meta name="geo.region" content="IN">
<meta name="geo.placename" content="India">
<meta name="theme-color" content="#062b5d">

<!-- SEO Tags -->
<?php if (stripos($final_robots, 'noindex') === false): ?>
<link rel="canonical" href="<?php echo waqt_e($final_canonical); ?>">
<?php endif; ?>
<meta property="og:site_name" content="Waqt Finance">
<meta property="og:type" content="website">
<meta property="og:locale" content="en_IN">
<meta property="og:title" content="<?php echo waqt_e($final_title); ?>">
<meta property="og:description" content="<?php echo waqt_e($final_desc); ?>">
<meta property="og:url" content="<?php echo waqt_e($final_canonical); ?>">
<meta property="og:image" content="<?php echo htmlspecialchars($final_meta_image, ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?php echo waqt_e($final_meta_image_alt); ?>">

<!-- Twitter Card Tags -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo waqt_e($final_title); ?>">
<meta name="twitter:description" content="<?php echo waqt_e($final_desc); ?>">
<meta name="twitter:image" content="<?php echo htmlspecialchars($final_meta_image, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="twitter:image:alt" content="<?php echo waqt_e($final_meta_image_alt); ?>">

<?php
if ($waqt_schema_items !== []) {
    echo waqt_seo_json_ld_script(waqt_seo_schema_graph($waqt_schema_items));
}
?>

<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $prefix; ?>assets/images/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $prefix; ?>assets/images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $prefix; ?>assets/images/favicon-16x16.png">
<link rel="icon" type="image/x-icon"  href="<?php echo $prefix; ?>assets/images/favicon.ico" >

<!-- DNS Prefetch & Preconnect for Performance -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdnjs.cloudflare.com">
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link rel="dns-prefetch" href="https://fonts.googleapis.com">
<link rel="dns-prefetch" href="https://fonts.gstatic.com">
<link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
<link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-WR2X2FZ8');</script>
<!-- End Google Tag Manager -->

<?php
// Dynamic asset router for performance optimization
$current_page_name = basename($_SERVER['PHP_SELF']);

$load_slick = false;
$load_loans = false;
$load_intl_tel = false;
$load_apply = false;
$load_calc = false;
$load_guide = false;
$load_odometer = false;
$load_lucide = false;
$load_aos = false;
$load_sweetalert = false;

// 1. Pages using Slick slider
$slick_pages = ['index.php', 'personal-loan.php', 'business-loan.php', 'vehicle-loan.php', 'loan-against-property.php', 'pay-day-loan.php', 'edi-loan.php', 'shopkeeper-business-loan.php', 'partner.php', 'about-us.php'];
if (in_array($current_page_name, $slick_pages, true)) {
    $load_slick = true;
}

// 2. Product landing pages using loans.css / loan.css
$loan_landing_pages = ['personal-loan.php', 'business-loan.php', 'vehicle-loan.php', 'loan-against-property.php', 'pay-day-loan.php', 'edi-loan.php', 'shopkeeper-business-loan.php', 'product-city-landing.php', 'location-detail.php'];
if (in_array($current_page_name, $loan_landing_pages, true)) {
    $load_loans = true;
}

// 3. Application wizard forms using apply.css / intlTelInput.css
$apply_wizard_pages = ['apply.php', 'basic-details-form.php', 'company-details.php', 'reference-details.php', 'bank-details.php', 'salary-slip-upload.php', 'pan-verification.php', 'aadhar-varification.php', 'mobile-otp.php'];
if (in_array($current_page_name, $apply_wizard_pages, true) || strpos($current_page_name, '-apply') !== false) {
    $load_apply = true;
    $load_intl_tel = true;
    $load_sweetalert = true;
}

// 4. Contact page
if ($current_page_name === 'contact-us.php') {
    $load_intl_tel = true;
    $load_sweetalert = true;
}

// 5. Calculators
if ($current_page_name === 'emi-calculator.php' || $current_page_name === 'eligibility-calculator.php') {
    $load_calc = true;
}

// 6. Guides
if (strpos($current_page_name, 'guide-') !== false || $current_page_name === 'business-loan-guide.php' || $current_page_name === 'car-loan-guide.php' || $current_page_name === 'commercial-vehicle-loan-guide.php') {
    $load_guide = true;
}

// 7. Counter Odometer
if ($current_page_name === 'index.php' || $current_page_name === 'partner.php') {
    $load_odometer = true;
}

// 8. Lucide icons
$load_lucide = true;

// 9. AOS animation
$aos_pages = ['index.php', 'personal-loan.php', 'business-loan.php', 'vehicle-loan.php', 'loan-against-property.php', 'pay-day-loan.php', 'edi-loan.php', 'partner.php', 'about-us.php', 'shopkeeper-business-loan.php'];
if (in_array($current_page_name, $aos_pages, true)) {
    $load_aos = true;
}

// 10. SweetAlert
$sweet_pages = ['index.php', 'contact-us.php', 'pay-online.php', 'repay.php', 'customer-dashboard.php'];
if (in_array($current_page_name, $sweet_pages, true)) {
    $load_sweetalert = true;
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'" />

<?php if ($load_odometer): ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/odometer.js/0.4.8/themes/odometer-theme-default.min.css" media="print" onload="this.media='all'">
<?php endif; ?>

<?php if ($load_sweetalert): ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" media="print" onload="this.media='all'">
<?php endif; ?>

<?php if ($load_aos): ?>
<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet" media="print" onload="this.media='all'">
<?php endif; ?>

<link rel="stylesheet" href="<?php echo $prefix; ?>assets/css/bootstrap.min.css">

<?php if ($load_slick): ?>
<link rel="stylesheet" href="<?php echo $prefix; ?>assets/css/slick.css" media="print" onload="this.media='all'">
<link rel="stylesheet" href="<?php echo $prefix; ?>assets/css/slick-theme.css" media="print" onload="this.media='all'">
<?php endif; ?>


<link rel="stylesheet" href="<?php echo $prefix; ?>assets/css/style.css?v=<?php echo get_asset_version('assets/css/style.css'); ?>">
<link rel="stylesheet" href="<?php echo $prefix; ?>assets/css/responsive.css?v=<?php echo get_asset_version('assets/css/responsive.css'); ?>">

<?php if ($load_loans): ?>
<link rel="stylesheet" href="<?php echo $prefix; ?>assets/css/loans.css?v=<?php echo get_asset_version('assets/css/loans.css'); ?>">
<link rel="stylesheet" href="<?php echo $prefix; ?>assets/css/loan.css?v=<?php echo get_asset_version('assets/css/loan.css'); ?>">
<?php endif; ?>

<?php if ($current_page_name === 'about-us.php'): ?>
<link rel="stylesheet" href="<?php echo $prefix; ?>assets/css/about-us.css?v=<?php echo get_asset_version('assets/css/about-us.css'); ?>">
<?php endif; ?>

<?php if ($load_intl_tel): ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css" media="print" onload="this.media='all'"/>
<?php endif; ?>

<!-- Combined & Optimized Google Fonts Loader (Fewer HTTP Requests, Defer Block) -->
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<?php if ($load_apply): ?>
<link rel="stylesheet" href="<?php echo $prefix; ?>assets/css/apply.css?v=<?php echo get_asset_version('assets/css/apply.css'); ?>">
<?php endif; ?>

<?php if ($load_calc): ?>
<link rel="stylesheet" href="<?php echo $prefix; ?>assets/css/calculator.css?v=<?php echo get_asset_version('assets/css/calculator.css'); ?>">
<?php endif; ?>

<?php if ($load_guide): ?>
<link rel="stylesheet" href="<?php echo $prefix; ?>assets/css/guide-to-home-loan.css?v=<?php echo get_asset_version('assets/css/guide-to-home-loan.css'); ?>">
<?php endif; ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" media="print" onload="this.media='all'">

<?php if ($load_lucide): ?>
<script src="https://unpkg.com/lucide@latest" defer></script>
<?php endif; ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-CYRLNY3X45"></script>

<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());

gtag('config', 'G-CYRLNY3X45');
</script>
<style>
    body, html, h1, h2, h3, h4, h5, h6, p, a, span, button, input, select, textarea {
        font-family: 'Inter', sans-serif !important;
    }
    .fa, .fas, .far, .fab, .fa-solid, .fa-regular, .fa-brands {
        font-family: 'Font Awesome 6 Free', 'Font Awesome 6 Brands', 'Font Awesome 5 Free', 'Font Awesome 5 Brands', 'Font Awesome' !important;
    }
</style>
<?php if (isset($extra_head)) echo $extra_head; ?>
</head>

<?php
$is_apply_page = false;
if (isset($body_class) && strpos($body_class, 'wf-apply-body-active') !== false) {
    $is_apply_page = true;
} elseif (strpos($_SERVER['PHP_SELF'], 'apply') !== false || strpos($_SERVER['REQUEST_URI'], 'apply') !== false) {
    $is_apply_page = true;
}
?>
<body<?php if ($is_apply_page) echo ' class="wf-apply-body-active"'; ?>>
    <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WR2X2FZ8"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
