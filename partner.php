<?php
$page_title = "DSA Registration Online | Waqt Finance Partner Program";
$meta_description = "Register as a Direct Selling Agent with Waqt Finance. Earn commission on loan referrals, track leads live, get a dedicated relationship manager.";
$canonical_url = "https://waqtfinance.com/partner";
include 'includes/header.php';
include 'includes/main-header.php';
?>

<style>
/* Premium Partner Page Styles */
:root {
    --wf-primary: #18529c;
    --wf-primary-hover: #0c1a30;
    --wf-secondary: #f59e0b;
    --wf-bg: #f8fafc;
    --wf-surface: #ffffff;
    --wf-text-main: #0f2742;
    --wf-text-muted: #64748b;
    --wf-border: #e2e8f0;
    --wf-radius: 12px;
    --wf-radius-sm: 8px;
    --wf-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.04), 0 4px 6px -2px rgba(15, 23, 42, 0.02);
}

.partner-hero-wrapper,
.partner-hero-wrapper *:not(i) {
    font-family: 'Inter', sans-serif !important;
}

.partner-hero-wrapper i {
    font-family: 'Font Awesome 6 Free', 'Font Awesome 6 Brands', 'Font Awesome 5 Free', 'Font Awesome 5 Brands', 'Font Awesome' !important;
    font-weight: 900 !important;
}

.partner-hero-wrapper {
    background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%) !important;
    padding: 80px 0;
    min-height: calc(100vh - 100px);
    display: flex;
    align-items: center;
}

.partner-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 60px;
    align-items: center;
}

@media (max-width: 991px) {
    .partner-container {
        grid-template-columns: 1fr;
        gap: 50px;
        padding-top: 20px;
    }
    .partner-hero-wrapper {
        padding: 50px 0;
    }
}

/* Left Content */
.partner-content {
    animation: fadeUp 0.8s ease-out forwards;
}

.partner-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(24, 82, 156, 0.06) !important;
    color: #18529c !important;
    border: 1px solid rgba(24, 82, 156, 0.15) !important;
    padding: 8px 16px;
    border-radius: 30px;
    font-size: 13.5px;
    font-weight: 600;
    margin-bottom: 24px;
}

.partner-title {
    font-size: clamp(32px, 5vw, 48px);
    font-weight: 800;
    color: var(--wf-text-main);
    line-height: 1.15;
    letter-spacing: -1px;
    margin-bottom: 20px;
}

.partner-title span {
    color: #18529c !important;
}

.partner-desc {
    font-size: 16.5px;
    color: var(--wf-text-muted);
    line-height: 1.65;
    margin-bottom: 40px;
}

.partner-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.stat-item {
    background: var(--wf-surface);
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02);
    border: 1px solid var(--wf-border);
    transition: all 0.3s ease;
    text-align: center;
}

.stat-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(24, 82, 156, 0.06);
    border-color: #18529c;
}

.stat-value {
    font-size: 22px;
    font-weight: 800;
    color: var(--wf-primary);
    margin-bottom: 4px;
}

.stat-label {
    font-size: 13px;
    color: var(--wf-text-muted);
    font-weight: 600;
    margin: 0;
}

/* Right Content - Auth Card */
.partner-auth-card {
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.08);
    padding: 36px 30px;
    border: 1px solid var(--wf-border);
    animation: fadeLeft 0.8s ease-out forwards;
    position: relative;
    overflow: hidden;
}

.partner-auth-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #18529c, #dc2626) !important;
}

.auth-tabs {
    display: flex;
    background: #f1f5f9;
    border-radius: 30px;
    padding: 4px;
    margin-bottom: 28px;
}

