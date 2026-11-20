<?php
declare(strict_types=1);

$product_key = trim((string) ($_GET['product'] ?? ''));
$city_key = trim((string) ($_GET['city'] ?? ''));

// Product content: Admin > Location CMS > Product content (defaults in config/location-product-templates.php).
// City: Admin > Location CMS. Unknown products, inactive products and unpublished cities return 404.
require_once __DIR__ . '/includes/location_data.php';
if (session_status() === PHP_SESSION_NONE && function_exists('start_secure_session')) {
    start_secure_session();
}
$isAdminPreview = !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$products = waqt_location_product_templates();
$location = waqt_get_location($city_key, $isAdminPreview);
if (!isset($products[$product_key]) || !$location) {
    http_response_code(404);
    include '404.php';
    exit;
}

$prod = $products[$product_key];
$prod['faqs'] = is_array($prod['faqs'] ?? null) ? $prod['faqs'] : [];
foreach (['features', 'eligibility', 'documents', 'common_mistakes'] as $listKey) {
    $prod[$listKey] = is_array($prod[$listKey] ?? null) ? $prod[$listKey] : [];
}
$prod['choice_scenarios'] = is_array($prod['choice_scenarios'] ?? null) ? $prod['choice_scenarios'] + ['title' => '', 'items' => []] : ['title' => '', 'items' => []];
$prod['benefits_vs_risks'] = is_array($prod['benefits_vs_risks'] ?? null) ? $prod['benefits_vs_risks'] + ['benefits' => [], 'risks' => []] : ['benefits' => [], 'risks' => []];

$city = [
    'name' => $location['name'],
    'type' => $location['type'] === 'region' ? 'AdministrativeArea' : 'City',
    'areas' => $location['areas'] ?: [$location['name']],
    'address' => $location['address'],
    'phone' => $location['phone'],
    'is_office' => $location['type'] === 'office' && $location['address'] !== '',
    'product_content' => $location['product_content'] ? json_encode($location['product_content']) : null,
];
$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
if ($location['status'] !== 'published') {
    $meta_robots = 'noindex, nofollow';
}

// Default page meta and heading content
$range_text = $prod['range_text'] ?: ("loans from " . $prod['min_amount']);
$page_title = "{$prod['name']} in {$city['name']} | Apply Online | Waqt Finance";
if (mb_strlen($page_title) > 60) {
    $page_title = "{$prod['name']} in {$city['name']} | Waqt Finance";
}
if (mb_strlen($page_title) > 60) {
    $page_title = "{$prod['name']} in {$city['name']}";
}
// Meta description: factual and within 160 characters (no approval-speed promises).
$waqt_pcl_meta = static function (array $prod, array $city, string $range_text): string {
    $parts = [
        "Apply online for a {$prod['name']} in {$city['name']} with Waqt Finance.",
        rtrim(ucfirst($range_text), '.') . ($prod['tenure'] !== '' ? ", tenure {$prod['tenure']}." : '.'),
        "Consent-based checks and clear terms before you accept.",
    ];
    $out = '';
    foreach ($parts as $part) {
        $next = trim($out . ' ' . $part);
        if (mb_strlen($next) > 160) break;
        $out = $next;
    }
    return $out !== '' ? $out : mb_substr($parts[0], 0, 160);
};
$meta_description = $waqt_pcl_meta($prod, $city, $range_text);
$meta_keywords = "{$product_key} in {$city_key}, {$prod['name']} {$city['name']}, online loan {$city['name']}, {$product_key} online {$city['name']}";
$page_heading = "{$prod['name']} in {$city['name']}";
$page_description_paragraph = "Apply online with Waqt Finance for " . strtolower($prod['name']) . " options in {$city['name']}. We support borrowers with a digital application process, consent-based checks, document review, and clear terms before acceptance.";

// Merge/Override content if database overrides exist
if (!empty($city['product_content'])) {
    $overrides = json_decode((string) $city['product_content'], true);
    if (is_array($overrides) && isset($overrides[$product_key])) {
        $p_overrides = $overrides[$product_key];
        
        // 1. Structural overrides first (so they flow into defaults if specific text overrides are empty)
        if (!empty($p_overrides['min_amount'])) {
            $prod['min_amount'] = $p_overrides['min_amount'];
        }
        if (!empty($p_overrides['tenure'])) {
            $prod['tenure'] = $p_overrides['tenure'];
        }
        if (!empty($p_overrides['interest'])) {
            $prod['interest'] = $p_overrides['interest'];
            if (preg_match('/(\d+(\.\d+)?)/', $p_overrides['interest'], $m)) {
                $prod['default_rate'] = floatval($m[1]);
            }
        }
        if (!empty($p_overrides['faqs']) && is_array($p_overrides['faqs'])) {
            $prod['faqs'] = $p_overrides['faqs'];
        }

        // 2. Text overrides
        if (!empty($p_overrides['title'])) {
            $page_heading = $p_overrides['title'];
        }
                if (!empty($p_overrides['meta_title']) && strlen(trim((string) $p_overrides['meta_title'])) <= 65) {
            $page_title = $p_overrides['meta_title'];
        }
        if (!empty($p_overrides['meta_description']) && strlen(trim((string) $p_overrides['meta_description'])) <= 160 && stripos((string) $p_overrides['meta_description'], 'Serving customers in') === false) {
            $meta_description = $p_overrides['meta_description'];
        } else {
            // Recompute with overridden min_amount and tenure
            $meta_description = $waqt_pcl_meta($prod, $city, $range_text);
        }
        if (!empty($p_overrides['description'])) {
            $page_description_paragraph = $p_overrides['description'];
        }
    }
}

$canonical_url = "https://waqtfinance.com/{$product_key}-in-{$city_key}";
$meta_image = is_file(__DIR__ . "/assets/images/og-{$product_key}.jpg") ? "assets/images/og-{$product_key}.jpg" : "assets/images/og-default.jpg";
$meta_image_alt = "{$prod['name']} in {$city['name']} by Waqt Finance";
$breadcrumb_label = "{$prod['name']} in {$city['name']}";
$breadcrumb_items = [
    ['name' => 'Home', 'url' => ''],
    ['name' => 'Locations', 'url' => 'locations'],
    ['name' => 'Loans in ' . $city['name'], 'url' => 'loan-in-' . $city_key],
    ['name' => $prod['name'], 'url' => $product_key . '-in-' . $city_key],
];
$product_page = is_file(__DIR__ . '/' . $product_key . '.php') ? $product_key : '';

