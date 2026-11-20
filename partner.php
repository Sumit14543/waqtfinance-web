<?php
// Partner (DSA) programme page.
// Backend: form-actions/submit-partner.php (action=register|login). Applications appear in Admin > Leads > Partners,
// where the team approves or rejects them. There is no partner dashboard yet, so login shows the application status.
$page_title = "Become a Loan Partner (DSA) | Waqt Finance";
$meta_description = "Join the Waqt Finance partner programme as a DSA. Refer customers for personal, business, payday, EDI, vehicle and property loans and earn commission on disbursed loans.";
$canonical_url = "https://waqtfinance.com/partner";
$breadcrumb_label = "Partner Program";
$breadcrumb_items = [['name' => 'Home', 'url' => ''], ['name' => 'Partner Program', 'url' => 'partner']];

$partner_faqs = [
    ['q' => 'Is there a fee to become a partner?', 'a' => 'No. Joining the Waqt Finance partner programme is free. Partners must also never collect any fee from customers on behalf of Waqt Finance.'],
    ['q' => 'How is commission paid?', 'a' => 'Commission is paid only on loans that are approved and disbursed. The rate for each product and the payout cycle are set out in your partner agreement, and payouts go to your registered bank account after TDS as applicable.'],
    ['q' => 'What documents do I need?', 'a' => 'PAN, Aadhaar-based KYC, a cancelled cheque or bank proof for payouts, and GST registration if you are a registered business. Our partner team will tell you if anything else is needed.'],
    ['q' => 'Which customers can I refer?', 'a' => 'Salaried and self-employed individuals and businesses who need a personal, business, payday, EDI, vehicle loan or loan against property. Every application goes through our standard credit checks; Waqt Finance takes the final lending decision.'],
    ['q' => 'How will I know the status of my referrals?', 'a' => 'Your partner manager shares status updates on the leads you refer. Customers also receive updates directly from Waqt Finance.'],
    ['q' => 'Can I start after registering online?', 'a' => 'Online registration is the first step. You can start referring customers after our team completes your KYC and you sign the partner agreement.'],
];
$seo_schema = [[
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(static fn(array $f): array => ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']]], $partner_faqs),
]];
$extra_head = '<link rel="stylesheet" href="assets/css/partner-page.css?v=1">';
$h = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

include 'includes/header.php';
include 'includes/main-header.php';
?>

<main class="wf-partner">
  <!-- Hero + application form -->
  <section class="wf-partner-hero" aria-labelledby="partner-title">
    <div class="wf-partner-wrap wf-partner-hero-grid">
      <div class="wf-partner-hero-copy">
        <nav class="wf-partner-crumb" aria-label="Breadcrumb"><a href="./">Home</a><span aria-hidden="true">/</span><span aria-current="page">Partner Program</span></nav>
        <span class="wf-partner-kicker"><i class="fa-solid fa-handshake" aria-hidden="true"></i> Partner (DSA) programme</span>
        <h1 id="partner-title">Refer customers. Earn on every disbursed loan.</h1>
        <p class="wf-partner-lead">Join Waqt Finance, an RBI-registered NBFC, as a loan partner. Refer customers for six loan products and earn commission when their loan is disbursed.</p>
        <ul class="wf-partner-facts">
          <li><strong>₹0</strong><span>Joining fee</span></li>
          <li><strong>6</strong><span>Loan products</span></li>
          <li><strong>Bank</strong><span>Payouts to your account</span></li>
        </ul>
      </div>

      <div class="wf-partner-card" id="partner-form">
        <div class="wf-partner-tabs" role="tablist" aria-label="Partner account">
          <button type="button" class="wf-partner-tab is-active" role="tab" id="tab-register" aria-selected="true" aria-controls="registerForm" data-target="register">Become a partner</button>
          <button type="button" class="wf-partner-tab" role="tab" id="tab-login" aria-selected="false" aria-controls="loginForm" data-target="login" tabindex="-1">Partner login</button>
        </div>

        <!-- Register -->
        <form id="registerForm" class="wf-partner-form is-active" role="tabpanel" aria-labelledby="tab-register" novalidate>
          <input type="hidden" name="action" value="register">
          <p class="wf-partner-form-intro">Register in a minute. Our partner team will call you to complete KYC and the partner agreement.</p>
          <div class="wf-partner-field">
            <label for="reg_name">Full name</label>
            <div class="wf-partner-input"><i class="fa-solid fa-user" aria-hidden="true"></i><input type="text" id="reg_name" name="full_name" autocomplete="name" maxlength="150" placeholder="As on your PAN" required></div>
          </div>
          <div class="wf-partner-field">
            <label for="reg_contact">Mobile number or email</label>
            <div class="wf-partner-input"><i class="fa-solid fa-at" aria-hidden="true"></i><input type="text" id="reg_contact" name="email_or_mobile" autocomplete="username" maxlength="150" placeholder="10-digit mobile or email" required></div>
          </div>
          <div class="wf-partner-field">
            <label for="registerPassword">Create password</label>
            <div class="wf-partner-input"><i class="fa-solid fa-lock" aria-hidden="true"></i><input type="password" id="registerPassword" name="password" autocomplete="new-password" minlength="8" placeholder="At least 8 characters" required aria-describedby="reg_pw_help"><button type="button" class="wf-partner-eye" data-toggle="registerPassword" aria-label="Show password"><i class="fa-solid fa-eye" aria-hidden="true"></i></button></div>
            <small id="reg_pw_help">Use at least 8 characters with a letter and a number.</small>
          </div>
          <label class="wf-partner-consent"><input type="checkbox" name="consent" value="1" required> <span>I agree to the <a href="terms-condition" target="_blank" rel="noopener">terms</a> and <a href="privacy-policy" target="_blank" rel="noopener">privacy policy</a>, and allow Waqt Finance to contact me about the partner programme.</span></label>
          <button type="submit" class="wf-partner-submit" id="registerSubmitBtn">Register as partner <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
          <div class="wf-partner-msg" role="status" aria-live="polite"></div>
        </form>

        <!-- Login -->
        <form id="loginForm" class="wf-partner-form" role="tabpanel" aria-labelledby="tab-login" novalidate hidden>
          <input type="hidden" name="action" value="login">
          <p class="wf-partner-form-intro">Already registered? Log in to see your application status.</p>
          <div class="wf-partner-field">
            <label for="login_contact">Mobile number or email</label>
            <div class="wf-partner-input"><i class="fa-solid fa-at" aria-hidden="true"></i><input type="text" id="login_contact" name="email_or_mobile" autocomplete="username" maxlength="150" required></div>
          </div>
          <div class="wf-partner-field">
            <label for="loginPassword">Password</label>
            <div class="wf-partner-input"><i class="fa-solid fa-lock" aria-hidden="true"></i><input type="password" id="loginPassword" name="password" autocomplete="current-password" required><button type="button" class="wf-partner-eye" data-toggle="loginPassword" aria-label="Show password"><i class="fa-solid fa-eye" aria-hidden="true"></i></button></div>
          </div>
          <p class="wf-partner-forgot">Forgot password? Email <a href="mailto:customercare@waqtfinance.com?subject=Partner%20password%20reset">customercare@waqtfinance.com</a> from your registered email, or call +91 92170 86610.</p>
          <button type="submit" class="wf-partner-submit" id="loginSubmitBtn">Log in <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
          <div class="wf-partner-msg" role="status" aria-live="polite"></div>
        </form>
      </div>
    </div>
  </section>

  <!-- Products partners can refer -->
  <section class="wf-partner-section" aria-labelledby="products-title">
    <div class="wf-partner-wrap">
      <h2 class="wf-partner-h2" id="products-title">Loans you can refer</h2>
      <p class="wf-partner-sub">One partnership, six products for salaried, self-employed and business customers.</p>
      <div class="wf-partner-products">
        <?php foreach ([
            ['personal-loan', 'fa-user', 'Personal Loan', 'Salaried and self-employed customers'],
            ['business-loan', 'fa-briefcase', 'Business Loan', 'MSMEs, traders and professionals'],
            ['pay-day-loan', 'fa-calendar-check', 'Payday Loan', 'Short-term needs until salary day'],
            ['edi-loan', 'fa-store', 'EDI Loan', 'Shopkeepers with daily repayments'],
            ['vehicle-loan', 'fa-car', 'Vehicle Loan', 'Cars, two-wheelers, commercial vehicles'],
            ['loan-against-property', 'fa-house', 'Loan Against Property', 'Larger needs against property'],
        ] as [$slug, $icon, $name, $who]): ?>
          <a class="wf-partner-product" href="<?php echo $h($slug); ?>"><span aria-hidden="true"><i class="fa-solid <?php echo $h($icon); ?>"></i></span><strong><?php echo $h($name); ?></strong><small><?php echo $h($who); ?></small></a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Steps -->
  <section class="wf-partner-section is-band" aria-labelledby="steps-title">
    <div class="wf-partner-wrap">
      <h2 class="wf-partner-h2" id="steps-title">How the partnership works</h2>
      <p class="wf-partner-sub">From registration to your first payout.</p>
      <ol class="wf-partner-steps">
        <li><span>1</span><h3>Register online</h3><p>Share your name and contact details using the form above.</p></li>
        <li><span>2</span><h3>KYC and agreement</h3><p>Our partner team verifies your PAN, KYC and bank details and shares the partner agreement with commission rates.</p></li>
        <li><span>3</span><h3>Refer customers</h3><p>Send leads to your partner manager. Customers apply and are assessed by Waqt Finance.</p></li>
        <li><span>4</span><h3>Get paid</h3><p>Commission on disbursed loans is paid to your bank account as per the payout cycle in your agreement.</p></li>
      </ol>
    </div>
  </section>

  <!-- Earnings illustration -->
  <section class="wf-partner-section" aria-labelledby="calc-title">
    <div class="wf-partner-wrap">
      <h2 class="wf-partner-h2" id="calc-title">Estimate your earnings</h2>
      <p class="wf-partner-sub">An illustration only. Actual rates depend on the product and are set in your partner agreement.</p>
      <div class="wf-partner-calc">
        <div class="wf-partner-calc-inputs">
          <div class="wf-partner-slider">
            <div><label for="disbursalSlider">Loans disbursed per month</label><output id="disbursalValue" for="disbursalSlider">₹10,00,000</output></div>
            <input type="range" id="disbursalSlider" min="100000" max="5000000" step="100000" value="1000000">
            <div class="wf-partner-range"><span>₹1 Lakh</span><span>₹50 Lakh</span></div>
          </div>
          <div class="wf-partner-slider">
            <div><label for="commissionSlider">Assumed commission rate</label><output id="commissionValue" for="commissionSlider">1.0%</output></div>
            <input type="range" id="commissionSlider" min="0.5" max="2" step="0.1" value="1">
            <div class="wf-partner-range"><span>0.5%</span><span>2%</span></div>
          </div>
        </div>
        <div class="wf-partner-calc-result">
          <span>Illustrative monthly commission</span>
          <strong id="payoutAmount">₹10,000</strong>
          <p>Before TDS. Paid only on approved and disbursed loans.</p>
          <a href="#partner-form" class="wf-partner-submit is-light">Register as partner <i class="fa-solid fa-arrow-up" aria-hidden="true"></i></a>
        </div>
      </div>
    </div>
  </section>

  <!-- Who can join -->
  <section class="wf-partner-section is-band" aria-labelledby="who-title">
    <div class="wf-partner-wrap">
      <h2 class="wf-partner-h2" id="who-title">Who can become a partner?</h2>
      <p class="wf-partner-sub">Individuals and firms with a network of borrowers.</p>
      <div class="wf-partner-who">
        <?php foreach ([
            ['fa-chart-line', 'Financial advisors', 'Add credit to the advice you already give.'],
            ['fa-calculator', 'Chartered accountants and tax consultants', 'Help business clients with working capital.'],
            ['fa-building', 'Property and vehicle dealers', 'Support buyers with vehicle loans and loan against property.'],
            ['fa-shield-halved', 'Insurance and mutual fund agents', 'Offer loans to your existing clients.'],
            ['fa-store', 'Shop and business associations', 'Connect members with EDI and business loans.'],
            ['fa-laptop-house', 'Freelancers and consultants', 'Refer customers alongside your current work.'],
        ] as [$icon, $title, $text]): ?>
          <div class="wf-partner-who-card"><span aria-hidden="true"><i class="fa-solid <?php echo $h($icon); ?>"></i></span><div><h3><?php echo $h($title); ?></h3><p><?php echo $h($text); ?></p></div></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Code of conduct -->
  <section class="wf-partner-section" aria-labelledby="conduct-title">
    <div class="wf-partner-wrap">
      <div class="wf-partner-conduct">
        <div>
          <h2 id="conduct-title">Partner code of conduct</h2>
          <p>Every partner acts on behalf of a regulated lender. These rules are part of the partner agreement.</p>
        </div>
        <ul>
          <li><i class="fa-solid fa-check" aria-hidden="true"></i> Tell customers you are a Waqt Finance partner and show your partner ID when asked.</li>
          <li><i class="fa-solid fa-check" aria-hidden="true"></i> Never collect any fee, cash or payment from a customer.</li>
          <li><i class="fa-solid fa-check" aria-hidden="true"></i> Never promise approval, a rate or an amount. Waqt Finance decides after assessment.</li>
          <li><i class="fa-solid fa-check" aria-hidden="true"></i> Collect customer documents only with consent, and never keep copies.</li>
          <li><i class="fa-solid fa-check" aria-hidden="true"></i> Do not call customers before 8 AM or after 7 PM, or use pressure or threats.</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="wf-partner-section is-band" aria-labelledby="faq-title">
    <div class="wf-partner-wrap wf-partner-narrow">
      <h2 class="wf-partner-h2" id="faq-title">Partner questions</h2>
      <div class="wf-partner-faq">
        <?php foreach ($partner_faqs as $i => $faq): ?>
        <details<?php echo $i === 0 ? ' open' : ''; ?>>
          <summary><?php echo $h($faq['q']); ?><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary>
          <p><?php echo $h($faq['a']); ?></p>
        </details>
        <?php endforeach; ?>
      </div>
      <p class="wf-partner-more">Questions about the programme? Email <a href="mailto:customercare@waqtfinance.com?subject=Partner%20programme">customercare@waqtfinance.com</a> or call <a href="tel:+919217086610">+91 92170 86610</a>.</p>
    </div>
  </section>
</main>

<script>
(function () {
  // Tabs
  var tabs = document.querySelectorAll('.wf-partner-tab');
  function switchTab(target, focus) {
    tabs.forEach(function (t) {
      var on = t.getAttribute('data-target') === target;
      t.classList.toggle('is-active', on);
      t.setAttribute('aria-selected', on ? 'true' : 'false');
      t.tabIndex = on ? 0 : -1;
      var form = document.getElementById(t.getAttribute('data-target') + 'Form');
      form.hidden = !on;
      form.classList.toggle('is-active', on);
      if (on && focus) t.focus();
    });
  }
  window.switchTab = switchTab;
  tabs.forEach(function (t, i) {
    t.addEventListener('click', function () { switchTab(t.getAttribute('data-target')); });
    t.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
        var next = tabs[(i + (e.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
        switchTab(next.getAttribute('data-target'), true);
      }
    });
  });
  if (location.hash === '#login') switchTab('login');

  // Show / hide password
  document.querySelectorAll('.wf-partner-eye').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.getElementById(btn.getAttribute('data-toggle'));
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      btn.querySelector('i').className = 'fa-solid ' + (show ? 'fa-eye-slash' : 'fa-eye');
    });
  });

  // Messages shown inside the form (no popup library needed)
  function say(form, text, type) {
    var box = form.querySelector('.wf-partner-msg');
    box.textContent = text;
    box.className = 'wf-partner-msg is-' + type;
  }
  function fieldError(form, input, text) {
    input.setAttribute('aria-invalid', 'true');
    input.focus();
    say(form, text, 'error');
  }

  function wire(formId, btnId) {
    var form = document.getElementById(formId);
    var btn = document.getElementById(btnId);
    form.addEventListener('input', function (e) { e.target.removeAttribute('aria-invalid'); });
    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      var data = new FormData(form);
      var action = data.get('action');
      var contact = String(data.get('email_or_mobile') || '').trim();
      var password = String(data.get('password') || '');
      var isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(contact);
      var isMobile = /^[6-9]\d{9}$/.test(contact);

      if (action === 'register') {
        var name = String(data.get('full_name') || '').trim();
        if (name.length < 3) return fieldError(form, form.full_name, 'Please enter your full name (at least 3 letters).');
        if (!isEmail && !isMobile) return fieldError(form, form.email_or_mobile, 'Enter a valid 10-digit mobile number or email address.');
        if (password.length < 8 || !/[A-Za-z]/.test(password) || !/\d/.test(password)) return fieldError(form, form.password, 'Use at least 8 characters with a letter and a number.');
        if (!form.consent.checked) return fieldError(form, form.consent, 'Please accept the terms and privacy policy to continue.');
      } else if (!contact || !password) {
        return fieldError(form, contact ? form.password : form.email_or_mobile, 'Enter your mobile number or email and your password.');
      }

      var original = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = 'Please wait <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>';
      say(form, '', 'info');
      try {
        var res = await fetch('form-actions/submit-partner.php', { method: 'POST', headers: { 'X-CSRF-Token': window.WAQT_CSRF_TOKEN || '' }, body: data, credentials: 'same-origin' });
        var json = await res.json().catch(function () { return {}; });
        if (json.success) {
          say(form, json.message || 'Done.', 'success');
          form.reset();
          if (action === 'register') {
            var loginContact = document.getElementById('login_contact');
            if (loginContact) loginContact.value = contact;
          }
        } else {
          say(form, json.message || 'Something went wrong. Please try again.', 'error');
        }
      } catch (err) {
        say(form, 'We could not reach the server. Check your connection and try again.', 'error');
      } finally {
        btn.disabled = false;
        btn.innerHTML = original;
      }
    });
  }
  wire('registerForm', 'registerSubmitBtn');
  wire('loginForm', 'loginSubmitBtn');

  // Earnings illustration
  var d = document.getElementById('disbursalSlider'), c = document.getElementById('commissionSlider');
  var rupee = function (n) { return '₹' + Math.round(n).toLocaleString('en-IN'); };
  function calc() {
    document.getElementById('disbursalValue').textContent = rupee(+d.value);
    document.getElementById('commissionValue').textContent = (+c.value).toFixed(1) + '%';
    document.getElementById('payoutAmount').textContent = rupee(+d.value * +c.value / 100);
  }
  d.addEventListener('input', calc); c.addEventListener('input', calc); calc();
})();
</script>

<?php
$footer_cta_title = "Need a loan for yourself?";
$footer_cta_desc = "Apply online and see all charges in the Key Fact Statement before you accept.";
include 'includes/footer.php';
?>
