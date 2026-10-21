<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
$conn = db();
$isAdmin = !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
if ($isAdmin) {
    // Admins are allowed to preview pages even if status is set to draft
    $stmt = $conn->prepare("SELECT * FROM location_pages WHERE slug = ? LIMIT 1");
} else {
    $stmt = $conn->prepare("SELECT * FROM location_pages WHERE slug = ? AND status = 'published' LIMIT 1");
}
$stmt->bind_param('s', $location_key);
$stmt->execute();
$location = $stmt->get_result()->fetch_assoc();

if (!$location) {
    http_response_code(404);
    require __DIR__ . '/../404.php';
    return;
}

$location['areas'] = json_decode((string) $location['areas'], true) ?? [];
$location['scenarios'] = json_decode((string) $location['scenarios'], true) ?? [];
$location['nearby'] = json_decode((string) $location['nearby'], true) ?? [];

$products = require __DIR__ . '/../config/loan-products.php';

$canonical_url = 'https://waqtfinance.com/loan-in-' . $location_key;
$page_title = $location['meta_title'] . ' | Waqt Finance';
$meta_description = $location['meta_description'];
$breadcrumb_label = 'Loans in ' . $location['name'];
$meta_image = 'assets/images/loan-hero.webp';
$meta_image_alt = 'Loan options in ' . $location['name'];
$faqs = [
    ['q'=>'Can I apply online from '.$location['name'].'?','a'=>'Yes. Eligible applicants can select a product and complete the relevant online application journey. Submission does not guarantee approval.'],
    ['q'=>'Which areas are covered?','a'=>'This service-area page covers '.implode(', ', $location['areas']).' and nearby areas, subject to product availability and policy.'],
    ['q'=>'What documents may be required?','a'=>'Depending on the product, documents may include PAN, KYC and address proof, bank statements, income or business proof, and asset-related records.'],
    ['q'=>'Is approval guaranteed?','a'=>'No. Approval, amount, pricing and tenure depend on verification, credit assessment, repayment capacity and applicable policy.'],
];
if ($location['type'] === 'office') {
    array_splice($faqs, 1, 0, [[
        'q'=>'Can I visit the Waqt Finance support location in Noida?',
        'a'=>'Waqt Finance lists a corporate support location at '.$location['address'].'. Please call +91 92170 86610 before visiting to confirm availability and the service required.'
    ]]);
} else {
    array_splice($faqs, 1, 0, [[
        'q'=>'Does Waqt Finance have a branch in '.$location['name'].'?',
        'a'=>'This page represents an online service area, not a physical branch. Contact verified support for assistance or information about the Noida support location.'
    ]]);
}
$custom_faqs = json_decode((string) ($location['faqs'] ?? '[]'), true) ?? [];
foreach ($custom_faqs as $c_faq) {
    if (!empty($c_faq['q']) && !empty($c_faq['a'])) {
        $faqs[] = [
            'q' => $c_faq['q'],
            'a' => $c_faq['a']
        ];
    }
}
$service_schema = ['@type'=>'Service','@id'=>$canonical_url.'#service','name'=>'Loan application services in '.$location['name'],'url'=>$canonical_url,'description'=>$meta_description,'provider'=>['@id'=>'https://waqtfinance.com/#organization'],'areaServed'=>array_map(static fn(string $area):array=>['@type'=>'Place','name'=>$area],$location['areas']),'serviceType'=>array_column($products,'name')];
$faq_schema = ['@type'=>'FAQPage','@id'=>$canonical_url.'#faq','mainEntity'=>array_map(static fn(array $faq):array=>['@type'=>'Question','name'=>$faq['q'],'acceptedAnswer'=>['@type'=>'Answer','text'=>$faq['a']]],$faqs)];
$seo_schema = [$service_schema,$faq_schema];
if ($location['type'] === 'office') {
    $seo_schema[] = ['@type'=>'FinancialService','@id'=>$canonical_url.'#office','name'=>'Waqt Finance Noida Support Location','url'=>$canonical_url,'telephone'=>'+919217086610','address'=>['@type'=>'PostalAddress','streetAddress'=>'BSI Business Park, Sector 63','addressLocality'=>'Noida','addressRegion'=>'Uttar Pradesh','postalCode'=>'201301','addressCountry'=>'IN']];
}
$css = __DIR__ . '/../assets/css/location-page.css';
$polish_css = __DIR__ . '/../assets/css/location-page-polish.css';
  $card_polish_css = __DIR__ . '/../assets/css/location-page-card-polish.css';
  $extra_head = '<link rel="stylesheet" href="assets/css/location-page.css?v='.(is_file($css)?filemtime($css):time()).'"><link rel="stylesheet" href="assets/css/location-page-polish.css?v='.(is_file($polish_css)?filemtime($polish_css):time()).'"><link rel="stylesheet" href="assets/css/location-page-card-polish.css?v='.(is_file($card_polish_css)?filemtime($card_polish_css):time()).'">';
