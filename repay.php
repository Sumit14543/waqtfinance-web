<?php 
$page_title = "Loan Repay | Waqt Finance - Pay EMI & Manage Loan";
$meta_description = "Easily manage your loan repayment with Waqt Finance. Pay EMI, check outstanding balance, view repayment schedule, and get complete loan account details online.";
$meta_keywords = "loan repayment Waqt Finance, EMI payment, loan EMI online, repayment schedule, NBFC loan payment, loan account status, pay EMI online India";
$canonical_url = "https://waqtfinance.com/repay";
?>


<?php include 'includes/header.php' ?>
<?php include 'includes/main-header.php' ?>

<style>
  .wf-alert-checkbox-wrap {
    position: relative;
    top: -9px;
  }
  .wf-alert-checkbox-text {
    position: relative;
    top: -2px;
    left: 5px;
    font-weight: 500;
    color: grey;
  }

  .wf-alert-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6) !important;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 9999;
    backdrop-filter: blur(4px) !important;
    transition: opacity 0.25s ease !important;
  }

  /* Modal */
  .wf-alert-modal {
    width: 100%;
    max-width: 520px;
    max-height: 85vh;
    overflow: hidden;
    background: #ffffff !important;
    border-radius: 12px !important;
    box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25) !important;
    display: flex;
    flex-direction: column;
    border: 1px solid #e2e8f0 !important;
  }

  /* Header */
  .wf-alert-header {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #062b5d !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    padding: 18px 24px !important;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .wf-alert-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .wf-alert-icon {
    font-size: 20px;
    color: #ffffff;
  }

  .wf-alert-title {
    font-size: 16.5px !important;
    font-weight: 800 !important;
    color: #ffffff !important;
    font-family: 'Inter', sans-serif !important;
    margin: 0 !important;
  }

  .wf-alert-close {
    border: none;
    background: none;
    font-size: 24px;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.8);
    line-height: 1;
    transition: color 0.2s ease;
  }

  .wf-alert-close:hover {
    color: #ffffff;
  }

  /* Body */
  .wf-alert-body {
    padding: 24px !important;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
    background: #f8fafc !important;
  }

  /* Intro Box */
  .wf-alert-main-box {
    border-left: 4px solid #18529c !important;
    background: #f0f7ff !important;
    border-radius: 8px !important;
    padding: 16px !important;
    color: #1e3a8a !important;
    font-size: 14.5px !important;
    line-height: 1.6 !important;
    box-shadow: none !important;
    border: 1px solid #dbeafe !important;
    border-left-width: 4px !important;
  }

  .wf-brand {
    font-weight: 700;
    color: #18529c;
  }

  /* Tips */
  .wf-alert-tip {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    border: 1px solid #e2e8f0 !important;
    background: #ffffff !important;
    border-radius: 8px !important;
    padding: 16px !important;
    box-shadow: none !important;
  }

  .wf-alert-check {
    width: 24px !important;
    height: 24px !important;
    min-width: 24px !important;
    border-radius: 50%;
    background: #e6f4ea !important;
    color: #137333 !important;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px !important;
    font-weight: 700;
  }

  .wf-alert-tip-text {
    font-size: 14px !important;
    color: #334155 !important;
    line-height: 1.5 !important;
  }

  .wf-alert-tip-text p {
    margin-bottom: 6px !important;
  }
  .wf-alert-tip-text p:last-child {
    margin-bottom: 0 !important;
  }

  /* Warning */
  .wf-alert-warning {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    border: 1px solid #fca5a5 !important;
    background: #fef2f2 !important;
    border-radius: 8px !important;
    padding: 16px !important;
    color: #991b1b !important;
    font-size: 14px !important;
    line-height: 1.6 !important;
    box-shadow: none !important;
  }

  .wf-alert-warning-icon {
    font-size: 18px;
    color: #ef4444;
  }

  /* Safe Payment */
  .wf-alert-safe {
    border: 1px solid #cbd5e1 !important;
    background: #ffffff !important;
    border-radius: 8px !important;
    padding: 16px !important;
    box-shadow: none !important;
  }

  .wf-alert-safe-title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    color: #0f2742 !important;
    font-size: 15px !important;
    font-weight: 700 !important;
  }

  .wf-alert-safe-content {
    font-size: 14px !important;
    color: #334155 !important;
    line-height: 1.6 !important;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .wf-alert-team {
    text-align: right;
    font-size: 13px !important;
    color: #64748b !important;
    font-style: italic;
    font-weight: 600;
  }

  /* Footer */
  .wf-alert-footer {
    position: sticky;
    bottom: 0;
    background: #ffffff !important;
    border-top: 1px solid #e2e8f0 !important;
    padding: 20px 24px !important;
  }

  /* Checkbox */
  .wf-alert-checkbox-wrap {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 14px;
    cursor: pointer;
  }

  .wf-alert-checkbox {
    width: 18px !important;
    height: 18px !important;
    accent-color: #18529c;
    margin-top: 2px;
    cursor: pointer;
  }

  .wf-alert-checkbox-text {
    font-size: 14px;
    color: #374151;
    line-height: 1.5;
    font-weight: 500;
  }

  /* Button */
  .wf-alert-btn {
    width: 100%;
    border: none;
    cursor: pointer;
    padding: 14px 18px !important;
    border-radius: 8px !important;
    color: #ffffff;
    font-size: 16px !important;
    font-weight: 700 !important;
    background: linear-gradient(135deg, #18529c 0%, #0b213a 100%) !important;
    box-shadow: 0 4px 6px rgba(24, 82, 156, 0.15) !important;
    transition: 0.3s;
  }

  .wf-alert-btn:hover {
    background: linear-gradient(135deg, #0b213a 0%, #062b5d 100%) !important;
    transform: translateY(-1.5px);
  }

  .wf-alert-overlay {
    display: none;
  }

  .wf-alert-overlay.active {
    display: flex;
  }

  @media(max-width:600px) {
    .wf-alert-title {
      font-size: 15px !important;
    }
    .wf-alert-btn {
      font-size: 15px !important;
    }
    .wf-alert-main-box,
    .wf-alert-tip-text,
    .wf-alert-warning,
    .wf-alert-safe-content {
      font-size: 13.5px !important;
    }
  }

  /* ==========================================================================
     WAQT BRAND OVERRIDES (NAVY & GREEN THEME)
     ========================================================================== */
  .reapyloan-hero,
  .reapyloan-hero * {
    font-family: 'Inter', sans-serif !important;
  }

  .reapyloan-hero {
    background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%) !important;
    padding: 80px 0 100px 0 !important;
    color: #0f2742 !important;
    position: relative;
    overflow: hidden;
  }

  .reapyloan-badge {
    border-color: #cbd5e1 !important;
    background: #ffffff !important;
  }

  .reapyloan-dot {
    background: #18529c !important;
  }

  .reapyloan-badge span:last-child {
    color: #18529c !important;
  }

  .reapyloan-title {
    color: #0f2742 !important;
    font-size: clamp(32px, 5vw, 48px) !important;
    font-weight: 800 !important;
    letter-spacing: -1px !important;
    line-height: 1.15 !important;
  }

  .reapyloan-title span, 
  .reapyloan-process-title span {
    background: none !important;
    -webkit-background-clip: unset !important;
    -webkit-text-fill-color: initial !important;
    color: #18529c !important;
  }

  .reapyloan-desc {
    color: #475569 !important;
    font-size: 16px !important;
    line-height: 1.6 !important;
  }

  .reapyloan-feature span {
    color: #475569 !important;
  }

  .reapyloan-feature i {
    color: #10b981 !important; /* Verified green */
  }

  .reapyloan-card-glow {
    background: #18529c !important;
    opacity: 0.12 !important;
  }

  .reapyloan-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.1) !important;
    padding: 32px 28px !important;
  }

  .reapyloan-card-head h2 {
    color: #0f2742 !important;
    font-size: 22px !important;
    font-weight: 800 !important;
  }

  .reapyloan-secure-tag {
    background: #e0f2fe !important;
    color: #0369a1 !important;
    font-weight: 700 !important;
  }

  .reapyloan-support {
    color: #475569 !important;
    background: #f8fafc !important;
    padding: 12px 16px !important;
    border-radius: 8px !important;
    border: 1px solid #e2e8f0 !important;
    font-size: 13.5px !important;
  }

  .reapyloan-support a {
    color: #18529c !important;
    font-weight: 600;
  }

  .reapyloan-input {
    height: 48px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    font-size: 15px !important;
    color: #0f2742 !important;
    padding-left: 44px !important;
    outline: none !important;
  }

  .reapyloan-input:focus, 
  .reapyloan-otp-input:focus {
    border-color: #18529c !important;
    box-shadow: 0 0 0 3px rgba(24, 82, 156, 0.15) !important;
  }

  .reapyloan-btn {
    height: 50px !important;
    background: linear-gradient(135deg, #18529c 0%, #0b213a 100%) !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    color: #ffffff !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 4px 6px rgba(24, 82, 156, 0.15) !important;
  }

  .reapyloan-btn:hover {
    background: linear-gradient(135deg, #0b213a 0%, #062b5d 100%) !important;
    box-shadow: 0 10px 15px rgba(24, 82, 156, 0.25) !important;
    transform: translateY(-1.5px) !important;
  }

  .reapyloan-link-btn:hover, 
  .reapyloan-resend {
    color: #18529c !important;
  }

  /* Process Steps */
  .reapyloan-step-card {
    border: 1px solid #e2e8f0 !important;
    background: #ffffff !important;
    border-radius: 12px !important;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05) !important;
    transition: all 0.3s ease !important;
  }

  .reapyloan-step-card:hover {
    transform: translateY(-4px) !important;
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05) !important;
  }

  .reapyloan-step-icon {
    color: #18529c !important;
    background: #f0f7ff !important;
  }

  .reapyloan-step-number {
    color: rgba(24, 82, 156, 0.1) !important;
  }

  .reapyloan-step-highlight {
    background: linear-gradient(135deg, #18529c, #1e40af) !important;
    color: #fff !important;
    border: none !important;
  }

  .reapyloan-step-highlight .reapyloan-step-icon {
    background: rgba(255, 255, 255, 0.2) !important;
    color: #fff !important;
  }

  .reapyloan-step-highlight .reapyloan-step-number {
    color: rgba(255, 255, 255, 0.3) !important;
  }

  /* FAQ Styles */
  .reapyloan-faq-item {
    border: 1px solid #e2e8f0 !important;
    background: #ffffff !important;
    border-radius: 8px !important;
    margin-bottom: 12px !important;
  }

  .reapyloan-faq-item.active {
    border-color: #18529c !important;
    box-shadow: 0 4px 6px -1px rgba(24, 82, 156, 0.05) !important;
  }

  .reapyloan-faq-question {
    color: #0f2742 !important;
    font-weight: 700 !important;
  }

  .reapyloan-faq-question i {
    color: #18529c !important;
  }

  /* Checkbox Card Styling */
  .wf-alert-checkbox-wrap {
    display: flex !important;
    align-items: flex-start !important;
    gap: 12px !important;
    margin-bottom: 20px !important;
    cursor: pointer !important;
    padding: 16px !important;
    background: #f0f7ff !important;
    border: 1px solid #dbeafe !important;
    border-radius: 8px !important;
    transition: all 0.2s ease !important;
    position: static !important;
    box-shadow: none !important;
  }

  .wf-alert-checkbox-wrap:hover {
    background: #e0f2fe !important;
    border-color: #bae6fd !important;
  }

  .wf-alert-checkbox {
    width: 18px !important;
    height: 18px !important;
    accent-color: #18529c !important;
    margin-top: 2px !important;
    border: 1.5px solid #cbd5e1 !important;
    border-radius: 4px !important;
  }

  .wf-alert-checkbox-text {
    font-size: 13px !important;
    color: #334155 !important;
    line-height: 1.45 !important;
    font-weight: 500 !important;
    position: static !important;
    left: 0 !important;
    top: 0 !important;
    text-transform: none !important;
    letter-spacing: normal !important;
  }

  /* Relative wrapper for Input Icons */
  .wf-input-wrapper {
    position: relative;
    width: 100%;
  }

  .wf-input-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
    font-size: 15px;
    z-index: 10;
  }
