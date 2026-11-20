<?php
declare(strict_types=1);

// /locations: every published location from Admin > Location CMS.
require_once __DIR__ . '/includes/location_data.php';

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$locations = waqt_list_locations();
$products = waqt_location_product_templates();

$page_title = 'Loan Service Locations | Waqt Finance';
$meta_description = 'Find Waqt Finance loan options by location. Compare personal, business, payday, EDI, vehicle and property loans for your city and apply online.';
$canonical_url = 'https://waqtfinance.com/locations';
$breadcrumb_label = 'Locations';
$breadcrumb_items = [['name' => 'Home', 'url' => ''], ['name' => 'Locations', 'url' => 'locations']];
$seo_schema = [[
    '@type' => 'ItemList',
    '@id' => $canonical_url . '#locations',
    'name' => 'Waqt Finance service locations',
    'itemListElement' => array_map(static fn(array $loc, int $i): array => ['@type' => 'ListItem', 'position' => $i + 1, 'url' => 'https://waqtfinance.com/loan-in-' . $loc['slug'], 'name' => 'Loans in ' . $loc['name']], $locations, array_keys($locations)),
]];

$extra_head = '';
foreach (['location-page.css', 'location-page-polish.css', 'location-hub.css'] as $cssFile) {
    $path = __DIR__ . '/assets/css/' . $cssFile;
    if (is_file($path)) $extra_head .= '<link rel="stylesheet" href="assets/css/' . $cssFile . '?v=' . filemtime($path) . '">';
}
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/main-header.php';
?>
<main class="wf-location-page" id="main-content">
  <section class="wf-location-hero" aria-labelledby="locations-title"><div class="container">
    <nav class="wf-location-breadcrumb" aria-label="Breadcrumb"><a href="./">Home</a><span aria-hidden="true">/</span><span aria-current="page">Locations</span></nav>
    <div class="wf-location-hero-content">
      <span class="wf-location-kicker"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i> Where we lend</span>
      <h1 id="locations-title">Loans by location</h1>
      <p>Choose your city to see the loan products available there, local guidance and how to reach us. Every application is completed online.</p>
      <ul class="wf-loc-hero-facts" aria-label="At a glance">
        <li><strong><?php echo count($locations); ?></strong> locations</li>
        <li><strong><?php echo count($products); ?></strong> loan products</li>
      </ul>
    </div>
  </div></section>

  <section class="wf-location-products" aria-labelledby="loc-list-title"><div class="container">
    <div class="wf-location-section-head"><span>Choose your city</span><h2 id="loc-list-title">Our service locations</h2><p>Don't see your city? You can still apply online; our team will tell you whether we serve your area.</p></div>
    <?php if (!$locations): ?>
      <p style="text-align:center; margin-top:24px;">Locations will appear here soon. Meanwhile, <a href="products">browse all loan products</a>.</p>
    <?php else: ?>
      <div class="wf-loc-index-grid">
        <?php foreach ($locations as $loc): ?>
          <a class="wf-loc-index-card" href="loan-in-<?php echo $h($loc['slug']); ?>">
            <span class="badge-type"><i class="fa-solid <?php echo $loc['type'] === 'office' ? 'fa-building' : 'fa-location-dot'; ?>" aria-hidden="true"></i> <?php echo $loc['type'] === 'office' ? 'Office location' : ($loc['type'] === 'region' ? 'Region' : 'Online service area'); ?></span>
            <h2>Loans in <?php echo $h($loc['name']); ?></h2>
            <?php if ($loc['intro'] !== ''): ?><p><?php echo $h(mb_strimwidth($loc['intro'], 0, 140, '…')); ?></p><?php endif; ?>
            <?php if ($loc['areas']): ?><small><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?php echo $h(implode(', ', array_slice($loc['areas'], 0, 4))); ?><?php echo count($loc['areas']) > 4 ? ' and more' : ''; ?></small><?php endif; ?>
            <strong>View loan options <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></strong>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div></section>
</main>
<?php
$footer_cta_title = 'Ready to apply?';
$footer_cta_desc = 'Apply online from anywhere. Review the product, eligibility and KFS before accepting any loan offer.';
include __DIR__ . '/includes/footer.php';
?>