include __DIR__ . '/header.php';
include __DIR__ . '/main-header.php';
?>
<main class="wf-location-page" id="main-content">
  <section class="wf-location-hero" aria-labelledby="location-title"><div class="container">
    <nav class="wf-location-breadcrumb" aria-label="Breadcrumb"><a href="./">Home</a><span>/</span><a href="loan-in-delhi-ncr">Locations</a><span>/</span><span aria-current="page"><?php echo htmlspecialchars($location['name']); ?></span></nav>
    <div class="wf-location-hero-content">
      <span class="wf-location-kicker"><i class="fa-solid fa-location-dot"></i> <?php echo $location['type']==='office'?'Verified support location':'Online service area'; ?></span>
      <h1 id="location-title"><?php echo htmlspecialchars($location['title']); ?></h1>
      <p><?php echo htmlspecialchars($location['intro']); ?></p>
      <div class="wf-location-actions"><a class="wf-location-btn primary" href="#location-products">Explore loan options <i class="fa-solid fa-arrow-down"></i></a><a class="wf-location-btn secondary" href="eligibility-calculator">Check eligibility</a></div>
    </div>
  </div></section>

  <section class="wf-location-products" id="location-products" aria-labelledby="location-products-title"><div class="container">
    <div class="wf-location-section-head"><span>Choose by requirement</span><h2 id="location-products-title">Loan options for <?php echo htmlspecialchars($location['name']); ?></h2><p>View product information before starting the correct application journey.</p></div>
    <div class="wf-location-area-chips" aria-label="Areas served"><?php foreach($location['areas'] as $area): ?><span><?php echo htmlspecialchars($area); ?></span><?php endforeach; ?></div>
    <div class="wf-location-product-grid"><?php foreach($products as $product): ?><article class="wf-location-product-card"><span class="wf-location-product-icon"><i class="fa-solid <?php echo $product['icon']; ?>"></i></span><h3><?php echo htmlspecialchars($product['name']); ?> in <?php echo htmlspecialchars($location['name']); ?></h3><p><?php echo htmlspecialchars($product['summary']); ?></p><div><a href="<?php echo $product['learn']; ?>-in-<?php echo $location_key; ?>">View details</a><a class="wf-location-apply" href="<?php echo $product['apply']; ?>?city=<?php echo urlencode($location_key); ?>">Start application <i class="fa-solid fa-arrow-right"></i></a></div></article><?php endforeach; ?></div>
  </div></section>

  <section class="wf-location-context"><div class="container"><div class="wf-location-section-head"><span>Local application guidance</span><h2>Preparing to apply from <?php echo htmlspecialchars($location['name']); ?></h2><p>Choose a product based on need and affordability—not location alone.</p></div><div class="wf-location-scenario-grid"><?php foreach($location['scenarios'] as $index=>$scenario): ?><article><span><?php echo $index+1; ?></span><h3><?php echo htmlspecialchars($scenario[0]); ?></h3><p><?php echo htmlspecialchars($scenario[1]); ?></p></article><?php endforeach; ?></div><div class="wf-location-note"><i class="fa-solid fa-circle-info"></i><div class="wf-location-note-content" style="flex: 1;">
        <?php if ($location['type'] === 'office'): ?>
          Waqt Finance lists its corporate support location at <?php echo htmlspecialchars((string) $location['address']); ?>. Please call before visiting to confirm availability and the service you need.
        <?php else: ?>
          <?php echo waqt_sanitize_rich_html((string) $location['local_note']); ?>
        <?php endif; ?>
      </div></div></div></section>

  <section class="wf-location-calculator" aria-labelledby="emi-title"><div class="container"><div class="wf-location-calc-grid"><div><span>Illustrative calculator</span><h2 id="emi-title">Estimate a monthly EMI</h2><p>This estimate uses a nominal annual rate entered by you. It is not an offer and does not represent APR or final sanctioned terms.</p><div class="wf-location-fields"><label>Loan amount (₹)<input id="locAmount" type="text" inputmode="numeric" value="2,50,000"></label><label>Annual rate (%)<input id="locRate" type="number" min="1" max="50" step="0.25" value="13"></label><label>Tenure (months)<input id="locTenure" type="number" min="1" max="360" value="36"></label></div></div><output class="wf-location-result" id="locEmiResult" aria-live="polite"><span>Estimated monthly EMI</span><strong>₹8,423</strong><small>Illustrative estimate only; review the applicable KFS and final terms.</small></output></div></div></section>

  <section class="wf-location-faq" aria-labelledby="location-faq-title"><div class="container"><div class="wf-location-section-head"><span>Important information</span><h2 id="location-faq-title">Frequently asked questions</h2></div><div class="wf-location-faq-list"><?php foreach($faqs as $i=>$faq): $panel='location-faq-panel-'.$i; $button='location-faq-button-'.$i; ?><article><h3><button id="<?php echo $button; ?>" type="button" aria-expanded="false" aria-controls="<?php echo $panel; ?>"><span><?php echo htmlspecialchars($faq['q']); ?></span><i class="fa-solid fa-plus"></i></button></h3><div id="<?php echo $panel; ?>" aria-labelledby="<?php echo $button; ?>" hidden><p><?php echo htmlspecialchars($faq['a']); ?></p></div></article><?php endforeach; ?></div></div></section>

  <section class="wf-location-nearby"><div class="container"><div><span>Explore nearby service areas</span><?php foreach($location['nearby'] as $slug=>$label): ?><a href="loan-in-<?php echo $slug; ?>"><?php echo htmlspecialchars($label); ?> <i class="fa-solid fa-arrow-right"></i></a><?php endforeach; ?></div><div><h2>Need help selecting a product?</h2><p>Contact verified support or review the loan FAQs before applying.</p><a href="faqs">Read loan FAQs</a><a href="contact-us">Contact support</a></div></div></section>
</main>
<script src="assets/js/location-page.js?v=<?php echo filemtime(__DIR__.'/../assets/js/location-page.js'); ?>" defer></script>
<?php
$footer_cta_title = 'Ready to explore loan options in '.$location['name'].'?';
$footer_cta_desc = 'Review the product, eligibility and KFS before accepting any loan offer.';
include __DIR__ . '/footer.php';
?>