</style>



<!-- HERO -->
<section class="reapyloan-hero">
  <div class="reapyloan-bg-blur-one"></div>
  <div class="reapyloan-bg-blur-two"></div>

  <div class="reapyloan-container">

    <div>
      <div class="reapyloan-badge">
        <span class="reapyloan-dot"></span>
        <span>Secure Payment Gateway</span>
      </div>

      <h1 class="reapyloan-title">
        Repay Your Loan Online Securely
      </h1>

      <p class="reapyloan-desc">
        Verify your details, generate a one-time password and clear your dues in
        under sixty seconds. Bank-grade encryption, zero convenience fees.
      </p>

      <div class="reapyloan-features">
        <div class="reapyloan-feature">
          <i class="fa-solid fa-circle-check"></i>
          <span>RBI-registered NBFC</span>
        </div>
        <div class="reapyloan-feature">
          <i class="fa-solid fa-shield-halved"></i>
          <span>256-bit encryption</span>
        </div>
        <div class="reapyloan-feature">
          <i class="fa-solid fa-bolt"></i>
          <span>Instant confirmation</span>
        </div>
      </div>
    </div>

    <div class="reapyloan-card-wrap">
      <div class="reapyloan-card-glow"></div>

      <div class="reapyloan-card">
        <div class="reapyloan-card-head">
          <div>
            <h2>Repay Loan</h2>
            <p class="reapyloan-card-sub">Verify your details before making any payment.</p>
          </div>
          <span class="reapyloan-secure-tag">Secure</span>
        </div>

        <p class="reapyloan-support">
          For support contact
          <a href="mailto:customercare@waqtfinance.com">customercare@waqtfinance.com</a>
        </p>

        <form id="reapyloan-pan-form">
          <div class="reapyloan-form-group">
            <label class="reapyloan-label" for="reapyloan-pan">PAN Number</label>
            <div class="wf-input-wrapper">
              <i class="fas fa-id-card wf-input-icon"></i>
              <input type="text" id="reapyloan-pan" class="reapyloan-input" maxlength="10" placeholder="ABCDE1234F">
            </div>
            <p class="reapyloan-help-text">Login using your PAN to receive OTP.</p>
          </div>
          
           <label class="wf-alert-checkbox-wrap">

  <input type="checkbox" class="wf-alert-checkbox" id="wfCheck">

  <span class="wf-alert-checkbox-text">
    I have read the Security Alert /
    मैंने सुरक्षा चेतावनी पढ़ ली है
  </span>

