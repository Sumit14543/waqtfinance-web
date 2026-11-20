<?php
declare(strict_types=1);

// Location hub page: /loan-in-{slug}. All content comes from Admin > Location CMS (see includes/location_data.php).
require_once __DIR__ . '/location_data.php';

if (session_status() === PHP_SESSION_NONE && function_exists('start_secure_session')) {
    start_secure_session();
}
$isAdmin = !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$location = waqt_get_location((string) $location_key, $isAdmin);

if (!$location) {
    http_response_code(404);
    require __DIR__ . '/../404.php';
    return;
}

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$products = waqt_location_product_templates();
$productIcons = [];
foreach (require __DIR__ . '/../config/loan-products.php' as $p) {
    $productIcons[$p['learn']] = $p['icon'];
}
$allLocations = waqt_list_locations();
$otherLocations = array_values(array_filter($allLocations, static fn(array $l): bool => $l['slug'] !== $location['slug']));
$publishedSlugs = array_fill_keys(array_column($allLocations, 'slug'), true);
$nearby = array_intersect_key($location['nearby'], $publishedSlugs);
unset($nearby[$location['slug']]);
if (!$nearby) {
    foreach (array_slice($otherLocations, 0, 4) as $l) $nearby[$l['slug']] = $l['name'];
}
$isOffice = $location['type'] === 'office' && $location['address'] !== '';
$phoneDigits = preg_replace('/[^\d+]/', '', $location['phone']);
$defaults = waqt_location_site_defaults();

$canonical_url = 'https://waqtfinance.com/loan-in-' . $location['slug'];
$page_title = $location['meta_title'] ?: ('Loans in ' . $location['name'] . ' | Waqt Finance');
$meta_description = $location['meta_description'] ?: ('Explore online loan options for eligible applicants in ' . $location['name'] . ' with Waqt Finance.');
$breadcrumb_label = 'Loans in ' . $location['name'];
$breadcrumb_items = [
    ['name' => 'Home', 'url' => ''],
    ['name' => 'Locations', 'url' => 'locations'],
    ['name' => 'Loans in ' . $location['name'], 'url' => 'loan-in-' . $location['slug']],
];
$meta_image = $location['hero_image'] !== '' ? $location['hero_image'] : 'assets/images/og-default.jpg';
$meta_image_alt = 'Loan options in ' . $location['name'];
if ($location['status'] !== 'published') {
    $meta_robots = 'noindex, nofollow'; // admin preview of a draft
}

$areasText = $location['areas'] ? implode(', ', $location['areas']) : $location['name'];
$faqs = [
    ['q' => 'Can I apply online from ' . $location['name'] . '?', 'a' => 'Yes. Eligible applicants can choose a product and complete the online application. Submitting an application does not guarantee approval.'],
    $isOffice
        ? ['q' => 'Can I visit the Waqt Finance office in ' . $location['name'] . '?', 'a' => 'Our office is at ' . $location['address'] . '. Please call ' . $location['phone'] . ' before visiting to confirm availability and the service you need.']
        : ['q' => 'Does Waqt Finance have a branch in ' . $location['name'] . '?', 'a' => 'This page covers an online service area, not a branch. Applications are handled online; call ' . $location['phone'] . ' for help.'],
    ['q' => 'Which areas are covered?', 'a' => 'This page covers ' . $areasText . ' and nearby areas, subject to product availability and policy.'],
    ['q' => 'What documents may be required?', 'a' => 'Depending on the product: PAN, KYC and address proof, bank statements, income or business proof, and asset documents for secured loans.'],
    ['q' => 'Is approval guaranteed?', 'a' => 'No. Approval, amount, interest rate and tenure depend on verification, credit assessment, repayment capacity and policy. You see all terms in a Key Fact Statement before accepting.'],
];
foreach ($location['faqs'] as $faq) $faqs[] = $faq;

