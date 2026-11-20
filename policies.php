<?php
// Policies & regulatory documents hub.
// PDFs are served from /pdfs (file names kept the same as the old page so existing links keep working).
// A PDF that is missing on the server is listed as "Being updated" instead of linking to a 404.
$page_title = "Policies & Regulatory Documents | Waqt Finance";
$meta_description = "Read Waqt Finance policies: Fair Practice Code, interest rate policy, KYC documents, loans and advances, privacy, grievance redressal and digital lending disclosures.";
$meta_keywords = "Waqt Finance policies, fair practice code, interest rate policy, KYC documents list, NBFC policies, grievance redressal";
$canonical_url = "https://waqtfinance.com/policies";
$breadcrumb_label = "Policies";
$breadcrumb_items = [['name' => 'Home', 'url' => ''], ['name' => 'Policies', 'url' => 'policies']];
$extra_head = '<link rel="stylesheet" href="assets/css/policies-hub.css?v=1">';
$h = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$pdf_docs = [
    ['file' => 'fair-practice-code.pdf', 'title' => 'Fair Practice Code Guidelines', 'desc' => 'Detailed guidelines for fair dealing with borrowers: loan terms, disbursement, interest, recovery conduct and complaints.', 'icon' => 'fa-scale-balanced'],
    ['file' => 'FPC.pdf', 'title' => 'Fair Practice Code (FPC)', 'desc' => 'Our Fair Practice Code covering transparency, lending practices and grievance handling.', 'icon' => 'fa-stamp'],
    ['file' => 'policy-for-determining-interest-rates.pdf', 'title' => 'Policy for Determining Interest Rates', 'desc' => 'How interest rates, processing fees and other charges are set, including risk grading.', 'icon' => 'fa-percent'],
    ['file' => 'document-on-loans-and-advances.pdf', 'title' => 'Policy on Loans and Advances', 'desc' => 'Our lending policy: eligibility, credit assessment, sanction and disbursement.', 'icon' => 'fa-file-contract'],
    ['file' => 'list-of-kyc-documents.pdf', 'title' => 'List of KYC Documents', 'desc' => 'Identity and address documents accepted for individuals and businesses.', 'icon' => 'fa-id-card'],
];
foreach ($pdf_docs as &$doc) {
    $path = __DIR__ . '/pdfs/' . $doc['file'];
    $doc['available'] = is_file($path);
    $doc['size'] = $doc['available'] ? filesize($path) : 0;
    $doc['url'] = 'pdfs/' . rawurlencode($doc['file']);
}
unset($doc);
$format_size = static fn(int $bytes): string => $bytes >= 1048576 ? number_format($bytes / 1048576, 1) . ' MB' : max(1, (int) round($bytes / 1024)) . ' KB';

$web_policies = [
    ['privacy-policy', 'fa-user-shield', 'Privacy Policy', 'What data we collect, why, and your rights under the DPDP Act.'],
    ['terms-condition', 'fa-file-lines', 'Terms and Conditions', 'Rules for using our website, apps and services.'],
    ['fair-practice-code', 'fa-scale-balanced', 'Fair Practice Code', 'Our commitments to borrowers, in plain language.'],
    ['interest-rate-policy', 'fa-percent', 'Interest Rates, Fees and Charges', 'Rate ranges, processing fees and other charges.'],
    ['digital-lending', 'fa-mobile-screen', 'Digital Lending Disclosures', 'Lending partners, apps and the Key Fact Statement.'],
    ['grievance-redressal-policy', 'fa-headset', 'Grievance Redressal', 'How to complain, timelines and escalation to the RBI.'],
    ['refund-cancellation', 'fa-rotate-left', 'Refund and Cancellation', 'Cooling-off period, cancellation and refunds.'],
    ['delete-account', 'fa-user-xmark', 'Delete Your Account', 'Request deletion of your account and data.'],
];

$item_list = [];
foreach ($pdf_docs as $i => $doc) {
    if ($doc['available']) {
        $item_list[] = ['@type' => 'ListItem', 'position' => count($item_list) + 1, 'item' => ['@type' => 'DigitalDocument', 'name' => $doc['title'], 'description' => $doc['desc'], 'encodingFormat' => 'application/pdf', 'url' => 'https://waqtfinance.com/' . $doc['url']]];
    }
}
foreach ($web_policies as [$slug, , $title, $desc]) {
    $item_list[] = ['@type' => 'ListItem', 'position' => count($item_list) + 1, 'item' => ['@type' => 'WebPage', 'name' => $title, 'description' => $desc, 'url' => 'https://waqtfinance.com/' . $slug]];
}
$seo_schema = [[
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'Policies and Regulatory Documents',
    'url' => $canonical_url,
    'description' => $meta_description,
    'publisher' => ['@id' => 'https://waqtfinance.com/#organization'],
    'mainEntity' => ['@type' => 'ItemList', 'itemListElement' => $item_list],
]];

include 'includes/header.php';
include 'includes/main-header.php';
?>