</label>


          <button type="submit" class="reapyloan-btn">
            Get OTP <i class="fa-solid fa-arrow-right"></i>
          </button>
        </form>

        <form id="reapyloan-otp-form" class="reapyloan-hidden">
          <div class="reapyloan-form-group">
            <label class="reapyloan-label">Enter OTP</label>
            <p class="reapyloan-help-text">
              Sent to mobile linked with PAN <strong id="reapyloan-pan-show"></strong>
            </p>

            <div class="reapyloan-otp-boxes">
              <input class="reapyloan-otp-input" maxlength="1">
              <input class="reapyloan-otp-input" maxlength="1">
              <input class="reapyloan-otp-input" maxlength="1">
              <input class="reapyloan-otp-input" maxlength="1">
              <input class="reapyloan-otp-input" maxlength="1">
              <input class="reapyloan-otp-input" maxlength="1">
            </div>

            <div class="reapyloan-actions">
              <button type="button" id="reapyloan-change-pan" class="reapyloan-link-btn">
                <i class="fa-solid fa-arrow-left"></i> Change PAN
              </button>

              <button type="button" id="reapyloan-resend-otp" class="reapyloan-link-btn reapyloan-resend">
                Resend OTP
              </button>
            </div>
          </div>

          <button type="submit" class="reapyloan-btn">
            Verify & Continue <i class="fa-solid fa-arrow-right"></i>
          </button>
        </form>

        <p class="reapyloan-terms">
          By proceeding, you agree to our terms and privacy policy.
        </p>
      </div>
    </div>

  </div>