// FAQs shown on the page (and in FAQPage schema): product FAQs, this city's own FAQs, then local help.
$display_faqs = [];
foreach (array_merge($prod['faqs'], $location['faqs'] ?? []) as $faq) {
    if (is_array($faq) && trim((string) ($faq['q'] ?? '')) !== '' && trim((string) ($faq['a'] ?? '')) !== '') {
        $display_faqs[] = ['q' => (string) $faq['q'], 'a' => (string) $faq['a']];
    }
}
if (count($city['areas']) > 1) {
    $display_faqs[] = [
        'q' => "Which areas of {$city['name']} can apply for a " . strtolower($prod['name']) . "?",
        'a' => "Applicants from " . implode(', ', $city['areas']) . " and nearby areas of {$city['name']} can apply online. Eligibility depends on your documents and our credit policy, not only on your locality.",
    ];
}
$site_contact = waqt_location_site_defaults();
$display_faqs[] = [
    'q' => "How can I get help in {$city['name']}?",
    'a' => $city['is_office']
        ? "Our office is at {$city['address']}. Please call {$city['phone']} before visiting."
        : "Applications from {$city['name']} are handled online. Call {$city['phone']} or email {$site_contact['email']}, Monday to Saturday, 10:00 AM to 6:00 PM.",
];
$prod['faqs'] = $display_faqs;

$area_served = [["@type" => $city['type'], "name" => $city['name']]];
foreach ($city['areas'] as $area) {
    if ($area !== $city['name']) {
        $area_served[] = ["@type" => "Place", "name" => $area];
    }
}
$loan_schema = [
    "@context" => "https://schema.org",
    "@type" => "LoanOrCredit",
    "@id" => $canonical_url . "#loan",
    "name" => "{$prod['name']} in {$city['name']}",
    "url" => $canonical_url,
    "description" => $meta_description,
    "provider" => ["@id" => "https://waqtfinance.com/#organization"],
    "areaServed" => $area_served,
    "currency" => "INR",
];
if ((int) ($prod['max_amount_val'] ?? 0) > 0) {
    $loan_schema["amount"] = ["@type" => "MonetaryAmount", "currency" => "INR", "maxValue" => (int) $prod['max_amount_val']];
    $min_numeric = (int) preg_replace('/\D/', '', (string) $prod['min_amount']);
    if ($min_numeric >= 1000 && $min_numeric < (int) $prod['max_amount_val']) {
        $loan_schema["amount"]["minValue"] = $min_numeric;
    }
}
$seo_schema = [
    [
        "@context" => "https://schema.org",
        "@type" => "Service",
        "@id" => $canonical_url . "#service",
        "name" => "{$prod['name']} in {$city['name']}",
        "url" => $canonical_url,
        "description" => $meta_description,
        "provider" => [
            "@id" => "https://waqtfinance.com/#organization"
        ],
        "areaServed" => $area_served,
        "serviceType" => [
            $prod['name']
        ]
    ],
    $loan_schema,
];

// Build & Append FAQ Schema if FAQs exist for improved Google Rich Snippets ranking
if (!empty($prod['faqs']) && is_array($prod['faqs'])) {
    $faq_entities = [];
    foreach ($prod['faqs'] as $faq) {
        if (!empty($faq['q']) && !empty($faq['a'])) {
            $faq_entities[] = [
                "@type" => "Question",
                "name" => $faq['q'],
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => strip_tags((string) $faq['a'])
                ]
            ];
        }
    }
    if (!empty($faq_entities)) {
        $seo_schema[] = [
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => $faq_entities
        ];
    }
}

