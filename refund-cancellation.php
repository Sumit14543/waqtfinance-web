<?php
$page_title = "Refund and Cancellation Policy | Waqt Finance";
$meta_description = "Cancel your Waqt Finance loan application before disbursement, or exit a disbursed loan during the RBI cooling-off period without penalty. See steps, charges and refund timelines.";
$meta_keywords = "refund policy, loan cancellation, cooling-off period, look-up period, waqt finance refund";
$canonical_url = "https://waqtfinance.com/refund-cancellation";

include 'includes/header.php';
include 'includes/main-header.php';
?>

<style>
.rc-page {
    --rc-navy: #062b5d;
    --rc-blue: #18529c;
    --rc-blue-soft: #eff6ff;
    --rc-ink: #0f172a;
    --rc-text: #475569;
    --rc-line: #e2e8f0;
    --rc-bg: #f8fafc;
    --rc-green: #15803d;
    --rc-green-soft: #f0fdf4;
    --rc-amber: #92400e;
    --rc-amber-soft: #fffbeb;
    --rc-amber-line: #fcd34d;
}

/* Hero */
.rc-hero {
    background: radial-gradient(circle at 50% 50%, #062b5d 0%, #031530 100%);
    padding: 72px 0 104px;
    color: #fff;
    text-align: center;
}
.rc-hero-inner { max-width: 760px; margin: 0 auto; }
.rc-hero-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
    padding: 6px 16px; border-radius: 999px;
    font-size: 13px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase;
    margin-bottom: 20px;
}
.rc-hero h1 {
    color: #fff; font-size: clamp(30px, 4.5vw, 46px); font-weight: 800;
    line-height: 1.2; margin: 0 0 16px;
}
.rc-hero-lead { color: rgba(255,255,255,.9); font-size: 17px; line-height: 1.7; margin: 0 0 12px; }
.rc-hero-meta { color: rgba(255,255,255,.65); font-size: 13.5px; margin: 0 0 28px; }
.rc-hero-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.rc-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    min-height: 46px; padding: 0 22px; border-radius: 10px;
    font-size: 15px; font-weight: 700; text-decoration: none !important;
    transition: background-color .2s ease, border-color .2s ease, color .2s ease;
}
.rc-btn:focus-visible { outline: 3px solid #93c5fd; outline-offset: 2px; }
.rc-btn-light { background: #fff; color: var(--rc-navy) !important; }
.rc-btn-light:hover { background: #e0ecff; }
.rc-btn-ghost { border: 1px solid rgba(255,255,255,.45); color: #fff !important; }
.rc-btn-ghost:hover { background: rgba(255,255,255,.1); border-color: #fff; }
.rc-btn-primary { background: var(--rc-blue); color: #fff !important; }
.rc-btn-primary:hover { background: var(--rc-navy); }
.rc-btn-outline { border: 1px solid var(--rc-line); background: #fff; color: var(--rc-ink) !important; }
.rc-btn-outline:hover { border-color: var(--rc-blue); color: var(--rc-blue) !important; }

/* Key facts */
.rc-facts { margin-top: -56px; position: relative; z-index: 5; margin-bottom: 40px; }
.rc-facts-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.rc-fact {
    background: #fff; border: 1px solid var(--rc-line); border-radius: 16px;
    padding: 22px 24px; display: flex; gap: 16px; align-items: flex-start;
    box-shadow: 0 10px 30px rgba(15,23,42,.06);
}
.rc-fact-icon {
    width: 46px; height: 46px; flex-shrink: 0; border-radius: 12px;
    background: var(--rc-blue-soft); color: var(--rc-blue);
    display: flex; align-items: center; justify-content: center; font-size: 19px;
}
.rc-fact-label { display: block; font-size: 12px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; color: var(--rc-text); margin-bottom: 4px; }
.rc-fact-value { display: block; font-size: 18px; font-weight: 800; color: var(--rc-ink); line-height: 1.3; }
.rc-fact-note { display: block; font-size: 13.5px; color: var(--rc-text); margin-top: 4px; line-height: 1.5; }

/* Policy tabs (shared look, fixed so every tab stays reachable) */
.wf-policy-nav-wrap {
    background: #fff; border-bottom: 1px solid var(--rc-line);
    padding: 12px 0; position: sticky; top: 90px; z-index: 100;
}
.wf-policy-nav-tabs {
    display: flex; gap: 8px; overflow-x: auto; flex-wrap: nowrap;
    -webkit-overflow-scrolling: touch; scrollbar-width: none; padding: 4px 2px;
}
.wf-policy-nav-tabs::-webkit-scrollbar { display: none; }
.wf-policy-nav-tabs > :first-child { margin-left: auto; }
.wf-policy-nav-tabs > :last-child { margin-right: auto; }
.wf-policy-tab-btn {
    flex-shrink: 0; background: #f8fafc; border: 1px solid var(--rc-line);
    color: #475569 !important; padding: 8px 16px; border-radius: 999px;
    font-size: 13px; font-weight: 700; text-decoration: none !important; white-space: nowrap;
    transition: background-color .2s ease, border-color .2s ease, color .2s ease;
}
.wf-policy-tab-btn:hover { background: #f1f5f9; color: var(--rc-blue) !important; border-color: var(--rc-blue); }
.wf-policy-tab-btn.active {
    background: linear-gradient(135deg, #062b5d 0%, #18529c 100%);
    color: #fff !important; border-color: #062b5d;
}
.wf-policy-tab-btn:focus-visible { outline: 3px solid #93c5fd; outline-offset: -3px; }
/* Fade the edge that has more tabs, so it is clear the row scrolls */
.wf-policy-nav-tabs.has-more-right { -webkit-mask-image: linear-gradient(to right, #000 88%, transparent); mask-image: linear-gradient(to right, #000 88%, transparent); }
.wf-policy-nav-tabs.has-more-left { -webkit-mask-image: linear-gradient(to left, #000 88%, transparent); mask-image: linear-gradient(to left, #000 88%, transparent); }
.wf-policy-nav-tabs.has-more-left.has-more-right { -webkit-mask-image: linear-gradient(to right, transparent, #000 12%, #000 88%, transparent); mask-image: linear-gradient(to right, transparent, #000 12%, #000 88%, transparent); }
@media (min-width: 1200px) {
    .wf-policy-nav-tabs { gap: 6px; }
    .wf-policy-tab-btn { padding: 8px 13px; }
}

/* Body */
.rc-body { background: var(--rc-bg); padding: 56px 0 80px; }
.rc-section { margin-bottom: 64px; scroll-margin-top: 170px; }
.rc-section:last-child { margin-bottom: 0; }
.rc-section-head { max-width: 680px; margin: 0 0 28px; }
.rc-eyebrow { display: block; font-size: 13px; font-weight: 800; letter-spacing: .6px; text-transform: uppercase; color: var(--rc-blue); margin-bottom: 8px; }
.rc-section-head h2 { font-size: clamp(24px, 3vw, 30px); font-weight: 800; color: var(--rc-ink); margin: 0 0 8px; line-height: 1.25; }
.rc-section-head p { font-size: 16px; color: var(--rc-text); line-height: 1.65; margin: 0; }

/* Stages */
.rc-stages { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; counter-reset: rc-stage; }
.rc-stage {
    background: #fff; border: 1px solid var(--rc-line); border-radius: 16px;
    padding: 26px; display: flex; flex-direction: column; position: relative;
}
.rc-stage.is-highlight { border: 2px solid var(--rc-blue); box-shadow: 0 12px 30px rgba(24,82,156,.12); }
.rc-stage-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
.rc-stage-num {
    width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
    background: var(--rc-blue-soft); color: var(--rc-blue);
    display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px;
}
.rc-stage.is-highlight .rc-stage-num { background: var(--rc-blue); color: #fff; }
.rc-chip {
    display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px;
    font-size: 12px; font-weight: 700; background: #f1f5f9; color: var(--rc-text);
}
.rc-chip-blue { background: var(--rc-blue-soft); color: var(--rc-blue); }
.rc-stage h3 { font-size: 19px; font-weight: 800; color: var(--rc-ink); margin: 0 0 6px; }
.rc-stage-when { font-size: 14.5px; color: var(--rc-text); margin: 0 0 18px; line-height: 1.55; }
.rc-stage dl { margin: auto 0 0; border-top: 1px solid var(--rc-line); padding-top: 16px; display: grid; gap: 14px; }
.rc-stage dt { font-size: 12px; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; color: var(--rc-text); margin-bottom: 4px; }
.rc-stage dd { margin: 0; font-size: 15px; color: var(--rc-ink); line-height: 1.55; font-weight: 600; }

/* Steps + contact */
.rc-how { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); gap: 24px; align-items: start; }
.rc-steps { list-style: none; margin: 0; padding: 0; display: grid; gap: 14px; }
.rc-step {
    background: #fff; border: 1px solid var(--rc-line); border-radius: 14px;
    padding: 20px 22px; display: flex; gap: 16px;
}
.rc-step-num {
    width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
    background: var(--rc-navy); color: #fff; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
}
.rc-step h3 { font-size: 16.5px; font-weight: 800; color: var(--rc-ink); margin: 4px 0 6px; }
.rc-step p { font-size: 15px; color: var(--rc-text); line-height: 1.6; margin: 0; }
.rc-contact {
    background: #fff; border: 1px solid var(--rc-line); border-radius: 16px; padding: 24px;
    position: sticky; top: 170px;
}
.rc-contact h3 { font-size: 17px; font-weight: 800; color: var(--rc-ink); margin: 0 0 4px; }
.rc-contact > p { font-size: 14px; color: var(--rc-text); margin: 0 0 16px; }
.rc-contact-list { list-style: none; margin: 0 0 16px; padding: 0; display: grid; gap: 10px; }
.rc-contact-link {
    display: flex; align-items: center; gap: 12px; padding: 12px 14px; min-height: 52px;
    border: 1px solid var(--rc-line); border-radius: 12px; text-decoration: none !important;
    color: var(--rc-ink) !important; transition: border-color .2s ease, background-color .2s ease;
}
a.rc-contact-link:hover { border-color: var(--rc-blue); background: var(--rc-blue-soft); }
.rc-contact-link i { width: 20px; text-align: center; color: var(--rc-blue); font-size: 16px; }
.rc-contact-link small { display: block; font-size: 12px; color: var(--rc-text); font-weight: 600; }
.rc-contact-link span { font-size: 14.5px; font-weight: 700; overflow-wrap: anywhere; }
.rc-contact-hours { font-size: 13px; color: var(--rc-text); margin: 0; }

/* Refund panel */
.rc-panel { background: #fff; border: 1px solid var(--rc-line); border-radius: 16px; padding: 28px; }
.rc-checks { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px 32px; }
.rc-checks li { display: flex; gap: 12px; font-size: 15px; color: var(--rc-text); line-height: 1.6; }
.rc-checks li i { color: var(--rc-green); margin-top: 4px; flex-shrink: 0; }
.rc-checks strong { color: var(--rc-ink); }

/* Callout */
.rc-callout {
    display: flex; gap: 14px; align-items: flex-start; margin-top: 20px;
    background: var(--rc-amber-soft); border: 1px solid var(--rc-amber-line); border-radius: 14px;
    padding: 18px 20px; color: var(--rc-amber); font-size: 15px; line-height: 1.6;
}
.rc-callout i { font-size: 18px; margin-top: 3px; flex-shrink: 0; }
.rc-callout strong { color: #78350f; }

/* Other terms */
.rc-terms { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.rc-term { background: #fff; border: 1px solid var(--rc-line); border-radius: 14px; padding: 22px; }
.rc-term h3 { font-size: 16px; font-weight: 800; color: var(--rc-ink); margin: 0 0 8px; display: flex; align-items: center; gap: 10px; }
.rc-term h3 i { color: var(--rc-blue); }
.rc-term p { font-size: 14.5px; color: var(--rc-text); line-height: 1.6; margin: 0; }

/* Grievance */
.rc-grievance {
    background: linear-gradient(135deg, #062b5d 0%, #18529c 100%); color: #fff;
    border-radius: 20px; padding: 36px 40px;
    display: flex; align-items: center; justify-content: space-between; gap: 28px; flex-wrap: wrap;
}
.rc-grievance h2 { color: #fff; font-size: 22px; font-weight: 800; margin: 0 0 8px; }
.rc-grievance p { color: rgba(255,255,255,.88); font-size: 15px; line-height: 1.6; margin: 0; max-width: 620px; }
.rc-grievance-actions { display: flex; gap: 12px; flex-wrap: wrap; }

@media (max-width: 991px) {
    .rc-facts-grid, .rc-stages, .rc-terms { grid-template-columns: 1fr; }
    .rc-how { grid-template-columns: 1fr; }
    .rc-contact { position: static; }
    .rc-checks { grid-template-columns: 1fr; }
    .wf-policy-nav-wrap { top: 70px; }
}
@media (max-width: 575px) {
    .rc-hero { padding: 56px 0 92px; }
    .rc-hero-lead { font-size: 16px; }
    .rc-hero-actions .rc-btn { width: 100%; }
    .rc-fact, .rc-stage, .rc-panel { padding: 20px; }
    .rc-step { padding: 18px; }
    .rc-grievance { padding: 28px 22px; }
    .rc-grievance-actions, .rc-grievance-actions .rc-btn { width: 100%; }
    .rc-section { margin-bottom: 48px; }
}
@media (prefers-reduced-motion: reduce) {
    .rc-page *, .wf-policy-tab-btn { transition: none !important; }
}
</style>

<div class="rc-page">
    <section class="rc-hero">
        <div class="container">
            <div class="rc-hero-inner">
                <span class="rc-hero-badge"><i class="fas fa-file-shield" aria-hidden="true"></i> Official Policy</span>
                <h1>Refund &amp; Cancellation Policy</h1>
                <p class="rc-hero-lead">You can cancel your loan application at any time before the money is disbursed. After disbursement, you can still exit the loan without any penalty during the RBI cooling-off period.</p>
                <p class="rc-hero-meta">Last updated: 19 November 2026</p>
                <div class="rc-hero-actions">
                    <a href="#how-to-cancel" class="rc-btn rc-btn-light"><i class="fas fa-list-check" aria-hidden="true"></i> How to cancel</a>
                    <a href="tel:+919560260240" class="rc-btn rc-btn-ghost"><i class="fas fa-phone" aria-hidden="true"></i> Call 95602 60240</a>
                </div>
            </div>
        </div>
    </section>

    <section class="container rc-facts" aria-label="Key facts">
        <div class="rc-facts-grid">
            <div class="rc-fact">
                <div class="rc-fact-icon"><i class="fas fa-ban" aria-hidden="true"></i></div>
                <div>
                    <span class="rc-fact-label">Before disbursement</span>
                    <span class="rc-fact-value">Cancel any time</span>
                    <span class="rc-fact-note">No penalty for cancelling your application.</span>
                </div>
            </div>
            <div class="rc-fact">
                <div class="rc-fact-icon"><i class="fas fa-hourglass-half" aria-hidden="true"></i></div>
                <div>
                    <span class="rc-fact-label">Cooling-off period</span>
                    <span class="rc-fact-value">1 to 3 days after disbursement</span>
                    <span class="rc-fact-note">Your exact period is in your Key Fact Statement.</span>
                </div>
            </div>
            <div class="rc-fact">
                <div class="rc-fact-icon"><i class="fas fa-rotate-left" aria-hidden="true"></i></div>
                <div>
                    <span class="rc-fact-label">Refund time</span>
                    <span class="rc-fact-value">Within 72 hours</span>
                    <span class="rc-fact-note">After your refund is approved, to the original payment method.</span>
                </div>
            </div>
        </div>
    </section>

    <nav class="wf-policy-nav-wrap" aria-label="Policies">
        <div class="container">
            <div class="wf-policy-nav-tabs">
                <a href="fair-practice-code" class="wf-policy-tab-btn">Fair Practices Code</a>
                <a href="grievance-redressal-policy" class="wf-policy-tab-btn">Grievance Redressal Policy</a>
                <a href="interest-rate-policy" class="wf-policy-tab-btn">Interest Rate Policy</a>
                <a href="anti-money-laundering" class="wf-policy-tab-btn">Anti Money Laundering Policy</a>
                <a href="privacy-policy" class="wf-policy-tab-btn">Privacy Policy</a>
                <a href="terms-condition" class="wf-policy-tab-btn">Terms & Conditions</a>
                <a href="refund-cancellation" class="wf-policy-tab-btn active" aria-current="page">Refund Policy</a>
                <a href="digital-lending" class="wf-policy-tab-btn">Digital Lending</a>
            </div>
        </div>
    </nav>

    <div class="rc-body">
        <div class="container">

            <section class="rc-section" id="your-options" aria-labelledby="options-title">
                <div class="rc-section-head">
                    <span class="rc-eyebrow">Your options</span>
                    <h2 id="options-title">What you can do at each stage</h2>
                    <p>What you can do, and what you pay, depends on whether the loan amount has reached your bank account.</p>
                </div>
                <div class="rc-stages">
                    <article class="rc-stage">
                        <div class="rc-stage-top">
                            <span class="rc-stage-num" aria-hidden="true">1</span>
                            <span class="rc-chip">Application stage</span>
                        </div>
                        <h3>Before disbursement</h3>
                        <p class="rc-stage-when">From the time you apply until the loan amount is credited to your bank account.</p>
                        <dl>
                            <div><dt>What you can do</dt><dd>Cancel your loan application.</dd></div>
                            <div><dt>What you pay</dt><dd>Nothing, except any fee you have already paid that your Key Fact Statement shows as non-refundable.</dd></div>
                        </dl>
                    </article>
                    <article class="rc-stage is-highlight">
                        <div class="rc-stage-top">
                            <span class="rc-stage-num" aria-hidden="true">2</span>
                            <span class="rc-chip rc-chip-blue"><i class="fas fa-building-columns" aria-hidden="true"></i> RBI mandated</span>
                        </div>
                        <h3>During the cooling-off period</h3>
                        <p class="rc-stage-when">At least 1 day for loans with a tenure under 7 days, and at least 3 days for loans with a tenure of 7 days or more, counted from disbursement.</p>
                        <dl>
                            <div><dt>What you can do</dt><dd>Exit the loan completely, with no prepayment penalty.</dd></div>
                            <div><dt>What you pay</dt><dd>The principal plus the proportionate APR for the days you used the money. A one-time processing fee is retained only if your Key Fact Statement disclosed it.</dd></div>
                        </dl>
                    </article>
                    <article class="rc-stage">
                        <div class="rc-stage-top">
                            <span class="rc-stage-num" aria-hidden="true">3</span>
                            <span class="rc-chip">Active loan</span>
                        </div>
                        <h3>After the cooling-off period</h3>
                        <p class="rc-stage-when">The loan can no longer be cancelled, and the repayment schedule in your loan agreement applies.</p>
                        <dl>
                            <div><dt>What you can do</dt><dd>Prepay or foreclose the loan.</dd></div>
                            <div><dt>What you pay</dt><dd>The outstanding amount and any charges set out in your loan agreement and Key Fact Statement.</dd></div>
                        </dl>
                    </article>
                </div>
            </section>

            <section class="rc-section" id="how-to-cancel" aria-labelledby="how-title">
                <div class="rc-section-head">
                    <span class="rc-eyebrow">How to cancel</span>
                    <h2 id="how-title">Cancel an application or exit a loan in 3 steps</h2>
                    <p>For an exit in the cooling-off period, contact us before the period ends.</p>
                </div>
                <div class="rc-how">
                    <ol class="rc-steps">
                        <li class="rc-step">
                            <span class="rc-step-num" aria-hidden="true">1</span>
                            <div>
                                <h3>Send us your request</h3>
                                <p>Email or call us, or use the app. Share your full name, registered mobile number and your application or loan number. Never share your OTP or password.</p>
                            </div>
                        </li>
                        <li class="rc-step">
                            <span class="rc-step-num" aria-hidden="true">2</span>
                            <div>
                                <h3>We confirm the details</h3>
                                <p>We verify your identity and tell you in writing the exact amount payable, if any, and how to pay it.</p>
                            </div>
                        </li>
                        <li class="rc-step">
                            <span class="rc-step-num" aria-hidden="true">3</span>
                            <div>
                                <h3>We close it and confirm</h3>
                                <p>Once any amount due is received, we cancel the application or close the loan and send you a confirmation. If a refund is due, we process it as described below.</p>
                            </div>
                        </li>
                    </ol>
                    <aside class="rc-contact" aria-labelledby="contact-title">
                        <h3 id="contact-title">Contact customer care</h3>
                        <p>Use any one of these channels.</p>
                        <ul class="rc-contact-list">
                            <li><a class="rc-contact-link" href="mailto:customercare@waqtfinance.com?subject=Loan%20cancellation%20request"><i class="fas fa-envelope" aria-hidden="true"></i><div><small>Email</small><span>customercare@<wbr>waqtfinance.com</span></div></a></li>
                            <li><a class="rc-contact-link" href="tel:+919560260240"><i class="fas fa-phone" aria-hidden="true"></i><div><small>Phone</small><span>95602 60240</span></div></a></li>
                            <li><div class="rc-contact-link"><i class="fas fa-mobile-screen" aria-hidden="true"></i><div><small>WAQT Finance app</small><span>Profile &rarr; Help and grievances</span></div></div></li>
                        </ul>
                        <p class="rc-contact-hours"><i class="far fa-clock" aria-hidden="true"></i> Monday to Saturday, 10:00 AM to 6:00 PM</p>
                    </aside>
                </div>
            </section>

            <section class="rc-section" id="refunds" aria-labelledby="refunds-title">
                <div class="rc-section-head">
                    <span class="rc-eyebrow">Refunds</span>
                    <h2 id="refunds-title">How and when you get your money back</h2>
                </div>
                <div class="rc-panel">
                    <ul class="rc-checks">
                        <li><i class="fas fa-circle-check" aria-hidden="true"></i><span>Approved refunds are credited <strong>within 72 hours of approval</strong>.</span></li>
                        <li><i class="fas fa-circle-check" aria-hidden="true"></i><span>Refunds go to the <strong>same payment method</strong> you used, unless we agree otherwise with you.</span></li>
                        <li><i class="fas fa-circle-check" aria-hidden="true"></i><span>If you pay <strong>more than you owe</strong>, we refund the extra amount to your bank account after verification.</span></li>
                        <li><i class="fas fa-circle-check" aria-hidden="true"></i><span>Interest is charged only for the <strong>actual number of days</strong> you used the money.</span></li>
                    </ul>
                </div>
                <div class="rc-callout" role="note">
                    <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                    <div><strong>Pay only through official channels.</strong> Make payments only through the WAQT Finance app, payment links sent by us, or our company bank account. Never pay into a personal account. WAQT Finance staff will never ask for your OTP or password.</div>
                </div>
            </section>

            <section class="rc-section" id="other-terms" aria-labelledby="terms-title">
                <div class="rc-section-head">
                    <span class="rc-eyebrow">Other terms</span>
                    <h2 id="terms-title">Good to know</h2>
                </div>
                <div class="rc-terms">
                    <div class="rc-term">
                        <h3><i class="fas fa-shield-halved" aria-hidden="true"></i> Additional services</h3>
                        <p>If we offer insurance or other services with a loan, their own cancellation terms apply. We tell you those terms before you buy them.</p>
                    </div>
                    <div class="rc-term">
                        <h3><i class="fas fa-file-signature" aria-hidden="true"></i> In your loan documents</h3>
                        <p>This policy is shared with you when you apply and is part of your loan agreement. Your cooling-off period is shown in your Key Fact Statement.</p>
                    </div>
                    <div class="rc-term">
                        <h3><i class="fas fa-rotate" aria-hidden="true"></i> Changes to this policy</h3>
                        <p>We may update this policy. We will post changes on this page and inform affected customers through the app, SMS or email.</p>
                    </div>
                </div>
            </section>

            <section class="rc-section" aria-labelledby="grievance-title">
                <div class="rc-grievance">
                    <div>
                        <h2 id="grievance-title">Not satisfied with how your request was handled?</h2>
                        <p>Write to our Grievance Redressal Officer. If your complaint is not resolved within 30 days, you can also complain to the Reserve Bank of India.</p>
                    </div>
                    <div class="rc-grievance-actions">
                        <a href="grievance-redressal-policy" class="rc-btn rc-btn-light"><i class="fas fa-scale-balanced" aria-hidden="true"></i> Grievance process</a>
                        <a href="mailto:customercare@waqtfinance.com?subject=Grievance%3A%20refund%20or%20cancellation" class="rc-btn rc-btn-ghost"><i class="fas fa-envelope" aria-hidden="true"></i> Email the officer</a>
                    </div>
                </div>
            </section>

        </div>
    </div>
</div>

<script>
(function () {
    var tabs = document.querySelector('.wf-policy-nav-tabs');
    var active = tabs && tabs.querySelector('.wf-policy-tab-btn.active');
    if (!tabs) return;
    var updateEdges = function () {
        var max = tabs.scrollWidth - tabs.clientWidth;
        tabs.classList.toggle('has-more-left', max > 1 && tabs.scrollLeft > 1);
        tabs.classList.toggle('has-more-right', max > 1 && tabs.scrollLeft < max - 1);
    };
    if (active && tabs.scrollWidth > tabs.clientWidth) {
        var offset = active.getBoundingClientRect().left - tabs.getBoundingClientRect().left + tabs.scrollLeft;
        tabs.scrollLeft = offset - (tabs.clientWidth - active.offsetWidth) / 2;
    }
    updateEdges();
    tabs.addEventListener('scroll', updateEdges, { passive: true });
    window.addEventListener('resize', updateEdges);
})();
</script>

<?php include 'includes/footer.php'; ?>
