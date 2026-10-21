<?php
$page_title = "Pay Loan EMI Online | Instant UPI QR | Waqt Finance";
$meta_description = "Pay your Waqt Finance loan EMI instantly via UPI. Scan the official QR code using Google Pay, PhonePe, Paytm or BHIM for secure, instant payment receipt.";
$meta_keywords = "scan and pay, UPI loan payment, waqt finance QR code, pay EMI online";
$canonical_url = "https://waqtfinance.com/pay-online";

// Load the modern design system stylesheet
$extra_head = '<link rel="stylesheet" href="assets/css/apply-modern.css">';

include 'includes/header.php';
include 'includes/main-header.php';
?>

<div class="wf-pay-online-body" style="background: #f8fafc; padding: 40px 0 80px 0; font-family: 'Inter', sans-serif !important; min-height: 85vh; display: flex; align-items: center; justify-content: center; box-sizing: border-box;">
    
    <div class="wf-pay-online-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.05); max-width: 580px; width: 100%; margin: 0 auto; overflow: hidden; box-sizing: border-box;" data-aos="fade-up">
        
        <!-- Header Banner Block Inside Card -->
        <div style="background: #062b5d; color: #ffffff; padding: 28px 24px; text-align: center; position: relative;">
            <div style="position: absolute; right: 20px; top: 20px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-radius: 20px; padding: 4px 12px; font-size: 11px; display: flex; align-items: center; gap: 6px; font-family: 'Inter', sans-serif !important;">
                <i class="fa fa-lock" style="color: #10b981;"></i> Secure SSL
            </div>
            <i class="fa fa-shield-halved" style="font-size: 40px; margin-bottom: 12px; color: #ffffff; opacity: 0.95;"></i>
            <h1 style="font-size: 22px; font-weight: 800; color: #ffffff; margin: 0 0 6px 0; border: none; padding: 0; font-family: 'Inter', sans-serif !important; letter-spacing: -0.2px;">Secure Payment Gateway</h1>
            <h2 style="font-size: 13.5px; color: rgba(255, 255, 255, 0.85); margin: 0; font-family: 'Inter', sans-serif !important;">Scan the QR code to securely pay your EMI installments</h2>
        </div>

        <div style="padding: 32px 36px 36px; box-sizing: border-box;">
            
            <p style="font-size: 14.5px; color: #475569; font-family: 'Inter', sans-serif !important; line-height: 1.5; text-align: center; margin-top: 0; margin-bottom: 24px;">
                Use Google Pay, PhonePe, Paytm, BHIM, or any UPI App to scan the QR code below.
            </p>

            <!-- Styled QR Frame Box -->
            <div style="border: 1px solid #cbd5e1; background: #f8fafc; border-radius: 12px; padding: 16px; margin: 0 auto 28px; max-width: 440px; box-shadow: 0 8px 16px rgba(0, 0, 0, 0.03); box-sizing: border-box;">
                <img src="assets/images/barcodee-img.webp" alt="Waqt Finance QR Code Scan and Pay" style="width: 100%; height: auto; display: block; border-radius: 8px; border: 1px solid #e2e8f0;">
            </div>

            <!-- Steps Guideline Box -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; margin-bottom: 28px; box-sizing: border-box;">
                <h5 style="font-size: 13px; font-weight: 800; color: #0b213a; margin: 0 0 12px 0; text-transform: uppercase; letter-spacing: 0.5px; font-family: 'Inter', sans-serif !important;">Repayment Instructions</h5>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px;">
                    <li style="font-size: 13px; color: #475569; display: flex; align-items: center; gap: 10px; font-family: 'Inter', sans-serif !important; line-height: 1.4;">
                        <span style="background: #e2e8f0; color: #0b213a; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0;">1</span>
                        <span>Scan the QR code with any UPI application.</span>
                    </li>
                    <li style="font-size: 13px; color: #475569; display: flex; align-items: center; gap: 10px; font-family: 'Inter', sans-serif !important; line-height: 1.4;">
                        <span style="background: #e2e8f0; color: #0b213a; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0;">2</span>
                        <span>Enter your EMI installment amount and make payment.</span>
                    </li>
                    <li style="font-size: 13px; color: #475569; display: flex; align-items: center; gap: 10px; font-family: 'Inter', sans-serif !important; line-height: 1.4;">
                        <span style="background: #e2e8f0; color: #0b213a; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0;">3</span>
                        <span>Take a screenshot of the payment receipt.</span>
                    </li>
                    <li style="font-size: 13px; color: #475569; display: flex; align-items: center; gap: 10px; font-family: 'Inter', sans-serif !important; line-height: 1.4;">
                        <span style="background: #e2e8f0; color: #0b213a; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0;">4</span>
                        <span>Click the button below to share the screenshot on WhatsApp.</span>
                    </li>
                </ul>
            </div>

            <!-- Call To Action Button -->
            <div style="text-align: center;">
                <a href="https://wa.me/919217086610" class="wf-btn" style="background: #25D366 !important; border: none !important; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25) !important; color: #ffffff !important; display: inline-flex; align-items: center; justify-content: center; width: 100%; gap: 10px; font-weight: 700; height: 50px; border-radius: 8px; font-family: 'Inter', sans-serif !important; text-decoration: none; transition: all 0.3s ease; box-sizing: border-box;">
                    <i class="fab fa-whatsapp" style="font-size: 20px;"></i> Open WhatsApp for Receipt Confirmation
                </a>
                
                <p style="font-size: 13.5px; color: #64748b; font-family: 'Inter', sans-serif !important; margin-top: 16px; margin-bottom: 0; display: flex; align-items: center; justify-content: center; gap: 6px; font-weight: 500;">
                    <i class="fa fa-shield-halved" style="color: #10b981; font-size: 15px;"></i> 100% Secure, Verified merchant account
                </p>
            </div>
        </div>

        <!-- Soft-Blue Redirect status Banner -->
        <div style="background: #f0f7ff; padding: 18px; font-size: 13.5px; color: #1e3a8a; text-align: center; border-top: 1px solid #dbeafe; font-family: 'Inter', sans-serif !important; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600;">
            <i class="fa fa-circle-notch fa-spin" style="color: #18529c;"></i> Redirecting to home page in <span id="timer" style="font-weight: 700; color: #18529c;">60</span> seconds...
        </div>
    </div>
</div>

<script>
let count = 60;
const timerEl = document.getElementById('timer');
const interval = setInterval(function() {
    count--;
    if (timerEl) {
        timerEl.textContent = count;
    }
    if (count <= 0) {
        clearInterval(interval);
        window.location.href = "index";
    }
}, 1000);
</script>

<?php include 'includes/footer.php' ?>