</section>


<!--model here -->
<div class="wf-alert-overlay" id="wfPopup">

  <div class="wf-alert-modal">

    <!-- Header -->

    <div class="wf-alert-header">

      <div class="wf-alert-header-left">

        <span class="wf-alert-icon">⚠️</span>

        <h2 class="wf-alert-title">
          Security Alert / सुरक्षा चेतावनी
        </h2>

      </div>

      <button class="wf-alert-close" onclick="document.getElementById('wfPopup').style.display='none'">
        &times;
      </button>

    </div>

    <!-- Body -->

    <div class="wf-alert-body">

      <!-- Intro -->

      <div class="wf-alert-main-box">

        <p>
          <strong>English:</strong>
          Fraudsters are sending fake payment requests via WhatsApp,
          SMS, and social media, impersonating
          <span class="wf-brand">Waqt Finance</span>
          employees. Stay alert:
        </p>

        <br>

        <p>
          <strong>हिंदी:</strong>
          धोखेबाज़ WhatsApp, SMS और सोशल मीडिया के माध्यम से नकली
          भुगतान अनुरोध भेज रहे हैं, जो
          <span class="wf-brand">Waqt Finance</span>
          के कर्मचारियों के रूप में पहचान बना रहे हैं।
          सतर्क रहें:
        </p>

      </div>

      <!-- Tip -->

      <div class="wf-alert-tip">

        <div class="wf-alert-check">✓</div>

        <div class="wf-alert-tip-text">

          <p>
            <strong>English:</strong>
            Do not click unknown links or reply to suspicious messages.
          </p>

          <p>
            <strong>हिंदी:</strong>
            अज्ञात लिंक पर क्लिक न करें या संदिग्ध संदेशों का जवाब न दें।
          </p>

        </div>

      </div>

      <!-- Tip -->

      <div class="wf-alert-tip">

        <div class="wf-alert-check">✓</div>

        <div class="wf-alert-tip-text">

          <p>
            <strong>English:</strong>
            Verify email IDs and domains before engaging.
          </p>

          <p>
            <strong>हिंदी:</strong>
            संलग्न होने से पहले ईमेल ID और डोमेन सत्यापित करें।
          </p>

        </div>

      </div>

      <!-- Tip -->

      <div class="wf-alert-tip">

        <div class="wf-alert-check">✓</div>

        <div class="wf-alert-tip-text">

          <p>
            <strong>English:</strong>
            Be cautious of calls or messages claiming to be from us.
          </p>

          <p>
            <strong>हिंदी:</strong>
            हमारे होने का दावा करने वाले कॉल या संदेशों से सावधान रहें।
          </p>

        </div>

      </div>

      <!-- Warning -->

      <div class="wf-alert-warning">

        <div class="wf-alert-warning-icon">⚠️</div>

        <div>

          <p>
            <strong>English:</strong>
            <span class="wf-brand">Waqt Finance</span>
            is not responsible for losses from payments to unverified
            persons or links.
          </p>

          <br>

          <p>
            <strong>हिंदी:</strong>
            <span class="wf-brand">Waqt Finance</span>
            अप्रमाणित व्यक्तियों या लिंक को भुगतान से होने वाले नुकसान
            के लिए जिम्मेदार नहीं है।
          </p>

        </div>

      </div>

      <!-- Safe Payment -->

      <div class="wf-alert-safe">

        <div class="wf-alert-safe-title">
          🔒 Safe Payment / सुरक्षित भुगतान
        </div>

        <div class="wf-alert-safe-content">

          <div>

            <p>
              <strong>हिंदी:</strong>
              payment केवल हमारी आधिकारिक वेबसाइट
              <span class="wf-brand">Waqt Finance</span>
              या अधिकृत लिंक से करें।
            </p>

            <p>
              <strong>English:</strong>
              Pay only through our official website
              <span class="wf-brand">Waqt Finance</span>
              or authorized link.
            </p>

          </div>

          <div>

            <p>
              <strong>हिंदी:</strong>
              किसी अज्ञात नंबर/लिंक पर भरोसा न करें।
            </p>

            <p>
              <strong>English:</strong>
              Do not trust any unknown number/link.
            </p>

          </div>

        </div>

      </div>

      <div class="wf-alert-team">
        - Team Waqt Finance
      </div>

    </div>

    <!-- Footer -->

    <div class="wf-alert-footer">

      <label class="wf-alert-checkbox-wrap">

        <input
          type="checkbox"
          class="wf-alert-checkbox"
          onclick="document.getElementById('wfPopup').style.display='none'"
        >

        <span class="wf-alert-checkbox-text">
          I have read the Security Alert /
          मैंने सुरक्षा चेतावनी पढ़ ली है
        </span>

      </label>

      <button class="wf-alert-btn">
        I UNDERSTAND / मैं समझ गया
      </button>

    </div>

  </div>