.auth-tab {
    flex: 1;
    padding: 10px 0;
    text-align: center;
    font-weight: 700;
    font-size: 14.5px;
    color: var(--wf-text-muted);
    background: transparent;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.auth-tab.active {
    background: #18529c !important;
    color: #ffffff !important;
    box-shadow: 0 4px 10px rgba(24, 82, 156, 0.2) !important;
    border-bottom: none !important;
}

.auth-form {
    display: none;
    animation: fadeIn 0.4s ease;
}

.auth-form.active {
    display: block;
}

.form-group {
    margin-bottom: 20px;
    position: relative;
}

.form-label {
    display: block;
    font-size: 13.5px;
    font-weight: 700;
    color: var(--wf-text-main);
    margin-bottom: 8px;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}

.input-icon {
    position: absolute;
    left: 16px;
    color: #94a3b8;
    font-size: 16px;
    z-index: 2;
    transition: color 0.3s ease;
}

.form-input {
    width: 100%;
    padding: 13px 16px 13px 44px;
    border: 1px solid var(--wf-border);
    border-radius: 8px;
    font-size: 14.5px;
    color: var(--wf-text-main);
    transition: all 0.3s ease;
    outline: none;
    background: var(--wf-surface);
    box-sizing: border-box;
}

.form-input:focus {
    border-color: var(--wf-primary);
    box-shadow: 0 0 0 3px rgba(24, 82, 156, 0.15);
}

.form-input:focus + .input-icon {
    color: var(--wf-primary);
}

.toggle-password {
    position: absolute;
    right: 16px;
    color: #94a3b8;
    cursor: pointer;
    font-size: 16px;
    z-index: 2;
    transition: color 0.3s ease;
}

.toggle-password:hover {
    color: var(--wf-text-main);
}

.auth-submit {
    width: 100%;
    padding: 14px 18px;
    background: linear-gradient(135deg, #18529c 0%, #0b213a 100%) !important;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
    box-shadow: 0 4px 14px rgba(24, 82, 156, 0.18) !important;
}

.auth-submit:hover {
    background: linear-gradient(135deg, #0b213a 0%, #062b5d 100%) !important;
    transform: translateY(-1.5px) !important;
    box-shadow: 0 6px 20px rgba(24, 82, 156, 0.25) !important;
}

.auth-footer {
    text-align: center;
    margin-top: 24px;
    font-size: 14px;
    color: var(--wf-text-muted);
}

.auth-footer a {
    color: #dc2626 !important;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s ease;
}

.auth-footer a:hover {
    text-decoration: underline;
}

/* Sections Styling */
.dsa-section {
    padding: 80px 0;
    background: #ffffff;
}

.dsa-section.bg-light {
    background: #f8fafc;
}

.section-title {
    font-size: clamp(24px, 4vw, 36px);
    font-weight: 800;
    color: var(--wf-text-main);
    text-align: center;
    margin-bottom: 12px;
}

.section-subtitle {
    font-size: 16px;
    color: var(--wf-text-muted);
    text-align: center;
    max-width: 600px;
    margin: 0 auto 50px auto;
    line-height: 1.6;
}

/* Calculator Styles */
.calculator-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 24px;
}

.calc-box {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 40px;
    background: #f8fafc;
    border: 1px solid var(--wf-border);
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.04);
    align-items: center;
    margin-top: 40px;
}

@media (max-width: 768px) {
    .calc-box {
        grid-template-columns: 1fr;
        padding: 30px 20px;
        gap: 30px;
    }
}

.calc-sliders {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.slider-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.slider-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.slider-label {
    font-size: 15px;
    font-weight: 700;
    color: var(--wf-text-main);
}

.slider-value {
    font-size: 16px;
    font-weight: 800;
    color: var(--wf-primary);
    background: rgba(24, 82, 156, 0.08);
    padding: 4px 12px;
    border-radius: 20px;
}

.range-slider {
    -webkit-appearance: none;
    width: 100%;
    height: 6px;
    background: #cbd5e1;
    border-radius: 5px;
    outline: none;
}

.range-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--wf-primary);
    cursor: pointer;
    box-shadow: 0 0 0 4px rgba(24, 82, 156, 0.15);
    transition: all 0.2s;
}

.range-slider::-webkit-slider-thumb:hover {
    transform: scale(1.1);
    background: #0f2742;
}

.slider-minmax {
    display: flex;
    justify-content: space-between;
    font-size: 12.5px;
    color: var(--wf-text-muted);
    font-weight: 600;
}

.result-card {
    background: linear-gradient(135deg, #070d19 0%, #18529c 100%);
    border-radius: 16px;
    padding: 35px 30px;
    color: #ffffff;
    text-align: center;
    box-shadow: 0 15px 30px rgba(24, 82, 156, 0.15);
}

.result-eyebrow {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--wf-secondary);
    letter-spacing: 1px;
    margin-bottom: 8px;
}