$service_schema = ['@type' => 'Service', '@id' => $canonical_url . '#service', 'name' => 'Loan application services in ' . $location['name'], 'url' => $canonical_url, 'description' => $meta_description, 'provider' => ['@id' => 'https://waqtfinance.com/#organization'], 'areaServed' => array_map(static fn(string $area): array => ['@type' => 'Place', 'name' => $area], $location['areas'] ?: [$location['name']]), 'serviceType' => array_values(array_column($products, 'name'))];
$faq_schema = ['@type' => 'FAQPage', '@id' => $canonical_url . '#faq', 'mainEntity' => array_map(static fn(array $faq): array => ['@type' => 'Question', 'name' => $faq['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']]], $faqs)];
$seo_schema = [$service_schema, $faq_schema];
if ($isOffice) {
    $seo_schema[] = ['@type' => 'FinancialService', '@id' => $canonical_url . '#office', 'name' => 'Waqt Finance, ' . $location['name'], 'url' => $canonical_url, 'telephone' => $phoneDigits, 'address' => ['@type' => 'PostalAddress', 'streetAddress' => $location['address'], 'addressCountry' => 'IN']];
}

$cssFiles = ['location-page.css', 'location-page-polish.css', 'location-page-card-polish.css', 'location-hub.css'];
$extra_head = '';
foreach ($cssFiles as $cssFile) {
    $path = __DIR__ . '/../assets/css/' . $cssFile;
    if (is_file($path)) $extra_head .= '<link rel="stylesheet" href="assets/css/' . $cssFile . '?v=' . filemtime($path) . '">';
}
include __DIR__ . '/header.php';
include __DIR__ . '/main-header.php';
?>
<main class="wf-location-page" id="main-content">
  <?php if ($location['status'] !== 'published'): ?>
    <div class="wf-loc-preview-bar" role="status"><i class="fa-solid fa-eye"></i> Admin preview: this location is a <strong>draft</strong> and is not visible to customers.</div>
  <?php endif; ?>

  <section class="wf-location-hero<?php echo $location['hero_image'] ? ' has-image' : ''; ?>" aria-labelledby="location-title"<?php if ($location['hero_image']): ?> style="--loc-hero-image:url('<?php echo $h($location['hero_image']); ?>')"<?php endif; ?>><div class="container">
    <div class="wf-loc-hero-grid">
      <div class="wf-loc-hero-main">
        <nav class="wf-location-breadcrumb" aria-label="Breadcrumb"><a href="./">Home</a><span aria-hidden="true">/</span><a href="locations">Locations</a><span aria-hidden="true">/</span><span aria-current="page"><?php echo $h($location['name']); ?></span></nav>
        <span class="wf-location-kicker"><i class="fa-solid <?php echo $isOffice ? 'fa-building' : 'fa-location-dot'; ?>" aria-hidden="true"></i> <?php echo $isOffice ? 'Office location' : 'Online service area'; ?></span>
        <h1 id="location-title"><?php echo $h($location['title'] ?: 'Loans in ' . $location['name']); ?></h1>
        <?php if ($location['intro'] !== ''): ?><p class="wf-loc-hero-intro"><?php echo $h($location['intro']); ?></p><?php endif; ?>
        <div class="wf-location-actions"><a class="wf-location-btn primary" href="#location-products">See loan options <i class="fa-solid fa-arrow-down" aria-hidden="true"></i></a><a class="wf-location-btn secondary" href="tel:<?php echo $h($phoneDigits); ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i> Call <?php echo $h($location['phone']); ?></a></div>
      </div>
      <aside class="wf-loc-hero-card" aria-label="<?php echo $h($location['name']); ?> at a glance">
        <p class="wf-loc-hero-card-title">At a glance</p>
        <ul>
          <li class="wf-loc-hide-sm"><i class="fa-solid fa-layer-group" aria-hidden="true"></i><div><strong><?php echo count($products); ?> loan products</strong><span>Personal, business and secured loans</span></div></li>
          <?php if ($location['areas']): ?>
          <li><i class="fa-solid fa-map-pin" aria-hidden="true"></i><div><strong><?php echo count($location['areas']); ?> areas covered</strong><span><?php echo $h(implode(', ', array_slice($location['areas'], 0, 3))); ?><?php echo count($location['areas']) > 3 ? ' and more' : ''; ?></span></div></li>
          <?php endif; ?>
          <?php if ($isOffice): ?>
          <li><i class="fa-solid fa-building" aria-hidden="true"></i><div><strong>Office in <?php echo $h($location['name']); ?></strong><span><?php echo $h($location['address']); ?></span></div></li>
          <?php else: ?>
          <li><i class="fa-solid fa-laptop" aria-hidden="true"></i><div><strong>Apply 100% online</strong><span>No branch visit needed</span></div></li>
          <?php endif; ?>
          <li class="wf-loc-hide-sm"><i class="fa-solid fa-headset" aria-hidden="true"></i><div><strong>Support</strong><span>Mon to Sat, 10:00 AM to 6:00 PM</span></div></li>
        </ul>
      </aside>
    </div>
  </div></section>

  <section class="wf-location-products" id="location-products" aria-labelledby="location-products-title"><div class="container">
    <div class="wf-location-section-head"><span>Choose by requirement</span><h2 id="location-products-title">Loan options in <?php echo $h($location['name']); ?></h2><p>Compare amounts and tenures, then open the product page for <?php echo $h($location['name']); ?> before you apply.</p></div>
    <?php if ($location['areas']): ?>
      <div class="wf-location-area-chips" aria-label="Areas served"><?php foreach ($location['areas'] as $area): ?><span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?php echo $h($area); ?></span><?php endforeach; ?></div>
    <?php endif; ?>
    <div class="wf-location-product-grid"><?php foreach ($products as $key => $product): ?>
      <article class="wf-location-product-card">
        <span class="wf-location-product-icon"><i class="fa-solid <?php echo $h($productIcons[$key] ?? 'fa-indian-rupee-sign'); ?>" aria-hidden="true"></i></span>
        <h3><?php echo $h($product['name']); ?></h3>
        <p><?php echo $h(mb_strimwidth(strip_tags((string) ($product['desc'] ?? '')), 0, 150, '…')); ?></p>
        <dl class="wf-loc-product-facts">
          <?php if (!empty($product['range_text'])): ?><div><dt>Amount</dt><dd><?php echo $h($product['range_text']); ?></dd></div><?php endif; ?>
          <?php if (!empty($product['tenure'])): ?><div><dt>Tenure</dt><dd><?php echo $h($product['tenure']); ?></dd></div><?php endif; ?>
        </dl>
        <div><a href="<?php echo $h($key . '-in-' . $location['slug']); ?>">View details</a><a class="wf-location-apply" href="<?php echo $h(($product['apply_url'] ?? 'apply') . (str_contains((string) ($product['apply_url'] ?? ''), '?') ? '&' : '?') . 'city=' . urlencode($location['slug'])); ?>">Apply <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
      </article>
    <?php endforeach; ?></div>
  </div></section>

  <?php if ($location['scenarios'] || $location['local_note'] !== '' || $isOffice): ?>
  <section class="wf-location-context"><div class="container">
    <div class="wf-location-section-head"><span>Local application guidance</span><h2>Applying from <?php echo $h($location['name']); ?></h2><p>Choose a product based on your need and what you can afford to repay.</p></div>
    <?php if ($location['scenarios']): ?>
      <div class="wf-location-scenario-grid"><?php foreach ($location['scenarios'] as $index => $scenario): ?><article><span><?php echo $index + 1; ?></span><h3><?php echo $h($scenario[0]); ?></h3><p><?php echo $h($scenario[1]); ?></p></article><?php endforeach; ?></div>
    <?php endif; ?>
    <div class="wf-loc-support-grid">
      <?php if ($location['local_note'] !== ''): ?>
        <div class="wf-location-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><div class="wf-location-note-content"><?php echo waqt_sanitize_rich_html($location['local_note']); ?></div></div>
      <?php endif; ?>
      <aside class="wf-loc-contact-card" aria-label="Contact">
        <h3><?php echo $isOffice ? 'Visit or call us' : 'Talk to us'; ?></h3>
        <?php if ($location['address'] !== ''): ?><p><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?php echo $h($location['address']); ?></p><?php endif; ?>
        <p><i class="fa-solid fa-phone" aria-hidden="true"></i> <a href="tel:<?php echo $h($phoneDigits); ?>"><?php echo $h($location['phone']); ?></a></p>
        <p><i class="fa-solid fa-envelope" aria-hidden="true"></i> <a href="mailto:<?php echo $h($defaults['email']); ?>"><?php echo $h($defaults['email']); ?></a></p>
        <p class="wf-loc-hours"><i class="fa-regular fa-clock" aria-hidden="true"></i> Monday to Saturday, 10:00 AM to 6:00 PM</p>
        <?php if ($location['map_url'] !== '' && preg_match('#^https://#', $location['map_url'])): ?>
          <a class="wf-location-btn secondary-dark" href="<?php echo $h($location['map_url']); ?>" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i> Get directions</a>
        <?php endif; ?>
      </aside>
    </div>
  </div></section>
  <?php endif; ?>

  <section class="wf-location-calculator" aria-labelledby="emi-title"><div class="container"><div class="wf-location-calc-grid"><div><span>Illustrative calculator</span><h2 id="emi-title">Estimate a monthly EMI</h2><p>This estimate uses the annual rate you enter. It is not an offer and does not represent APR or final sanctioned terms.</p><div class="wf-location-fields"><label>Loan amount (₹)<input id="locAmount" type="text" inputmode="numeric" value="2,50,000"></label><label>Annual rate (%)<input id="locRate" type="number" min="1" max="50" step="0.25" value="13"></label><label>Tenure (months)<input id="locTenure" type="number" min="1" max="360" value="36"></label></div></div><output class="wf-location-result" id="locEmiResult" aria-live="polite"><span>Estimated monthly EMI</span><strong>₹8,423</strong><small>Illustrative estimate only; review the applicable KFS and final terms.</small></output></div></div></section>

  <section class="wf-location-faq" aria-labelledby="location-faq-title"><div class="container"><div class="wf-location-section-head"><span>Important information</span><h2 id="location-faq-title">Frequently asked questions</h2></div><div class="wf-location-faq-list"><?php foreach ($faqs as $i => $faq): $panel = 'location-faq-panel-' . $i; $button = 'location-faq-button-' . $i; ?><article><h3><button id="<?php echo $button; ?>" type="button" aria-expanded="false" aria-controls="<?php echo $panel; ?>"><span><?php echo $h($faq['q']); ?></span><i class="fa-solid fa-plus" aria-hidden="true"></i></button></h3><div id="<?php echo $panel; ?>" aria-labelledby="<?php echo $button; ?>" role="region" hidden><p><?php echo $h($faq['a']); ?></p></div></article><?php endforeach; ?></div></div></section>

  <section class="wf-location-nearby"><div class="container">
    <div><span>Explore nearby service areas</span><?php foreach ($nearby as $slug => $label): ?><a href="loan-in-<?php echo $h($slug); ?>"><?php echo $h($label); ?> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a><?php endforeach; ?><a href="locations" class="wf-loc-all-link">All locations <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
    <div><h2>Need help selecting a product?</h2><p>Talk to our support team or read the loan FAQs before applying.</p><a href="faqs">Read loan FAQs</a><a href="contact-us">Contact support</a></div>
  </div></section>
</main>
<script src="assets/js/location-page.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/location-page.js'); ?>" defer></script>
<?php
$footer_cta_title = 'Ready to explore loan options in ' . $location['name'] . '?';
$footer_cta_desc = 'Review the product, eligibility and KFS before accepting any loan offer.';
include __DIR__ . '/footer.php';
?>