</div>

<!--end --model-->





<!-- PROCESS -->
<section class="reapyloan-process">
  <div class="reapyloan-process-container">

    <div class="reapyloan-process-head">
    

      <h2 class="reapyloan-process-title">
        Simple & Secure Loan Repayment
      </h2>

      <p class="reapyloan-process-desc">
        We've made loan repayment straightforward and stress-free. Here's how you can do it.
      </p>
    </div>

    <div class="reapyloan-process-grid">

      <div class="reapyloan-step-card">
        <div class="reapyloan-step-top">
          <div class="reapyloan-step-icon"><i class="fa-solid fa-user-check"></i></div>
          <span class="reapyloan-step-number">01</span>
        </div>
        <h3>Start with the Repayment Link</h3>
        <p>Access the repayment option through our website or a link shared directly with you.</p>
      </div>

      <div class="reapyloan-step-card">
        <div class="reapyloan-step-top">
          <div class="reapyloan-step-icon"><i class="fa-solid fa-id-card"></i></div>
          <span class="reapyloan-step-number">02</span>
        </div>
        <h3>Enter Basic Details</h3>
        <p>Provide your PAN or registered credentials so we can fetch your loan account.</p>
      </div>

      <div class="reapyloan-step-card">
        <div class="reapyloan-step-top">
          <div class="reapyloan-step-icon"><i class="fa-solid fa-key"></i></div>
          <span class="reapyloan-step-number">03</span>
        </div>
        <h3>Verify Yourself</h3>
        <p>An OTP will be sent to your registered mobile number to confirm your identity.</p>
      </div>

      <div class="reapyloan-step-card">
        <div class="reapyloan-step-top">
          <div class="reapyloan-step-icon"><i class="fa-solid fa-wallet"></i></div>
          <span class="reapyloan-step-number">04</span>
        </div>
        <h3>View Loan Information</h3>
        <p>Check your outstanding balance, next due date and repayment options clearly displayed.</p>
      </div>

      <div class="reapyloan-step-card">
        <div class="reapyloan-step-top">
          <div class="reapyloan-step-icon"><i class="fa-solid fa-credit-card"></i></div>
          <span class="reapyloan-step-number">05</span>
        </div>
        <h3>Choose Payment Amount</h3>
        <p>Decide whether to pay your full balance or make a partial payment.</p>
      </div>

      <div class="reapyloan-step-card">
        <div class="reapyloan-step-top">
          <div class="reapyloan-step-icon"><i class="fa-solid fa-shield-halved"></i></div>
          <span class="reapyloan-step-number">06</span>
        </div>
        <h3>Proceed to Payment</h3>
        <p>Select Pay Now and choose from UPI, debit/credit card, or net banking.</p>
      </div>

      <div class="reapyloan-step-card reapyloan-step-highlight">
        <div class="reapyloan-step-top">
          <div class="reapyloan-step-icon"><i class="fa-solid fa-circle-check"></i></div>
          <span class="reapyloan-step-number">07</span>
        </div>
        <h3>Receive Instant Confirmation</h3>
        <p>After successful payment, you'll get confirmation via SMS and email immediately.</p>
      </div>

    </div>
  </div>
