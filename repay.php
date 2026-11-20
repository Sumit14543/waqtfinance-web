<?php
// Loan repayment page.
// Payments are taken only through official channels: the secure payment link sent from the
// Waqt Finance CRM (token based) and the official UPI QR on /pay-online.
// The old PAN + OTP form on this page was not connected to any backend, so it has been replaced.
$page_title = "Repay Your Loan | Pay EMI Online | Waqt Finance";
$meta_description = "Pay your Waqt Finance EMI or dues through official channels only: the secure payment link we send you or our UPI QR. Get help with dues, receipts and foreclosure.";
$meta_keywords = "Waqt Finance loan repayment, pay EMI online, loan payment link, UPI EMI payment, loan foreclosure";
$canonical_url = "https://waqtfinance.com/repay";
$breadcrumb_label = "Repay Loan";
$breadcrumb_items = [['name' => 'Home', 'url' => ''], ['name' => 'Repay Loan', 'url' => 'repay']];

$repay_phone_display = '+91 92170 86610';
$repay_phone_tel = '+919217086610';
$repay_whatsapp = 'https://wa.me/919217086610';
$repay_email = 'customercare@waqtfinance.com';

$repay_faqs = [
    ['q' => 'How can I repay my loan?', 'a' => 'Use the secure payment link we send to your registered mobile number or email, or scan our official UPI QR on the Pay Online page. If you do not have a link, contact customer care and we will send one.'],
    ['q' => 'How do I know a payment link is genuine?', 'a' => 'Genuine links come from Waqt Finance to the mobile number or email registered on your loan, and show your name and loan details before you pay. Our team will never ask you to pay into a personal bank account or a personal UPI ID. If in doubt, call customer care before paying.'],
    ['q' => 'I paid but the payment is not showing. What should I do?', 'a' => 'Bank and UPI payments can take some time to reflect. If it does not show by the next working day, send your loan account number and the UTR or transaction ID to ' . $repay_email . ' or on WhatsApp, and we will check and update your account.'],
    ['q' => 'Can I pay part of my dues or close my loan early?', 'a' => 'Contact customer care for the exact amount due or a foreclosure statement. Part-payment and foreclosure are handled as per your loan agreement and Key Fact Statement, which list any applicable charges.'],
    ['q' => 'What happens if I miss an EMI?', 'a' => 'Late payment charges may apply as per your loan agreement, and repayment history is reported to credit bureaus, which can affect your credit score. If you expect difficulty, contact us before the due date to discuss your options.'],
    ['q' => 'How do I report a fraud call or a fake payment request?', 'a' => 'Do not pay. Email ' . $repay_email . ' with the number or link that contacted you. You can also report cyber fraud at cybercrime.gov.in or by calling 1930.'],
];
$seo_schema = [[
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(static fn(array $f): array => ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']]], $repay_faqs),
]];
$extra_head = '<link rel="stylesheet" href="assets/css/repay-page.css?v=1">';
$h = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

include 'includes/header.php';
include 'includes/main-header.php';
?>

<main class="wf-repay">
  <!-- Hero + payment options -->
  <section class="wf-repay-hero" aria-labelledby="repay-title">
    <div class="wf-repay-wrap wf-repay-hero-grid">
      <div class="wf-repay-hero-copy">
        <nav class="wf-repay-crumb" aria-label="Breadcrumb"><a href="./">Home</a><span aria-hidden="true">/</span><span aria-current="page">Repay Loan</span></nav>
        <span class="wf-repay-kicker"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Official payment channels</span>
        <h1 id="repay-title">Repay your Waqt Finance loan</h1>
        <p class="wf-repay-lead">Pay your EMI or dues only through the secure payment link we send you or our official UPI QR. Never pay into a personal bank account or UPI ID, even if the caller says they are from Waqt Finance.</p>
        <ul class="wf-repay-points">
          <li><i class="fa-solid fa-building-columns" aria-hidden="true"></i> RBI-registered NBFC</li>
          <li><i class="fa-solid fa-lock" aria-hidden="true"></i> Secure, encrypted payment pages</li>
          <li><i class="fa-solid fa-headset" aria-hidden="true"></i> Help Mon to Sat, 10 AM to 6 PM</li>
        </ul>
      </div>

      <div class="wf-repay-card" id="pay-options">
        <h2>Choose how to pay</h2>
        <p class="wf-repay-card-sub">Keep your loan account number handy.</p>

        <div class="wf-repay-option is-primary">
          <span class="wf-repay-option-icon" aria-hidden="true"><i class="fa-solid fa-link"></i></span>
          <div>
            <h3>Payment link from us</h3>
            <p>Open the secure link sent to your registered mobile number or email by SMS, WhatsApp or email. It shows your amount and supports UPI, cards and net banking.</p>
            <a class="wf-repay-text-link" href="#verify-link">How to check a link is genuine <i class="fa-solid fa-arrow-down" aria-hidden="true"></i></a>
          </div>
        </div>

        <div class="wf-repay-option">
          <span class="wf-repay-option-icon" aria-hidden="true"><i class="fa-solid fa-qrcode"></i></span>
          <div>
            <h3>Scan and pay by UPI</h3>
            <p>Use our official QR with any UPI app, then share the receipt with us on WhatsApp.</p>
            <a class="wf-repay-btn" href="pay-online">Open UPI QR <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
          </div>
        </div>

        <div class="wf-repay-option">
          <span class="wf-repay-option-icon" aria-hidden="true"><i class="fa-solid fa-comment-dots"></i></span>
          <div>
            <h3>No link or need your due amount?</h3>
            <p>Ask customer care for a payment link, your outstanding amount or a foreclosure statement.</p>
            <div class="wf-repay-contact-row">
              <a class="wf-repay-btn is-whatsapp" href="<?php echo $h($repay_whatsapp); ?>" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp</a>
              <a class="wf-repay-btn is-ghost" href="tel:<?php echo $h($repay_phone_tel); ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i> <?php echo $h($repay_phone_display); ?></a>
            </div>
            <a class="wf-repay-text-link" href="mailto:<?php echo $h($repay_email); ?>"><?php echo $h($repay_email); ?></a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Security alert (always visible, English + Hindi) -->
  <section class="wf-repay-section" id="verify-link" aria-labelledby="alert-title">
    <div class="wf-repay-wrap">
      <div class="wf-repay-alert">
        <div class="wf-repay-alert-head">
          <span class="wf-repay-alert-icon" aria-hidden="true"><i class="fa-solid fa-triangle-exclamation"></i></span>
          <div>
            <h2 id="alert-title">Security alert <span lang="hi">/ सुरक्षा चेतावनी</span></h2>
            <p>Fraudsters send fake payment requests on WhatsApp, SMS and social media while pretending to be Waqt Finance staff.</p>
            <p lang="hi">धोखेबाज़ WhatsApp, SMS और सोशल मीडिया पर Waqt Finance के कर्मचारी बनकर नकली भुगतान अनुरोध भेज रहे हैं।</p>
          </div>
        </div>
        <div class="wf-repay-alert-grid">
          <div class="is-do">
            <h3><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Do <span lang="hi">/ करें</span></h3>
            <ul>
              <li>Pay only by our payment link or the QR on <strong>waqtfinance.com/pay-online</strong>.<span lang="hi">भुगतान केवल हमारे लिंक या आधिकारिक QR से करें।</span></li>
              <li>Check that the payment page shows your name and loan details.<span lang="hi">भुगतान पेज पर अपना नाम और लोन विवरण जांचें।</span></li>
              <li>Call <?php echo $h($repay_phone_display); ?> if anything looks wrong.<span lang="hi">कुछ भी संदिग्ध लगे तो हमें कॉल करें।</span></li>
            </ul>
          </div>
          <div class="is-dont">
            <h3><i class="fa-solid fa-circle-xmark" aria-hidden="true"></i> Don't <span lang="hi">/ न करें</span></h3>
            <ul>
              <li>Pay into a personal bank account or personal UPI ID.<span lang="hi">किसी निजी खाते या निजी UPI ID में भुगतान न करें।</span></li>
              <li>Share OTP, PIN, CVV or passwords with anyone, including our staff.<span lang="hi">OTP, PIN, CVV या पासवर्ड किसी से साझा न करें।</span></li>
              <li>Click unknown links or reply to suspicious messages.<span lang="hi">अज्ञात लिंक पर क्लिक न करें।</span></li>
            </ul>
          </div>
        </div>
        <p class="wf-repay-alert-foot">Waqt Finance is not responsible for payments made to unverified persons or links. Report fraud at <a href="mailto:<?php echo $h($repay_email); ?>"><?php echo $h($repay_email); ?></a>, at <a href="https://cybercrime.gov.in" target="_blank" rel="noopener noreferrer">cybercrime.gov.in</a> or on helpline 1930.</p>
      </div>
    </div>
  </section>

  <!-- Steps -->
  <section class="wf-repay-section is-band" aria-labelledby="steps-title">
    <div class="wf-repay-wrap">
      <h2 class="wf-repay-h2" id="steps-title">How repayment works</h2>
      <p class="wf-repay-sub">Four steps, whichever way you pay.</p>
      <ol class="wf-repay-steps">
        <li><span>1</span><h3>Check your due amount</h3><p>Your EMI amount and due date are in your loan agreement and repayment schedule. Ask us if you are not sure.</p></li>
        <li><span>2</span><h3>Open an official channel</h3><p>Use the payment link we sent you or the UPI QR on our Pay Online page.</p></li>
        <li><span>3</span><h3>Pay and keep the reference</h3><p>Note the UTR or transaction ID and keep a screenshot of the receipt.</p></li>
        <li><span>4</span><h3>Confirm with us</h3><p>Link payments update automatically. For QR payments, share the receipt on WhatsApp with your loan account number.</p></li>
      </ol>
    </div>
  </section>

  <!-- FAQ -->
  <section class="wf-repay-section" aria-labelledby="faq-title">
    <div class="wf-repay-wrap wf-repay-narrow">
      <h2 class="wf-repay-h2" id="faq-title">Repayment questions</h2>
      <div class="wf-repay-faq">
        <?php foreach ($repay_faqs as $i => $faq): ?>
        <details<?php echo $i === 0 ? ' open' : ''; ?>>
          <summary><?php echo $h($faq['q']); ?><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary>
          <p><?php echo $h($faq['a']); ?></p>
        </details>
        <?php endforeach; ?>
      </div>
      <p class="wf-repay-more">Have a complaint about a payment? See our <a href="grievance-redressal-policy">grievance redressal policy</a> or <a href="contact-us">contact us</a>.</p>
    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