$extra_head = <<<HTML
<style>
.wf-local-breadcrumb{display:flex;flex-wrap:wrap;justify-content:center;gap:8px;font-size:13px;color:#c9d9ea;margin-bottom:14px}
.wf-local-breadcrumb a{color:#fff;text-decoration:none}
.wf-local-breadcrumb a:hover{text-decoration:underline}
.wf-local-related h2{text-align:center;margin-bottom:22px}
.wf-local-related-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,320px),1fr));gap:20px;max-width:1000px;margin:0 auto}
.wf-local-related-grid>div{background:#fff;border:1px solid #e3eaf3;border-radius:14px;padding:20px 22px}
.wf-local-related-grid h3{font-size:17px;font-weight:800;color:#0b213a;margin:0 0 12px}
.wf-local-related-links{display:flex;flex-wrap:wrap;gap:8px}
.wf-local-related-links a{display:inline-flex;align-items:center;gap:6px;padding:8px 12px;border:1px solid #d4e4f5;border-radius:999px;background:#f8fbff;color:#18529c;font-size:14px;font-weight:700;text-decoration:none}
.wf-local-related-links a:hover{border-color:#18529c}
.wf-local-related-links a.is-hub{background:#18529c;color:#fff;border-color:#18529c}
    :root {
        --wf-primary: #062b5d;
        --wf-secondary: #18529c;
        --wf-accent: #dc2626;
        --wf-slate-50: #f8fafc;
        --wf-slate-100: #f1f5f9;
        --wf-slate-200: #e2e8f0;
        --wf-slate-600: #475569;
        --wf-slate-800: #1e293b;
        --wf-slate-900: #0f172a;
    }
    
    .wf-local-hero {
        background: radial-gradient(circle at 50% 50%, #062b5d 0%, #031530 100%);
        color: #fff;
        padding: 85px 0 75px;
        text-align: center;
    }
    .wf-local-hero-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        max-width: 800px;
        margin: 0 auto;
    }
    .wf-local-hero h1 {
        color: #fff;
        font-size: clamp(34px, 5vw, 54px);
        font-weight: 800;
        line-height: 1.15;
        margin-bottom: 20px;
        letter-spacing: -0.5px;
        text-align: center;
    }
    .wf-local-hero p {
        color: rgba(255, 255, 255, 0.9);
        font-size: 18px;
        line-height: 1.75;
        max-width: 720px;
        margin-bottom: 30px;
        text-align: center;
    }
    
    .wf-local-section {
        padding: 55px 0;
    }
    .wf-local-section.wf-local-band {
        background: var(--wf-slate-50);
        border-top: 1px solid var(--wf-slate-100);
        border-bottom: 1px solid var(--wf-slate-100);
    }
    .wf-local-section h2 {
        color: var(--wf-primary);
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 12px;
        letter-spacing: -0.5px;
        text-align: center;
    }
    .wf-local-sub {
        color: var(--wf-slate-600);
        max-width: 760px;
        line-height: 1.7;
        margin: 0 auto 35px auto;
        text-align: center;
        font-size: 16px;
    }
    
    /* Bento Structure */
    .wf-product-bento {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
        max-width: 960px;
        margin: 0 auto;
    }
    .wf-product-bento-card {
        background: #fff;
        border: 1px solid var(--wf-slate-200);
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .wf-product-bento-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.05);
    }
    .wf-product-bento-card strong {
        color: var(--wf-slate-600);
        display: block;
        font-size: 13px;
        text-transform: uppercase;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
        text-align: center;
    }
    .wf-product-bento-card span {
        font-size: 22px;
        font-weight: 800;
        color: var(--wf-secondary);
        text-align: center;
    }
    
    .wf-local-btn {
        background: #fff;
        color: var(--wf-primary) !important;
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 800;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .wf-local-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    }
    
    /* Interactive Tabs Section */
    .wf-tabs-container {
        max-width: 800px;
        margin: 0 auto;
        background: #fff;
        border: 1px solid var(--wf-slate-200);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03);
    }
    .wf-tabs-nav {
        display: flex;
        background: var(--wf-slate-50);
        border-bottom: 1px solid var(--wf-slate-200);
    }
    .wf-tab-btn {
        flex: 1;
        padding: 16px;
        border: none;
        background: transparent;
        font-size: 15px;
        font-weight: 700;
        color: var(--wf-slate-600);
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
    }
    .wf-tab-btn.active {
        color: var(--wf-secondary);
        background: #fff;
        box-shadow: inset 0 -3px 0 var(--wf-secondary);
    }
    .wf-tabs-content {
        padding: 30px;
    }
    .wf-tab-pane {
        display: none;
    }
    .wf-tab-pane.active {
        display: block;
    }
    .wf-checklist {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .wf-checklist li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 15px;
        color: var(--wf-slate-800);
        line-height: 1.4;
        text-align: left;
    }
    .wf-checklist li i {
        color: #22c55e;
        margin-top: 3px;
        font-size: 16px;
    }
    
    /* EMI Calculator Widget */
    .wf-calc-widget {
        max-width: 960px;
        margin: 0 auto;
        background: #fff;
        border: 1px solid var(--wf-slate-200);
        border-radius: 20px;
        padding: 35px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 40px;
    }
    .wf-calc-sliders {
        display: flex;
        flex-direction: column;
        gap: 28px;
        text-align: left;
    }
    .wf-slider-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .wf-slider-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 700;
        color: var(--wf-slate-800);
        font-size: 15px;
        margin: 0;
    }
    .wf-slider-header span {
        color: var(--wf-secondary);
        font-size: 18px;
        font-weight: 800;
    }
    .wf-slider-input {
        width: 100%;
        height: 6px;
        background: var(--wf-slate-200);
        border-radius: 5px;
        outline: none;
        -webkit-appearance: none;
    }
    .wf-slider-input::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--wf-secondary);
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        transition: transform 0.1s;
    }
    .wf-slider-input::-webkit-slider-thumb:hover {
        transform: scale(1.15);
    }
    .wf-calc-results {
        background: var(--wf-slate-50);
        border-radius: 16px;
        padding: 28px;
        border: 1px solid var(--wf-slate-200);
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: center;
        gap: 20px;
    }
    .wf-calc-row {
        display: flex;
        flex-direction: column;
        gap: 4px;
        text-align: center;
    }
    .wf-calc-row strong {
        font-size: 13px;
        text-transform: uppercase;
        color: var(--wf-slate-600);
        letter-spacing: 0.5px;
        text-align: center;
    }
    .wf-calc-row span {
        font-size: 26px;
        font-weight: 800;
        color: var(--wf-primary);
        text-align: center;
    }
    
    /* How to Apply Stepper */
    .wf-apply-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid var(--wf-slate-200);
        box-shadow: 0 4px 30px rgba(15, 23, 42, 0.03);
        max-width: 800px;
        margin: 0 auto;
        padding: 45px 50px;
    }
    .wf-apply-timeline {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 36px;
    }
    .wf-apply-line {
        position: absolute;
        left: 12px;
        top: 10px;
        bottom: 10px;
        width: 2px;
        background: #fbcfe8;
        z-index: 1;
    }
    .wf-apply-step {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 20px;
        z-index: 2;
    }
    .wf-apply-badge {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #b91c1c;
        border: 4px solid #fbcfe8;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .wf-apply-text {
        font-size: 16px;
        color: var(--wf-slate-600);
        line-height: 1.6;
        text-align: left;
    }
    .wf-apply-text strong {
        color: var(--wf-slate-850);
        font-weight: 800;
    }
    .wf-apply-btn-wrapper {
        text-align: center;
        margin-top: 35px;
    }
    .wf-apply-now-btn {
        background: #b91c1c;
        color: #ffffff !important;
        padding: 14px 45px;
        border-radius: 99px;
        font-size: 15px;
        font-weight: 800;
        text-decoration: none !important;
        display: inline-block;
        transition: all 0.2s ease;
        box-shadow: 0 4px 15px rgba(185, 28, 28, 0.2);
    }
    .wf-apply-now-btn:hover {
        background: #991b1b;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(185, 28, 28, 0.3);
    }
    
    /* Services Two-Column Grid */
    .wf-city-services-grid {
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 40px;
        max-width: 960px;
        margin: 0 auto;
        text-align: left;
        align-items: stretch;
    }
    .wf-city-services-left {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .wf-city-services-left p {
        color: var(--wf-slate-600);
        line-height: 1.8;
        font-size: 15.5px;
        margin-bottom: 18px;
    }
    .wf-city-services-left p:last-child {
        margin-bottom: 0;
    }
    .wf-localities-serviced {
        background: #fff;
        border: 1px solid var(--wf-slate-200);
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.02);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .wf-localities-serviced h4 {
        font-size: 15px;
        font-weight: 700;
        color: var(--wf-primary);
        margin: 0 0 16px 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .wf-localities-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .wf-localities-grid span {
        font-size: 13px;
        color: var(--wf-slate-800);
        background: var(--wf-slate-50);
        padding: 6px 12px;
        border-radius: 6px;
        border: 1px solid var(--wf-slate-200);
        font-weight: 500;
    }

    /* Choice Scenarios */
    .wf-scenarios-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        max-width: 960px;
        margin: 0 auto;
    }
    .wf-scenario-card {
        background: #fff;
        border: 1px solid var(--wf-slate-200);
        border-radius: 12px;
        padding: 24px;
        text-align: left;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02);
        transition: transform 0.2s ease;
    }
    .wf-scenario-card:hover {
        transform: translateY(-2px);
    }
    .wf-scenario-icon {
        font-size: 24px;
        color: var(--wf-secondary);
        margin-bottom: 14px;
    }
    .wf-scenario-card h3 {
        font-size: 17px;
        font-weight: 700;
        color: var(--wf-primary);
        margin-bottom: 10px;
    }
    .wf-scenario-card p {
        color: var(--wf-slate-600);
        font-size: 14px;
        line-height: 1.6;
        margin: 0;
    }

    /* Comparison Table */
    .wf-comp-table-wrap {
        max-width: 960px;
        margin: 0 auto;
        overflow-x: auto;
        border-radius: 12px;
        border: 1px solid var(--wf-slate-200);
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.02);
    }
    .wf-comp-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
    }
    .wf-comp-table th {
        background: var(--wf-primary);
        color: #fff;
        padding: 16px 20px;
        font-weight: 700;
        text-align: left;
        font-size: 15px;
    }
    .wf-comp-table td {
        padding: 16px 20px;
        border-bottom: 1px solid var(--wf-slate-100);
        color: var(--wf-slate-800);
        font-size: 14.5px;
        line-height: 1.6;
        width: 50%;
        text-align: left;
    }

    /* Common Mistakes */
    .wf-mistakes-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        max-width: 960px;
        margin: 0 auto;
    }
    .wf-mistake-card {
        background: #fdf2f2;
        border-left: 4px solid var(--wf-accent);
        border-radius: 8px;
        padding: 20px;
        text-align: left;
    }
    .wf-mistake-card h4 {
        color: #991b1b;
        margin: 0 0 8px 0;
        font-size: 16px;
        font-weight: 700;
    }
    .wf-mistake-card p {
        color: var(--wf-slate-600);
        font-size: 14px;
        line-height: 1.6;
        margin: 0;
    }

    /* Accordion FAQ */
    .wf-faq-wrap {
        max-width: 800px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .wf-faq-item {
        background: #fff;
        border: 1px solid var(--wf-slate-200);
        border-radius: 10px;
        overflow: hidden;
    }
    .wf-faq-trigger {
        width: 100%;
        padding: 20px 24px;
        background: transparent;
        border: none;
        text-align: left;
        font-size: 17px;
        font-weight: 700;
        color: var(--wf-slate-900);
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }
    .wf-faq-trigger span {
        font-size: 14px;
        color: var(--wf-slate-600);
        transition: transform 0.2s ease;
    }
    .wf-faq-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.25s ease-out;
        background: var(--wf-slate-50);
        border-top: 0 solid var(--wf-slate-200);
        text-align: left;
    }
    .wf-faq-content p {
        padding: 20px 24px;
        color: var(--wf-slate-600);
        line-height: 1.6;
        font-size: 15px;
        margin: 0;
    }
    .wf-faq-item.active .wf-faq-trigger span {
        transform: rotate(180deg);
    }
    .wf-faq-item.active .wf-faq-content {
        border-top-width: 1px;
    }
    
    @media (max-width: 991px) {
        .wf-calc-widget,
        .wf-city-services-grid,
        .wf-scenarios-grid,
        .wf-mistakes-grid,
        .wf-product-bento {
            grid-template-columns: 1fr;
        }
        .wf-calc-widget {
            gap: 30px;
        }
        .wf-checklist {
            grid-template-columns: 1fr;
        }
        .wf-city-services-left {
            margin-bottom: 10px;
        }
    }