</section>

<!-- FAQ -->
<section class="reapyloan-faq">
  <div class="reapyloan-faq-container">

    <div class="reapyloan-faq-head">
      

      <h2 class="reapyloan-faq-title">
        Frequently Asked Questions
      </h2>

      <p class="reapyloan-faq-desc">
        Quick answers about loan repayment, EMI payments & security.
      </p>
    </div>

    <div class="reapyloan-faq-list">

      <div class="reapyloan-faq-item active">
        <button class="reapyloan-faq-question" type="button">
          <span>How can I repay my loan?</span>
          <i class="fa-solid fa-chevron-down"></i>
        </button>
        <div class="reapyloan-faq-answer">
          <p>Visit the repayment page, enter your PAN, verify with the OTP sent to your registered mobile, choose the amount and pay via UPI, debit/credit card, or net banking.</p>
        </div>
      </div>

      <div class="reapyloan-faq-item">
        <button class="reapyloan-faq-question" type="button">
          <span>What payment methods are available?</span>
          <i class="fa-solid fa-chevron-down"></i>
        </button>
        <div class="reapyloan-faq-answer">
          <p>We support UPI, all major debit and credit cards, and net banking from leading Indian banks. NEFT/RTGS is also available for high-value payments.</p>
        </div>
      </div>

      <div class="reapyloan-faq-item">
        <button class="reapyloan-faq-question" type="button">
          <span>Is loan repayment secure?</span>
          <i class="fa-solid fa-chevron-down"></i>
        </button>
        <div class="reapyloan-faq-answer">
          <p>Yes. All transactions are routed through authorised payment gateways and protected with strong TLS encryption. We never store your card or banking credentials.</p>
        </div>
      </div>

      <div class="reapyloan-faq-item">
        <button class="reapyloan-faq-question" type="button">
          <span>Can I make a partial payment?</span>
          <i class="fa-solid fa-chevron-down"></i>
        </button>
        <div class="reapyloan-faq-answer">
          <p>Absolutely. After verifying your identity you can choose to pay the full outstanding balance or any partial amount, subject to your specific loan terms.</p>
        </div>
      </div>

      <div class="reapyloan-faq-item">
        <button class="reapyloan-faq-question" type="button">
          <span>Will I get confirmation after payment?</span>
          <i class="fa-solid fa-chevron-down"></i>
        </button>
        <div class="reapyloan-faq-answer">
          <p>Yes — once your payment is successful you'll instantly receive an SMS and email confirmation along with a downloadable receipt.</p>
        </div>
      </div>

    </div>
  </div>