.result-amount {
    font-size: 40px;
    font-weight: 800;
    color: #ffffff;
    margin-bottom: 12px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.result-text {
    font-size: 13.5px;
    color: #cbd5e1;
    line-height: 1.5;
    margin-bottom: 25px;
}

.calc-cta-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 14px;
    background: var(--wf-secondary);
    color: #0c1a30 !important;
    text-decoration: none !important;
    font-weight: 700;
    border-radius: 8px;
    transition: all 0.3s;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.18);
}

.calc-cta-btn:hover {
    background: #d97706;
    transform: translateY(-1.5px);
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.25);
}

/* Bento Layout */
.benefits-wrapper {
    max-width: 1200px;
    margin: 45px auto 0 auto;
    padding: 0 24px;
}

.benefits-bento-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
}

@media (max-width: 991px) {
    .benefits-bento-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .benefits-bento-grid {
        grid-template-columns: 1fr;
    }
}

.bento-card {
    background: var(--wf-surface);
    border: 1px solid var(--wf-border);
    border-radius: 16px;
    padding: 30px 24px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02);
}

.bento-card:hover {
    transform: translateY(-4px);
    border-color: var(--wf-primary);
    box-shadow: 0 12px 24px rgba(24, 82, 156, 0.08);
}

.bento-bg-icon {
    position: absolute;
    right: -15px;
    bottom: -15px;
    font-size: 80px;
    color: rgba(24, 82, 156, 0.03);
    pointer-events: none;
    transform: rotate(-15deg);
}

.bento-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(24, 82, 156, 0.06);
    color: var(--wf-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin-bottom: 20px;
}

.bento-title {
    font-size: 18px;
    font-weight: 750;
    color: var(--wf-text-main);
    margin-bottom: 10px;
}

.bento-desc {
    font-size: 14px;
    color: var(--wf-text-muted);
    line-height: 1.6;
    margin: 0;
}

/* How It Works Steps */
.steps-container {
    max-width: 1200px;
    margin: 45px auto 0 auto;
    padding: 0 24px;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    position: relative;
}

@media (max-width: 991px) {
    .steps-container {
        grid-template-columns: 1fr;
        gap: 40px;
    }
}

.step-item {
    background: var(--wf-surface);
    border: 1px solid var(--wf-border);
    border-radius: 16px;
    padding: 35px 30px;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02);
    transition: all 0.3s ease;
    text-align: center;
}

.step-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(24, 82, 156, 0.06);
    border-color: var(--wf-primary);
}

.step-number {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--wf-primary);
    color: #ffffff;
    font-size: 20px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px auto;
    box-shadow: 0 4px 10px rgba(24, 82, 156, 0.2);
}

.step-content h3 {
    font-size: 18px;
    font-weight: 750;
    color: var(--wf-text-main);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.step-content p {
    font-size: 14px;
    color: var(--wf-text-muted);
    line-height: 1.6;
    margin: 0;
}

/* Eligibility Profile Cards */
.eligibility-grid {
    max-width: 1200px;
    margin: 45px auto 0 auto;
    padding: 0 24px;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

@media (max-width: 991px) {
    .eligibility-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .eligibility-grid {
        grid-template-columns: 1fr;
    }
}

.eligibility-card {
    background: var(--wf-surface);
    border: 1px solid var(--wf-border);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02);
    transition: all 0.3s ease;
}

.eligibility-card:hover {
    transform: translateY(-4px);
    border-color: var(--wf-primary);
    box-shadow: 0 12px 24px rgba(24, 82, 156, 0.06);
}

.eligibility-icon {
    font-size: 24px;
    color: var(--wf-primary);
    margin-bottom: 20px;
}

.eligibility-title {
    font-size: 18px;
    font-weight: 750;
    color: var(--wf-text-main);
    margin-bottom: 10px;
}

.eligibility-desc {
    font-size: 14px;
    color: var(--wf-text-muted);
    line-height: 1.6;
    margin: 0;
}

/* Partner FAQ Section */
.partner-faq-wrapper {
    max-width: 800px;
    margin: 45px auto 0 auto;
    padding: 0 24px;
}

.partner-faq-item {
    background: var(--wf-surface);
    border: 1px solid var(--wf-border);
    border-radius: 12px;
    margin-bottom: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02);
}