/* Brand-consistent controls (blue, not red) */
.wf-local-page input[type="range"].wf-slider-input, input[type="range"].wf-slider-input { background: #dbe5f1 !important; accent-color: #18529c; height: 6px; }
input[type="range"].wf-slider-input::-webkit-slider-thumb { background: #18529c !important; border: 3px solid #fff; }
input[type="range"].wf-slider-input::-moz-range-thumb { background: #18529c; border: 3px solid #fff; }
.wf-apply-badge { background: #18529c; border-color: #dbeafe; }
.wf-apply-line { background: #bfdbfe; }
.wf-apply-now-btn { background: #18529c; border-radius: 10px; }
.wf-apply-now-btn:hover { background: #0d3f7b; }
.wf-apply-now-btn:focus-visible, .wf-tab-btn:focus-visible, .wf-faq-trigger:focus-visible { outline: 3px solid #93c5fd; outline-offset: 2px; }
/* Hero: two columns with a key-facts card */
.wf-local-hero { padding: 56px 0 60px; text-align: left; }
.wf-pcl-hero-grid { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr); gap: 40px; align-items: center; max-width: 1140px; margin: 0 auto; }
.wf-pcl-hero-grid .wf-local-hero-content { align-items: flex-start; max-width: none; margin: 0; }
.wf-pcl-hero-grid .wf-local-hero h1, .wf-local-hero .wf-pcl-hero-grid h1 { text-align: left; font-size: clamp(30px, 4.2vw, 48px); margin-bottom: 14px; }
.wf-pcl-hero-grid .wf-local-breadcrumb { justify-content: flex-start; }
.wf-pcl-hero-grid .wf-local-description-box, .wf-pcl-hero-grid p { text-align: left; }
.wf-pcl-hero-grid .wf-local-trust-strip { justify-content: flex-start; }
.wf-pcl-hero-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 6px; }
.wf-pcl-btn-ghost { display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; border: 1px solid rgba(255,255,255,.45); border-radius: 8px; color: #fff !important; font-weight: 700; text-decoration: none !important; }
.wf-pcl-btn-ghost:hover { background: rgba(255,255,255,.1); }
.wf-local-btn:focus-visible, .wf-pcl-btn-ghost:focus-visible { outline: 3px solid #93c5fd; outline-offset: 2px; }
.wf-pcl-hero-card { background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.16); border-radius: 18px; padding: 24px; backdrop-filter: blur(6px); }
.wf-pcl-hero-card-title { color: #fff; font-size: 13px; font-weight: 800; letter-spacing: .6px; text-transform: uppercase; opacity: .85; margin: 0 0 12px; }
.wf-pcl-hero-card dl { margin: 0; display: grid; gap: 0; }
.wf-pcl-hero-card dl > div { display: grid; grid-template-columns: 120px 1fr; gap: 12px; padding: 11px 0; border-top: 1px solid rgba(255,255,255,.12); }
.wf-pcl-hero-card dl > div:first-child { border-top: 0; }
.wf-pcl-hero-card dt { color: #c9d9ea; font-size: 13.5px; font-weight: 600; }
.wf-pcl-hero-card dt i { width: 16px; color: #93c5fd; margin-right: 4px; }
.wf-pcl-hero-card dd { color: #fff; font-size: 15px; font-weight: 700; margin: 0; line-height: 1.45; }
/* style.css sets hero <p> with !important; the card text needs its own size */
body .wf-local-hero .wf-pcl-hero-card-note { color: #c9d9ea !important; font-size: 12.5px !important; line-height: 1.5 !important; margin: 12px 0 0 !important; max-width: none !important; }
body .wf-local-hero .wf-pcl-hero-card-title { color: #fff !important; font-size: 13px !important; line-height: 1.4 !important; margin: 0 0 12px !important; max-width: none !important; }
body .wf-local-hero .wf-pcl-hero-grid h1 { font-size: clamp(30px, 4.2vw, 48px) !important; }
@media (max-width: 575px) {
  body .wf-local-hero .wf-pcl-hero-grid h1 { font-size: 32px !important; }
  .wf-pcl-hero-grid .wf-local-trust-strip { flex-direction: row; align-items: flex-start; }
  .wf-pcl-hero-grid .wf-local-trust-strip span { width: auto; justify-content: flex-start; min-height: 34px; }
}
@media (max-width: 900px) {
  .wf-pcl-hero-grid { grid-template-columns: 1fr; gap: 24px; }
  .wf-local-hero { padding: 40px 0 44px; }
}
@media (max-width: 575px) {
  .wf-pcl-hide-sm { display: none !important; }
  .wf-pcl-hero-card { padding: 18px; }
  .wf-pcl-hero-card dl > div { grid-template-columns: 104px 1fr; }
  .wf-pcl-hero-actions a { flex: 1 1 100%; justify-content: center; }
}
/* City block */
.wf-pcl-scenarios { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 240px), 1fr)); gap: 16px; max-width: 1000px; margin: 0 auto 28px; }
.wf-pcl-scenarios article { background: #fff; border: 1px solid #e3eaf3; border-radius: 14px; padding: 18px 20px; }
.wf-pcl-scenarios h3 { font-size: 16.5px; font-weight: 800; color: #0b213a; margin: 0 0 8px; }
.wf-pcl-scenarios p { color: #475569; font-size: 14.5px; line-height: 1.6; margin: 0; }
.wf-pcl-note { color: #334155; line-height: 1.7; }
.wf-pcl-note p { margin: 0 0 10px; }
.wf-pcl-contact { list-style: none; padding: 0; margin: 16px 0 0; display: grid; gap: 10px; }
.wf-pcl-contact li { display: flex; gap: 10px; align-items: flex-start; color: #334155; font-size: 15px; line-height: 1.55; }
.wf-pcl-contact i { color: #18529c; margin-top: 4px; width: 16px; }
.wf-pcl-contact a { color: #18529c; font-weight: 700; }
.wf-localities-serviced h3 { font-size: 17px; font-weight: 800; color: var(--wf-primary); margin: 0 0 14px; }
.wf-mistake-title { font-size: 18px; font-weight: 800; color: var(--wf-slate-900); margin: 0 0 8px; }
.wf-faq-h { margin: 0; font-size: inherit; font-weight: inherit; }
</style>
HTML;

include 'includes/header.php';
include 'includes/main-header.php';
?>

<main>
    <!-- Premium Hero Section -->
    <section class="wf-local-hero">
        <div class="container">
            <div class="wf-pcl-hero-grid">
            <div class="wf-local-hero-content">
                <nav class="wf-local-breadcrumb" aria-label="Breadcrumb"><a href="./">Home</a><span aria-hidden="true">/</span><a href="locations">Locations</a><span aria-hidden="true">/</span><a href="loan-in-<?php echo $e($city_key); ?>"><?php echo $e($city['name']); ?></a><span aria-hidden="true">/</span><span aria-current="page"><?php echo $e($prod['name']); ?></span></nav>
                <h1><?php echo htmlspecialchars($page_heading, ENT_QUOTES, 'UTF-8'); ?></h1>
                <div class="wf-local-description-box" style="margin-bottom: 20px; color: #cbd5e1; font-size: 15.5px; line-height: 1.6; font-family: 'Plus Jakarta Sans', sans-serif;">
                    <?php echo waqt_sanitize_rich_html($page_description_paragraph); ?>
                </div>
                <div class="wf-local-trust-strip" aria-label="Loan application highlights">
                    <span><i class="fa-solid fa-shield-halved"></i> Consent-based checks</span>
                    <span><i class="fa-solid fa-file-invoice"></i> Clear terms before acceptance</span>
                    <span><i class="fa-solid fa-location-dot"></i> Serving <?php echo htmlspecialchars($city['name'], ENT_QUOTES, 'UTF-8'); ?> areas</span>
                </div>
                <div class="wf-pcl-hero-actions">
                    <a class="wf-local-btn" href="<?php echo $e($prod['apply_url']); ?>?city=<?php echo urlencode($city_key); ?>">Apply Now <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    <a class="wf-pcl-btn-ghost" href="#emi-calculator"><i class="fa-solid fa-calculator" aria-hidden="true"></i> Check your EMI</a>
                </div>
            </div>
            <aside class="wf-pcl-hero-card" aria-label="<?php echo $e($prod['name']); ?> key facts">
                <p class="wf-pcl-hero-card-title">Key facts</p>
                <dl>
                    <div><dt><i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i> Loan amount</dt><dd><?php echo $e(ucfirst($range_text)); ?></dd></div>
                    <div><dt><i class="fa-solid fa-calendar-days" aria-hidden="true"></i> Tenure</dt><dd><?php echo $e($prod['tenure']); ?></dd></div>
                    <div><dt><i class="fa-solid fa-percent" aria-hidden="true"></i> Interest</dt><dd><?php echo $e($prod['interest']); ?></dd></div>
                    <div class="wf-pcl-hide-sm"><dt><i class="fa-solid <?php echo $city['is_office'] ? 'fa-building' : 'fa-location-dot'; ?>" aria-hidden="true"></i> <?php echo $city['is_office'] ? 'Office' : 'Service'; ?></dt><dd><?php echo $city['is_office'] ? $e($city['address']) : 'Online across ' . $e($city['name']); ?></dd></div>
                </dl>
                <p class="wf-pcl-hero-card-note">Final amount, rate and charges are shown in the Key Fact Statement before you accept.</p>
            </aside>
            </div>
        </div>
    </section>

    <!-- Bento Overview Grid -->
    <section class="wf-local-section">
        <div class="container">
            <h2>Loan Details & Structure</h2>
            <p class="wf-local-sub">Below is the general structure for a <?php echo $e(strtolower($prod['name'])); ?> for applicants in <?php echo $e($city['name']); ?>. Final eligibility checks and documents review apply.</p>
            <div class="wf-local-area-chips" aria-label="Popular service areas in <?php echo htmlspecialchars($city['name'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php foreach (array_slice($city['areas'], 0, 6) as $area): ?>
                    <span><?php echo htmlspecialchars($area, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endforeach; ?>
            </div>

            <div class="wf-product-bento">
                <div class="wf-product-bento-card">
                    <strong>Minimum Amount</strong>
                    <span><?php echo $e($prod['min_amount']); ?></span>
                </div>
                <div class="wf-product-bento-card">
                    <strong>Repayment Tenure</strong>
                    <span><?php echo $e($prod['tenure']); ?></span>
                </div>
                <div class="wf-product-bento-card">
                    <strong>Interest Rate</strong>
                    <span><?php echo $e($prod['interest']); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- EMI Calculator widget (Calculates dynamically) -->
    <section class="wf-local-section wf-local-band" id="emi-calculator">
        <div class="container">
            <h2>Calculate Your EMI</h2>
            <p class="wf-local-sub">Enter your loan details below to instantly calculate your estimated monthly installment.</p>
            
            <div class="wf-calc-widget">
                <div class="wf-calc-sliders">
                    <div class="wf-slider-group">
                        <label class="wf-slider-header" for="loanAmtSlider">Loan Amount 
                            <span id="loanAmtVal"><?php echo $e($prod['min_amount']); ?></span>
                        </label>
                        <input type="range" class="wf-slider-input" id="loanAmtSlider" min="<?php echo (int)filter_var($prod['min_amount'], FILTER_SANITIZE_NUMBER_INT); ?>" max="<?php echo (int) $prod['max_amount_val']; ?>" step="5000" value="<?php echo (int)filter_var($prod['min_amount'], FILTER_SANITIZE_NUMBER_INT); ?>">
                    </div>
                    
                    <div class="wf-slider-group">
                        <label class="wf-slider-header" for="interestSlider">Interest Rate (p.a.) 
                            <span id="interestVal"><?php echo (float) $prod['default_rate']; ?>%</span>
                        </label>
                        <input type="range" class="wf-slider-input" id="interestSlider" min="9" max="36" step="0.25" value="<?php echo (float) $prod['default_rate']; ?>">
                    </div>
                    
                    <div class="wf-slider-group">
                        <label class="wf-slider-header" for="tenureSlider">Tenure (Months) 
                            <span id="tenureVal"><?php echo (int) $prod['default_tenure']; ?> months</span>
                        </label>
                        <input type="range" class="wf-slider-input" id="tenureSlider" min="3" max="84" step="1" value="<?php echo (int) $prod['default_tenure']; ?>">
                    </div>
                </div>
                
                <div class="wf-calc-results">
                    <div class="wf-calc-row">
                        <strong>Estimated EMI</strong>
                        <span id="emiResult">₹0</span>
                    </div>
                    <div class="wf-calc-row">
                        <strong>Total Repayment Amount</strong>
                        <span id="totalRepayResult" style="font-size: 19px; color: var(--wf-slate-600);">₹0</span>
                    </div>
                    <a class="wf-local-btn" href="<?php echo $e($prod['apply_url']); ?>?city=<?php echo urlencode($city_key); ?>" style="background: var(--wf-primary); color: #fff !important; margin-top: 10px;">Apply for This Loan</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Choice Scenarios Section -->
    <?php if (isset($prod['choice_scenarios'])): ?>
    <section class="wf-local-section">
        <div class="container">
            <h2><?php echo $e($prod['choice_scenarios']['title']); ?></h2>
            <p class="wf-local-sub">Choosing the right loan setup is critical. Here are common scenarios where this fits perfectly:</p>
            
            <div class="wf-scenarios-grid">
                <?php foreach ($prod['choice_scenarios']['items'] as $item): ?>
                    <div class="wf-scenario-card">
                        <div class="wf-scenario-icon"><i class="fa-solid fa-circle-check"></i></div>
                        <h3><?php echo $e($item['q'] ?? ''); ?></h3>
                        <p><?php echo $e($item['a'] ?? ''); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Benefits vs Risks Section -->
    <?php if (isset($prod['benefits_vs_risks'])): ?>
    <section class="wf-local-section wf-local-band">
        <div class="container">
            <h2>Benefits vs Risks</h2>
            <p class="wf-local-sub">Evaluate strategic advantages against potential financial trade-offs before proceeding.</p>
            
            <div class="wf-comp-table-wrap">
                <table class="wf-comp-table">
                    <thead>
                        <tr>
                            <th>Key Benefits</th>
                            <th>Key Risks & Considerations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $max_rows = max(count($prod['benefits_vs_risks']['benefits']), count($prod['benefits_vs_risks']['risks']));
                        for ($i = 0; $i < $max_rows; $i++): 
                            $benefit = $prod['benefits_vs_risks']['benefits'][$i] ?? '';
                            $risk = $prod['benefits_vs_risks']['risks'][$i] ?? '';
                        ?>
                            <tr>
                                <td><?php if ($benefit): ?><i class="fa-solid fa-circle-check" style="color: #22c55e; margin-right: 8px;"></i> <?php echo $e($benefit); ?><?php endif; ?></td>
                                <td><?php if ($risk): ?><i class="fa-solid fa-triangle-exclamation" style="color: var(--wf-accent); margin-right: 8px;"></i> <?php echo $e($risk); ?><?php endif; ?></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Callout Grid: Common Mistakes to Avoid -->
    <?php if (isset($prod['common_mistakes'])): ?>
    <section class="wf-local-section">
        <div class="container">
            <h2>Common Mistakes to Avoid</h2>
            <p class="wf-local-sub">Avoid these common missteps that can turn borrowing into a financial burden.</p>
            
            <div class="wf-mistakes-grid">
                <?php foreach ($prod['common_mistakes'] as $index => $mistake): ?>
                    <div class="wf-mistake-card">
                        <h3 class="wf-mistake-title"><?php echo ($index + 1) . '. ' . $e($mistake['q'] ?? ''); ?></h3>
                        <p><?php echo $e($mistake['a'] ?? ''); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Tabbed Eligibility and Documents Section -->
    <section class="wf-local-section wf-local-band">
        <div class="container">
            <h2>Eligibility & Documents Required</h2>
            <p class="wf-local-sub">Check these before you apply. Keeping your documents ready helps your application move faster.</p>
            
            <div class="wf-tabs-container">
                <div class="wf-tabs-nav">
                    <button class="wf-tab-btn active" onclick="switchLocalTab(event, 'tab-eligibility')">Eligibility Criteria</button>
                    <button class="wf-tab-btn" onclick="switchLocalTab(event, 'tab-docs')">Required Documents</button>
                </div>
                
                <div class="wf-tabs-content">
                    <div class="wf-tab-pane active" id="tab-eligibility">
                        <ul class="wf-checklist">
                            <?php foreach ($prod['eligibility'] as $item): ?>
                                <li><i class="fa-solid fa-circle-check"></i> <?php echo $e($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="wf-tab-pane" id="tab-docs">
                        <ul class="wf-checklist">
                            <?php foreach ($prod['documents'] as $item): ?>
                                <li><i class="fa-solid fa-file-invoice"></i> <?php echo $e($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Timeline Stepper Section -->
    <section class="wf-local-section wf-apply-stepper-section">
        <div class="container">
            <h2>How to Apply for a <?php echo $e($prod['name']); ?> Online – Step-by-Step Guide</h2>
            
            <div class="wf-apply-card">
                <div class="wf-apply-timeline">
                    <div class="wf-apply-line"></div>
                    
                    <div class="wf-apply-step">
                        <div class="wf-apply-badge"><i class="fa-solid fa-check"></i></div>
                        <div class="wf-apply-text">
                            <strong>Step 1</strong> – Click 'Apply Now' and fill out the basic registration form
                        </div>
                    </div>
                    
                    <div class="wf-apply-step">
                        <div class="wf-apply-badge"><i class="fa-solid fa-check"></i></div>
                        <div class="wf-apply-text">
                            <strong>Step 2</strong> – Verify your mobile number instantly via a secure OTP code
                        </div>
                    </div>
                    
                    <div class="wf-apply-step">
                        <div class="wf-apply-badge"><i class="fa-solid fa-check"></i></div>
                        <div class="wf-apply-text">
                            <strong>Step 3</strong> – Complete secure PAN and Aadhaar identity verification
                        </div>
                    </div>
                    
                    <div class="wf-apply-step">
                        <div class="wf-apply-badge"><i class="fa-solid fa-check"></i></div>
                        <div class="wf-apply-text">
                            <strong>Step 4</strong> – Provide employment details and upload salary slips or business proof
                        </div>
                    </div>
                    
                    <div class="wf-apply-step">
                        <div class="wf-apply-badge"><i class="fa-solid fa-check"></i></div>
                        <div class="wf-apply-text">
                            <strong>Step 5</strong> – Link your active bank account for secure verification and instant disbursal reference ID
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="wf-apply-btn-wrapper">
                <a href="<?php echo $prefix; ?>apply" class="wf-apply-now-btn">Apply Now</a>
            </div>
        </div>
    </section>

    <!-- City block: content comes from Admin > Location CMS for this city, so each city page is different -->
    <section class="wf-local-section wf-local-band" aria-labelledby="city-title">
        <div class="container">
            <h2 id="city-title"><?php echo $e($prod['name']); ?> for <?php echo $e($city['name']); ?> applicants</h2>
            <?php if ($location['intro'] !== ''): ?><p class="wf-local-sub"><?php echo $e($location['intro']); ?></p><?php endif; ?>

            <?php if ($location['scenarios']): ?>
            <div class="wf-pcl-scenarios">
                <?php foreach (array_slice($location['scenarios'], 0, 4) as $scenario): ?>
                <article>
                    <h3><?php echo $e($scenario[0] ?? ''); ?></h3>
                    <p><?php echo $e($scenario[1] ?? ''); ?></p>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="wf-city-services-grid">
                <div class="wf-city-services-left">
                    <?php if ($location['local_note'] !== ''): ?>
                        <div class="wf-pcl-note"><?php echo waqt_sanitize_rich_html($location['local_note']); ?></div>
                    <?php else: ?>
                        <p>Applicants from <?php echo $e($city['name']); ?> apply online and upload documents after giving consent. Final approval, amount, tenure, charges and repayment terms are shared after verification and policy review.</p>
                    <?php endif; ?>
                    <ul class="wf-pcl-contact">
                        <?php if ($city['is_office']): ?>
                            <li><i class="fa-solid fa-building" aria-hidden="true"></i><span><strong>Office:</strong> <?php echo $e($city['address']); ?></span></li>
                        <?php else: ?>
                            <li><i class="fa-solid fa-laptop" aria-hidden="true"></i><span><strong>Online service</strong> for all of <?php echo $e($city['name']); ?>; no branch visit needed.</span></li>
                        <?php endif; ?>
                        <li><i class="fa-solid fa-phone" aria-hidden="true"></i><span><strong>Call:</strong> <a href="tel:<?php echo $e(preg_replace('/[^0-9+]/', '', $city['phone'])); ?>"><?php echo $e($city['phone']); ?></a> (Mon to Sat, 10 AM to 6 PM)</span></li>
                        <?php if ($location['map_url'] !== '' && preg_match('#^https://#', $location['map_url'])): ?>
                            <li><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i><span><a href="<?php echo $e($location['map_url']); ?>" target="_blank" rel="noopener noreferrer">Open in Google Maps</a></span></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="wf-city-services-right">
                    <div class="wf-localities-serviced">
                        <h3>Areas we serve in <?php echo $e($city['name']); ?></h3>
                        <div class="wf-localities-grid">
                            <?php foreach ($city['areas'] as $area): ?>
                                <span><?php echo $e($area); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Dynamic FAQ Accordion -->
    <section class="wf-local-section">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <p class="wf-local-sub">Find quick answers to common questions about <?php echo $e(strtolower($prod['name'])); ?> options in <?php echo $e($city['name']); ?>.</p>
            
            <div class="wf-faq-wrap">
                <?php foreach ($prod['faqs'] as $index => $faq): $fid = 'pcl-faq-' . $index; ?>
                    <div class="wf-faq-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <h3 class="wf-faq-h">
                        <button type="button" class="wf-faq-trigger" id="<?php echo $fid; ?>-q" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="<?php echo $fid; ?>" onclick="toggleLocalFaq(this)">
                            <?php echo $e($faq['q'] ?? ''); ?>
                            <span class="fa-solid fa-chevron-down" aria-hidden="true"></span>
                        </button>
                        </h3>
                        <div class="wf-faq-content" id="<?php echo $fid; ?>" role="region" aria-labelledby="<?php echo $fid; ?>-q">
                            <p><?php echo waqt_sanitize_rich_html((string) ($faq['a'] ?? '')); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <!-- Internal links: other products in this city, and this product in other cities -->
    <?php
    $otherProducts = array_filter($products, static fn($p, $k) => $k !== $product_key, ARRAY_FILTER_USE_BOTH);
    $otherCities = array_filter(waqt_list_locations(), static fn($l) => $l['slug'] !== $city_key);
    ?>
    <section class="wf-local-section wf-local-band wf-local-related" aria-labelledby="related-title">
        <div class="container">
            <h2 id="related-title">Explore more</h2>
            <div class="wf-local-related-grid">
                <?php if ($otherProducts): ?>
                <div>
                    <h3>Other loans in <?php echo $e($city['name']); ?></h3>
                    <div class="wf-local-related-links">
                        <?php foreach ($otherProducts as $k => $p): ?><a href="<?php echo $e($k . '-in-' . $city_key); ?>"><?php echo $e($p['name']); ?> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a><?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($otherCities): ?>
                <div>
                    <h3><?php echo $e($prod['name']); ?> in other cities</h3>
                    <div class="wf-local-related-links">
                        <?php foreach ($otherCities as $l): ?><a href="<?php echo $e($product_key . '-in-' . $l['slug']); ?>"><?php echo $e($l['name']); ?> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a><?php endforeach; ?>
                        <a href="loan-in-<?php echo $e($city_key); ?>" class="is-hub">All loans in <?php echo $e($city['name']); ?> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        <?php if ($product_page !== ''): ?><a href="<?php echo $e($product_page); ?>" class="is-hub"><?php echo $e($prod['name']); ?> guide <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<script>
function switchLocalTab(evt, tabId) {
    const container = evt.currentTarget.closest('.wf-tabs-container');
    const buttons = container.querySelectorAll('.wf-tab-btn');
    const panes = container.querySelectorAll('.wf-tab-pane');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    panes.forEach(pane => pane.classList.remove('active'));
    
    evt.currentTarget.classList.add('active');
    container.querySelector('#' + tabId).classList.add('active');
}

function toggleLocalFaq(btn) {
    const faqItem = btn.closest('.wf-faq-item');
    const isAlreadyActive = faqItem.classList.contains('active');
    
    const container = btn.closest('.wf-faq-wrap');
    container.querySelectorAll('.wf-faq-item').forEach(item => {
        item.classList.remove('active');
        item.querySelector('.wf-faq-content').style.maxHeight = null;
        item.querySelector('.wf-faq-trigger').setAttribute('aria-expanded', 'false');
    });
    
    if (!isAlreadyActive) {
        faqItem.classList.add('active');
        btn.setAttribute('aria-expanded', 'true');
        const content = faqItem.querySelector('.wf-faq-content');
        content.style.maxHeight = content.scrollHeight + "px";
    }
}
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.wf-faq-item.active .wf-faq-content').forEach(function (c) { c.style.maxHeight = c.scrollHeight + 'px'; });
});

// Live EMI Calculation logic
document.addEventListener("DOMContentLoaded", function() {
    const loanSlider = document.getElementById("loanAmtSlider");
    const rateSlider = document.getElementById("interestSlider");
    const tenureSlider = document.getElementById("tenureSlider");
    
    const loanAmtVal = document.getElementById("loanAmtVal");
    const interestVal = document.getElementById("interestVal");
    const tenureVal = document.getElementById("tenureVal");
    
    const emiResult = document.getElementById("emiResult");
    const totalRepayResult = document.getElementById("totalRepayResult");
    
    function calculateEmi() {
        const principal = parseFloat(loanSlider.value);
        const annualRate = parseFloat(rateSlider.value);
        const months = parseInt(tenureSlider.value);
        
        loanAmtVal.innerText = "₹" + principal.toLocaleString("en-IN");
        interestVal.innerText = annualRate.toFixed(2) + "%";
        tenureVal.innerText = months + " months";
        
        // monthly rate
        const monthlyRate = annualRate / 12 / 100;
        
        let emi = 0;
        let totalRepay = 0;
        
        if (months === 1) {
            // Payday loan flat single payment
            totalRepay = principal + (principal * monthlyRate);
            emi = totalRepay;
        } else {
            // Standard compound EMI
            emi = (principal * monthlyRate * Math.pow(1 + monthlyRate, months)) / (Math.pow(1 + monthlyRate, months) - 1);
            totalRepay = emi * months;
        }
        
        if (isNaN(emi) || !isFinite(emi)) {
            emi = 0;
            totalRepay = principal;
        }
        
        emiResult.innerText = "₹" + Math.round(emi).toLocaleString("en-IN");
        totalRepayResult.innerText = "₹" + Math.round(totalRepay).toLocaleString("en-IN");
    }
    
    if (loanSlider && rateSlider && tenureSlider) {
        loanSlider.addEventListener("input", calculateEmi);
        rateSlider.addEventListener("input", calculateEmi);
        tenureSlider.addEventListener("input", calculateEmi);
        
        // initial run
        calculateEmi();
    }
});
</script>

<?php 
$footer_cta_title = "Ready to Apply for a " . $prod['name'] . " in " . $city['name'] . "?";
$footer_cta_desc = "Start your secure online application. You will see the Key Fact Statement with all charges before you accept.";
include 'includes/footer.php'; 
?>