</section>

<div id="reapyloan-toast" class="reapyloan-toast"></div>

<script>
/* PAN + OTP */
const panForm = document.getElementById("reapyloan-pan-form");
const otpForm = document.getElementById("reapyloan-otp-form");
const panInput = document.getElementById("reapyloan-pan");
const panShow = document.getElementById("reapyloan-pan-show");
const changePanBtn = document.getElementById("reapyloan-change-pan");
const resendOtpBtn = document.getElementById("reapyloan-resend-otp");
const otpInputs = document.querySelectorAll(".reapyloan-otp-input");
const toastBox = document.getElementById("reapyloan-toast");

function showToast(message, type = "success"){
  toastBox.innerText = message;
  toastBox.className = "reapyloan-toast";

  if(type === "error"){
    toastBox.classList.add("reapyloan-error");
  }

  toastBox.style.display = "block";

  setTimeout(() => {
    toastBox.style.display = "none";
  }, 3000);
}

panInput.addEventListener("input", function(){
  this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, "");
});

panForm.addEventListener("submit", function(e){
  e.preventDefault();

  const pan = panInput.value.toUpperCase();
  const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]$/;

  if(!panRegex.test(pan)){
    showToast("Invalid PAN. Please enter a valid PAN number.", "error");
    return;
  }

  panShow.innerText = pan;
  panForm.classList.add("reapyloan-hidden");
  otpForm.classList.remove("reapyloan-hidden");
  otpInputs[0].focus();

  showToast("OTP sent to your registered mobile.");
});

otpInputs.forEach((input, index) => {
  input.addEventListener("input", function(){
    this.value = this.value.replace(/[^0-9]/g, "");

    if(this.value && index < otpInputs.length - 1){
      otpInputs[index + 1].focus();
    }
  });

  input.addEventListener("keydown", function(e){
    if(e.key === "Backspace" && !this.value && index > 0){
      otpInputs[index - 1].focus();
    }
  });
});

otpForm.addEventListener("submit", function(e){
  e.preventDefault();

  let otp = "";
  otpInputs.forEach(input => otp += input.value);

  if(otp.length !== 6){
    showToast("Invalid OTP. Please enter the 6-digit OTP.", "error");
    return;
  }

  showToast("OTP verified successfully. Redirecting to payment...");
});

changePanBtn.addEventListener("click", function(){
  otpForm.classList.add("reapyloan-hidden");
  panForm.classList.remove("reapyloan-hidden");
  otpInputs.forEach(input => input.value = "");
  panInput.focus();
});

resendOtpBtn.addEventListener("click", function(){
  showToast("A new OTP has been sent.");
});

/* FAQ ACCORDION */
const faqItems = document.querySelectorAll(".reapyloan-faq-item");

function setFaqHeight(){
  faqItems.forEach(item => {
    const answer = item.querySelector(".reapyloan-faq-answer");

    if(item.classList.contains("active")){
      answer.style.maxHeight = answer.scrollHeight + "px";
    } else {
      answer.style.maxHeight = "0px";
    }
  });
}

faqItems.forEach(item => {
  const question = item.querySelector(".reapyloan-faq-question");

  question.addEventListener("click", () => {
    faqItems.forEach(other => {
      if(other !== item){
        other.classList.remove("active");
      }
    });

    item.classList.toggle("active");
    setFaqHeight();
  });
});

window.addEventListener("load", setFaqHeight);
window.addEventListener("resize", setFaqHeight);
</script>


<script>
  const checkbox = document.getElementById("wfCheck");
  const popup = document.getElementById("wfPopup");

  // checkbox click -> open popup
  checkbox.addEventListener("change", function () {
    if (this.checked) {
      popup.style.display = "flex";
    } else {
      popup.style.display = "none";
    }
  });
</script>


<?php include 'includes/footer.php' ?>