.partner-faq-item.active {
    border-color: var(--wf-primary);
    box-shadow: 0 10px 20px rgba(24, 82, 156, 0.06);
}

.partner-faq-question {
    padding: 20px 24px;
    font-size: 16px;
    font-weight: 700;
    color: var(--wf-text-main);
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    user-select: none;
}

.partner-faq-question i {
    color: var(--wf-primary);
    transition: transform 0.3s ease;
}

.partner-faq-item.active .partner-faq-question i {
    transform: rotate(180deg);
}

.partner-faq-answer {
    padding: 0 24px;
    max-height: 0;
    opacity: 0;
    color: var(--wf-text-muted);
    line-height: 1.65;
    overflow: hidden;
    transition: max-height 0.3s ease-out, opacity 0.3s ease;
    font-size: 14.5px;
}

.partner-faq-item.active .partner-faq-answer {
    padding: 0 24px 20px 24px;
    opacity: 1;
}

/* CTA Wrapper Section */
.dsa-cta-wrapper {
    padding: 80px 24px;
    background: #ffffff;
}

.dsa-cta-card {
    max-width: 1100px;
    margin: 0 auto;
    background: linear-gradient(135deg, #070d19 0%, #18529c 100%);
    border-radius: 20px;
    padding: 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 40px;
    color: #ffffff;
    box-shadow: 0 20px 40px rgba(24, 82, 156, 0.15);
}

.cta-content {
    flex: 1.2;
}

.cta-content h2 {
    font-size: clamp(24px, 4vw, 36px);
    font-weight: 800;
    margin-bottom: 16px;
    color: #ffffff;
    line-height: 1.2;
}

.cta-content p {
    color: #cbd5e1;
    font-size: 16px;
    line-height: 1.6;
    margin: 0;
}

.cta-action {
    flex: 0.8;
    display: flex;
    flex-direction: column;
    gap: 16px;
    align-items: center;
}

.cta-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 16px 32px;
    background: var(--wf-secondary);
    color: #0c1a30 !important;
    text-decoration: none !important;
    font-weight: 700;
    border-radius: 8px;
    transition: all 0.3s;
    font-size: 16px;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.18);
}

.cta-btn:hover {
    background: #d97706;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.25);
}

.cta-guarantee {
    color: #cbd5e1;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
}

@media (max-width: 991px) {
    .dsa-cta-card {
        flex-direction: column;
        text-align: center;
        padding: 40px 30px;
        gap: 30px;
    }
    .cta-action {
        width: 100%;
    }
}