<main class="wf-pol">
  <section class="wf-pol-hero" aria-labelledby="pol-title">
    <div class="wf-pol-wrap">
      <nav class="wf-pol-crumb" aria-label="Breadcrumb"><a href="./">Home</a><span aria-hidden="true">/</span><span aria-current="page">Policies</span></nav>
      <h1 id="pol-title">Policies and regulatory documents</h1>
      <p>Waqt Finance is an RBI-registered NBFC. These are the policies that govern how we lend, price loans, protect your data and handle complaints.</p>
      <nav class="wf-pol-jump" aria-label="On this page">
        <a href="#documents"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Policy documents</a>
        <a href="#customer-policies"><i class="fa-solid fa-book-open" aria-hidden="true"></i> Customer policies</a>
        <a href="#help"><i class="fa-solid fa-headset" aria-hidden="true"></i> Complaints and help</a>
      </nav>
    </div>
  </section>

  <section class="wf-pol-section" id="documents" aria-labelledby="docs-title">
    <div class="wf-pol-wrap">
      <div class="wf-pol-head">
        <h2 id="docs-title">Policy documents</h2>
        <p>Our official policy papers as PDF files. They open in a new tab.</p>
      </div>
      <ul class="wf-pol-docs">
        <?php foreach ($pdf_docs as $doc): ?>
        <li class="wf-pol-doc<?php echo $doc['available'] ? '' : ' is-missing'; ?>">
          <span class="wf-pol-doc-icon" aria-hidden="true"><i class="fa-solid <?php echo $h($doc['icon']); ?>"></i></span>
          <div class="wf-pol-doc-body">
            <h3><?php if ($doc['available']): ?><a href="<?php echo $h($doc['url']); ?>" target="_blank" rel="noopener"><?php echo $h($doc['title']); ?><span class="wf-pol-sr"> (PDF, opens in a new tab)</span></a><?php else: echo $h($doc['title']); endif; ?></h3>
            <p><?php echo $h($doc['desc']); ?></p>
            <span class="wf-pol-meta"><?php echo $doc['available'] ? 'PDF · ' . $h($format_size($doc['size'])) : 'Being updated. Email customercare@waqtfinance.com for a copy.'; ?></span>
          </div>
          <?php if ($doc['available']): ?>
          <div class="wf-pol-doc-actions">
            <a class="wf-pol-btn" href="<?php echo $h($doc['url']); ?>" target="_blank" rel="noopener" tabindex="-1" aria-hidden="true"><i class="fa-solid fa-eye" aria-hidden="true"></i> View</a>
            <a class="wf-pol-btn is-ghost" href="<?php echo $h($doc['url']); ?>" download aria-label="Download <?php echo $h($doc['title']); ?> PDF"><i class="fa-solid fa-download" aria-hidden="true"></i> Download</a>
          </div>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>

  <section class="wf-pol-section is-band" id="customer-policies" aria-labelledby="web-title">
    <div class="wf-pol-wrap">
      <div class="wf-pol-head">
        <h2 id="web-title">Customer policies</h2>
        <p>Easy-to-read pages you can view on any device.</p>
      </div>
      <div class="wf-pol-pages">
        <?php foreach ($web_policies as [$slug, $icon, $title, $desc]): ?>
        <a class="wf-pol-page" href="<?php echo $h($slug); ?>">
          <span aria-hidden="true"><i class="fa-solid <?php echo $h($icon); ?>"></i></span>
          <div><h3><?php echo $h($title); ?></h3><p><?php echo $h($desc); ?></p></div>
          <i class="fa-solid fa-arrow-right wf-pol-arrow" aria-hidden="true"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="wf-pol-section" id="help" aria-labelledby="help-title">
    <div class="wf-pol-wrap">
      <div class="wf-pol-help">
        <div>
          <h2 id="help-title">Have a complaint or question?</h2>
          <p>Write to us first. If your complaint is not resolved within 30 days, or you are not satisfied with our reply, you can escalate it to the RBI.</p>
        </div>
        <div class="wf-pol-help-actions">
          <a class="wf-pol-btn" href="mailto:customercare@waqtfinance.com"><i class="fa-solid fa-envelope" aria-hidden="true"></i> customercare@waqtfinance.com</a>
          <a class="wf-pol-btn is-ghost" href="tel:+919217086610"><i class="fa-solid fa-phone" aria-hidden="true"></i> +91 92170 86610</a>
          <a class="wf-pol-btn is-ghost" href="grievance-redressal-policy"><i class="fa-solid fa-route" aria-hidden="true"></i> Escalation steps</a>
          <a class="wf-pol-btn is-ghost" href="https://cms.rbi.org.in" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-building-columns" aria-hidden="true"></i> RBI complaint portal</a>
        </div>
      </div>
    </div>
  </section>
</main>

<?php
$footer_cta_title = "Ready to apply?";
$footer_cta_desc = "Apply online and see all charges in the Key Fact Statement before you accept.";
include 'includes/footer.php';
?>