/* Animations */
@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeLeft {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@media (max-width: 600px) {
    .partner-title {
        font-size: 28px;
    }
    .partner-badge {
        font-size: 12.5px;
        padding: 6px 12px;
    }
    .stat-item {
        padding: 12px 10px;
    }
    .stat-value {
        font-size: 18px;
    }
    .stat-label {
        font-size: 11px;
    }
    .auth-submit {
        padding: 12px;
        font-size: 14.5px;
    }
    .form-input {
        padding: 11px 16px 11px 40px;
        font-size: 13.5px;
    }
    .input-icon {
        left: 14px;
        font-size: 14px;
    }
    .section-title {
        font-size: 22px;
    }
    .section-subtitle {
        font-size: 14.5px;
        margin-bottom: 30px;
    }
}
</style>

<div class="partner-hero-wrapper">
    <div class="partner-container">
        
        <!-- Left Side: Value Proposition -->
        <div class="partner-content">
            <div class="partner-badge">
                <i class="fa fa-users"></i> Trusted by 5,000+ Partners
            </div>
            
            <h1 class="partner-title">
                Earn <span>₹4,00,000+</span> monthly as a DSA
            </h1>
            
            <p class="partner-desc">
                Join our tech-driven platform to grow your business, offer seamless loan disbursals, and build lasting financial relationships with your clients.
            </p>
            
            <div class="partner-stats">
                <div class="stat-item">
                    <div class="stat-value">15 Min</div>
                    <div class="stat-label">Fast Approval</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">99%</div>
                    <div class="stat-label">Retention Rate</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">Zero</div>
                    <div class="stat-label">Onboarding Fee</div>
                </div>
            </div>
        </div>

        <!-- Right Side: Auth Form -->
        <div class="partner-auth-card">
            <div class="auth-tabs">
                <button type="button" class="auth-tab active" data-target="login">Login</button>
                <button type="button" class="auth-tab" data-target="register">Register</button>
            </div>

            <!-- Login Form -->
            <form id="loginForm" class="auth-form active" novalidate>
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label class="form-label">Email or Mobile Number</label>
                    <div class="input-wrapper">
                        <i class="fa fa-envelope input-icon"></i>
                        <input type="text" name="email_or_mobile" class="form-input" placeholder="Enter your email or mobile" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <i class="fa fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-input" id="loginPassword" placeholder="Enter your password" required>
                        <i class="fa fa-eye toggle-password" onclick="togglePasswordVisibility('loginPassword')"></i>
                    </div>
                </div>
                
                <div style="text-align: right; margin-top: -10px; margin-bottom: 20px;">
                    <a href="#" style="font-size: 13px; color: var(--wf-primary); font-weight: 500; text-decoration: none;">Forgot Password?</a>
                </div>

                <button type="submit" class="auth-submit" id="loginSubmitBtn">
                    Secure Login <i class="fa fa-arrow-right"></i>
                </button>
                
                <div class="auth-footer">
                    Don't have an account? <a href="javascript:void(0)" onclick="switchTab('register')">Register Now</a>
                </div>
            </form>

            <!-- Register Form -->
            <form id="registerForm" class="auth-form" novalidate>
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <div class="input-wrapper">
                        <i class="fa fa-user input-icon"></i>
                        <input type="text" name="full_name" class="form-input" placeholder="Enter your full name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email or Mobile Number</label>
                    <div class="input-wrapper">
                        <i class="fa fa-envelope input-icon"></i>
                        <input type="text" name="email_or_mobile" class="form-input" placeholder="Enter email or mobile" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Create Password</label>
                    <div class="input-wrapper">
                        <i class="fa fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-input" id="registerPassword" placeholder="Create a secure password" required>
                        <i class="fa fa-eye toggle-password" onclick="togglePasswordVisibility('registerPassword')"></i>
                    </div>
                </div>

                <button type="submit" class="auth-submit" id="registerSubmitBtn">
                    Create Partner Account <i class="fa fa-arrow-right"></i>
                </button>
                
                <div class="auth-footer">
                    Already a partner? <a href="javascript:void(0)" onclick="switchTab('login')">Login Here</a>
                </div>
            </form>
        </div>
        
    </div>
</div>

<!-- Interactive Earnings Commission Calculator -->
<section class="dsa-section calculator-section">
    <div class="calculator-container">
        <div class="calculator-header text-center">
            <h2 class="section-title">Estimate Your Partner Earnings</h2>
            <p class="section-subtitle">Slide the controls to calculate your monthly income based on your projected loan disbursals.</p>
        </div>
        
        <div class="calc-box">
            <div class="calc-sliders">
                <!-- Slider 1 -->
                <div class="slider-group">
                    <div class="slider-info">
                        <label for="disbursalSlider" class="slider-label">Expected Monthly Disbursals</label>
                        <span class="slider-value" id="disbursalValue">₹25,00,000</span>
                    </div>
                    <input type="range" id="disbursalSlider" class="range-slider" min="500000" max="10000000" step="500000" value="2500000">
                    <div class="slider-minmax">
                        <span>₹5 Lakhs</span>
                        <span>₹1 Crore</span>
                    </div>
                </div>
                
                <!-- Slider 2 -->
                <div class="slider-group">
                    <div class="slider-info">
                        <label for="commissionSlider" class="slider-label">DSA Commission Rate</label>
                        <span class="slider-value" id="commissionValue">1.8%</span>
                    </div>
                    <input type="range" id="commissionSlider" class="range-slider" min="1.0" max="2.5" step="0.1" value="1.8">
                    <div class="slider-minmax">
                        <span>1.0%</span>
                        <span>2.5%</span>
                    </div>
                </div>
            </div>
            
            <div class="calc-result">
                <div class="result-card">
                    <div class="result-eyebrow">Estimated Monthly Payout</div>
                    <div class="result-amount" id="payoutAmount">₹45,000</div>
                    <p class="result-text">Based on standard disbursal rates. High performers can unlock even higher commission slabs!</p>
                    <a href="#" class="calc-cta-btn" onclick="window.scrollTo({top:0, behavior:'smooth'}); switchTab('register'); return false;">Start Earning Now <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Partner With Us (Bento Layout) -->
<section class="dsa-section bg-light">
    <h2 class="section-title">Why Partner With Waqt Finance?</h2>
    <p class="section-subtitle">Experience industry-leading benefits designed to help you maximize your earnings.</p>
    
    <div class="benefits-wrapper">
        <div class="benefits-bento-grid">
            <div class="bento-card">
                <i class="fa fa-money-bill-wave bento-bg-icon"></i>
                <div class="bento-content">
                    <div class="bento-icon-wrapper"><i class="fa fa-money-bill-wave"></i></div>
                    <h3 class="bento-title">High Commissions</h3>
                    <p class="bento-desc">Earn some of the highest payout percentages in the industry with no earning caps. Your hard work directly translates to unmatched financial rewards.</p>
                </div>
            </div>
            <div class="bento-card">
                <i class="fa fa-bolt bento-bg-icon"></i>
                <div class="bento-content">
                    <div class="bento-icon-wrapper"><i class="fa fa-bolt"></i></div>
                    <h3 class="bento-title">Fast Disbursals</h3>
                    <p class="bento-desc">Tech-driven process ensures quick approvals and lightning-fast disbursals.</p>
                </div>
            </div>
            <div class="bento-card">
                <i class="fa fa-chart-pie bento-bg-icon"></i>
                <div class="bento-content">
                    <div class="bento-icon-wrapper"><i class="fa fa-chart-pie"></i></div>
                    <h3 class="bento-title">Live Tracking</h3>
                    <p class="bento-desc">Monitor your leads and conversion statuses in real-time through the dashboard.</p>
                </div>
            </div>
            <div class="bento-card">
                <i class="fa fa-headset bento-bg-icon"></i>
                <div class="bento-content">
                    <div class="bento-icon-wrapper"><i class="fa fa-headset"></i></div>
                    <h3 class="bento-title">Dedicated Support</h3>
                    <p class="bento-desc">Get an exclusive Relationship Manager to assist you with complex cases, expedite queries, and guide you every step of the way to success.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="dsa-section">
    <h2 class="section-title">How It Works</h2>
    <p class="section-subtitle">Start earning in three simple steps.</p>

    <div class="steps-container">
        <div class="step-item">
            <div class="step-number">1</div>
            <div class="step-content">
                <h3><i class="fa fa-user-plus" style="color: var(--wf-primary);"></i> Register</h3>
                <p>Fill out the quick registration form above, submit your basic KYC documents, and get your partner account activated within hours.</p>
            </div>
        </div>
        <div class="step-item">
            <div class="step-number">2</div>
            <div class="step-content">
                <h3><i class="fa fa-share-nodes" style="color: var(--wf-primary);"></i> Refer Clients</h3>
                <p>Use your partner dashboard to log in leads or share your unique referral link with clients needing personal or business loans.</p>
            </div>
        </div>
        <div class="step-item">
            <div class="step-number">3</div>
            <div class="step-content">
                <h3><i class="fa fa-wallet" style="color: var(--wf-primary);"></i> Earn Payouts</h3>
                <p>Once the loan is approved and disbursed, your commission is automatically calculated and credited to your registered bank account.</p>
            </div>
        </div>
    </div>
</section>

<!-- Eligibility -->
<section class="dsa-section bg-light">
    <h2 class="section-title">Who Can Become a Partner?</h2>
    <p class="section-subtitle">Whether you are an individual or a business, you can join our network and scale your income.</p>

    <div class="eligibility-grid">
        <div class="eligibility-card">
            <div class="eligibility-icon"><i class="fa fa-chart-line"></i></div>
            <h3 class="eligibility-title">Financial Advisors</h3>
            <p class="eligibility-desc">Add loan products to your portfolio and offer comprehensive financial solutions.</p>
        </div>
        <div class="eligibility-card">
            <div class="eligibility-icon"><i class="fa fa-building"></i></div>
            <h3 class="eligibility-title">Real Estate Agents</h3>
            <p class="eligibility-desc">Help your clients secure home loans quickly and close property deals faster.</p>
        </div>
        <div class="eligibility-card">
            <div class="eligibility-icon"><i class="fa fa-calculator"></i></div>
            <h3 class="eligibility-title">Chartered Accountants</h3>
            <p class="eligibility-desc">Assist your business clients with working capital and expansion loans.</p>
        </div>
        <div class="eligibility-card">
            <div class="eligibility-icon"><i class="fa fa-chart-pie"></i></div>
            <h3 class="eligibility-title">Mutual Fund Distributors</h3>
            <p class="eligibility-desc">Expand your earning potential by catering to clients' credit needs.</p>
        </div>
        <div class="eligibility-card">
            <div class="eligibility-icon"><i class="fa fa-shield-alt"></i></div>
            <h3 class="eligibility-title">Insurance Agents</h3>
            <p class="eligibility-desc">Leverage your existing client base to offer them reliable financial assistance.</p>
        </div>
        <div class="eligibility-card">
            <div class="eligibility-icon"><i class="fa fa-laptop-house"></i></div>
            <h3 class="eligibility-title">Freelancers & Consultants</h3>
            <p class="eligibility-desc">Start a zero-investment side business and create a steady passive income.</p>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="dsa-section">
    <h2 class="section-title">Frequently Asked Questions</h2>
    <p class="section-subtitle">Common questions about the Waqt Finance Partner Program.</p>

    <div class="partner-faq-wrapper">
        <div class="partner-faq-item">
            <div class="partner-faq-question">
                Is there any registration fee to become a DSA?
                <i class="fa fa-chevron-down"></i>
            </div>
            <div class="partner-faq-answer" style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out, opacity 0.3s ease;">
                No, joining the Waqt Finance Partner Program is completely free. There are no onboarding or hidden fees involved.
            </div>
        </div>
        <div class="partner-faq-item">
            <div class="partner-faq-question">
                When do I get my commission payouts?
                <i class="fa fa-chevron-down"></i>
            </div>
            <div class="partner-faq-answer" style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out, opacity 0.3s ease;">
                Commissions are calculated automatically upon successful loan disbursal and credited to your registered bank account on a regular schedule (weekly/monthly).
            </div>
        </div>
        <div class="partner-faq-item">
            <div class="partner-faq-question">
                Who can I refer for a loan?
                <i class="fa fa-chevron-down"></i>
            </div>
            <div class="partner-faq-answer" style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out, opacity 0.3s ease;">
                You can refer any individual or business looking for personal loans, business loans, or lines of credit, provided they meet our basic eligibility criteria.
            </div>
        </div>
        <div class="partner-faq-item">
            <div class="partner-faq-question">
                How do I track my referrals?
                <i class="fa fa-chevron-down"></i>
            </div>
            <div class="partner-faq-answer" style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out, opacity 0.3s ease;">
                Once registered, you will get access to a dedicated partner dashboard where you can log leads, check their real-time approval status, and track your total earnings.
            </div>
        </div>
    </div>
</section>

<!-- Premium CTA Section -->
<section class="dsa-cta-wrapper">
    <div class="dsa-cta-card">
        <div class="cta-content">
            <h2>Ready to skyrocket your income?</h2>
            <p>Join over 5,000+ ambitious partners who are already building a sustainable, high-earning business with Waqt Finance.</p>
        </div>
        <div class="cta-action">
            <a href="#" class="cta-btn" onclick="window.scrollTo({top:0, behavior:'smooth'}); switchTab('register'); return false;">
                Start Earning Today <i class="fa fa-arrow-up"></i>
            </a>
            <div class="cta-guarantee"><i class="fa fa-shield-alt"></i> 100% Free to Join. No Hidden Fees.</div>
        </div>
    </div>
</section>

<script>
    // Earnings Calculator Logic
    const disbursalSlider = document.getElementById('disbursalSlider');
    const commissionSlider = document.getElementById('commissionSlider');
    const disbursalValue = document.getElementById('disbursalValue');
    const commissionValue = document.getElementById('commissionValue');
    const payoutAmount = document.getElementById('payoutAmount');

    function formatRupee(amount) {
        return '₹' + amount.toLocaleString('en-IN');
    }

    function calculatePayout() {
        const disbursal = parseInt(disbursalSlider.value);
        const commissionRate = parseFloat(commissionSlider.value);
        
        disbursalValue.textContent = formatRupee(disbursal);
        commissionValue.textContent = commissionRate + '%';
        
        const payout = Math.round(disbursal * (commissionRate / 100));
        payoutAmount.textContent = formatRupee(payout);
    }

    if (disbursalSlider && commissionSlider) {
        disbursalSlider.addEventListener('input', calculatePayout);
        commissionSlider.addEventListener('input', calculatePayout);
        calculatePayout(); // Initialize
    }

    // FAQ Toggle Logic (Smooth Slide Transition)
    const faqItems = document.querySelectorAll('.partner-faq-item');
    faqItems.forEach(item => {
        const question = item.querySelector('.partner-faq-question');
        const answer = item.querySelector('.partner-faq-answer');
        
        question.addEventListener('click', () => {
            const isActive = item.classList.contains('active');
            
            // Close all
            faqItems.forEach(faq => {
                faq.classList.remove('active');
                faq.querySelector('.partner-faq-answer').style.maxHeight = '0px';
            });
            
            // Open clicked if it wasn't already open
            if (!isActive) {
                item.classList.add('active');
                answer.style.maxHeight = answer.scrollHeight + "px";
            }
        });
    });

    // Tab Switching Logic
    const tabs = document.querySelectorAll('.auth-tab');
    const forms = document.querySelectorAll('.auth-form');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-target');
            switchTab(target);
        });
    });

    function switchTab(target) {
        // Update Tabs
        tabs.forEach(t => t.classList.remove('active'));
        const activeTab = document.querySelector(`.auth-tab[data-target="${target}"]`);
        if (activeTab) activeTab.classList.add('active');

        // Update Forms
        forms.forEach(f => f.classList.remove('active'));
        const activeForm = document.getElementById(`${target}Form`);
        if (activeForm) activeForm.classList.add('active');
    }

    // Password Visibility Toggle
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const icon = input.nextElementSibling;
        
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    }

    // AJAX Form Submissions
    const submitForm = async (formId, btnId, successMsg) => {
        const form = document.getElementById(formId);
        const btn = document.getElementById(btnId);
        if (!form || !btn) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            const action = formData.get('action');

            if (action === 'register') {
                const fullName = formData.get('full_name').trim();
                const emailOrMobile = formData.get('email_or_mobile').trim();
                const password = formData.get('password');

                if (fullName.length < 3) {
                    Swal.fire({ icon: 'error', title: 'Invalid Name', text: 'Full Name must be at least 3 characters.' });
                    return;
                }
                const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailOrMobile);
                const isMobile = /^[6-9]\d{9}$/.test(emailOrMobile);
                if (!isEmail && !isMobile) {
                    Swal.fire({ icon: 'error', title: 'Invalid Input', text: 'Enter a valid email address or 10-digit mobile number.' });
                    return;
                }
                if (password.length < 6) {
                    Swal.fire({ icon: 'error', title: 'Weak Password', text: 'Password must be at least 6 characters.' });
                    return;
                }
            } else {
                const emailOrMobile = formData.get('email_or_mobile').trim();
                const password = formData.get('password');
                if (!emailOrMobile || !password) {
                    Swal.fire({ icon: 'error', title: 'Required Fields', text: 'All fields are required.' });
                    return;
                }
            }

            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Processing... <i class="fas fa-spinner fa-spin ms-2"></i>';

            try {
                const response = await fetch('form-actions/submit-partner.php', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': window.WAQT_CSRF_TOKEN || ''
                    },
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || successMsg,
                        confirmButtonColor: '#18529c'
                    }).then(() => {
                        form.reset();
                        if (action === 'register') {
                            switchTab('login');
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: data.message || 'Operation failed.'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Unable to reach backend. Please try again later.'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        submitForm('loginForm', 'loginSubmitBtn', 'Logged in successfully!');
        submitForm('registerForm', 'registerSubmitBtn', 'Registration completed successfully!');
    });
</script>

<?php include 'includes/footer.php';?>