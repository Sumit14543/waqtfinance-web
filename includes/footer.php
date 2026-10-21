<?php
// Path Prefix Support
$prefix = isset($path_prefix) ? $path_prefix : "";
?>

<style>
/* ==========================================================================
   ENTERPRISE FINTECH FOOTER CSS (KOTAK STYLE - LIGHT THEME)
   ========================================================================== */
.wf-footer-cta-strip {
    background: linear-gradient(135deg, #0b5cad 0%, #062b5d 100%);
    color: #ffffff;
    padding: 40px 24px;
    font-family: 'Inter', sans-serif !important;
}

.wf-footer-cta-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 24px;
}

.wf-footer-cta-text h3 {
    font-size: 24px !important;
    font-weight: 800 !important;
    margin: 0 0 6px 0 !important;
    color: #ffffff !important;
    letter-spacing: -0.3px;
    font-family: 'Inter', sans-serif !important;
}

.wf-footer-cta-text p {
    font-size: 15px !important;
    color: rgba(255, 255, 255, 0.9) !important;
    margin: 0 !important;
    font-family: 'Inter', sans-serif !important;
}

.wf-footer-cta-buttons {
    display: flex;
    gap: 16px;
}

.wf-footer-btn-primary {
    background: #0066D6;
    color: #ffffff !important;
    padding: 12px 28px;
    font-weight: 700;
    font-size: 14.5px;
    border-radius: 8px;
    text-decoration: none !important;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 4px 6px rgba(0, 102, 214, 0.15);
}

.wf-footer-btn-primary:hover {
    background: #0052ad;
    transform: translateY(-2px);
    box-shadow: 0 8px 12px rgba(0, 102, 214, 0.25);
}

.wf-footer-btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff !important;
    padding: 12px 28px;
    font-weight: 700;
    font-size: 14.5px;
    border-radius: 8px;
    text-decoration: none !important;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    border: 1px solid rgba(255, 255, 255, 0.25);
}

.wf-footer-btn-secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

/* Main Footer CSS */
.wf-main-footer-container {
    background-color: #f8fafc;
    color: #334155;
    font-family: 'Inter', sans-serif !important;
    padding-top: 56px;
    border-top: 1px solid #e2e8f0;
}

.wf-footer-grid {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 28px;
}

.wf-footer-col {
    display: flex;
    flex-direction: column;
}

.wf-footer-col-title {
    font-size: 13.5px !important;
    font-weight: 800 !important;
    color: #0f172a !important;
    margin: 0 0 16px 0 !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-family: 'Inter', sans-serif !important;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.wf-footer-col-content nav {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.wf-footer-col-content nav a {
    color: #475569 !important;
    font-size: 13px;
    text-decoration: none !important;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    line-height: 1.4;
}

.wf-footer-col-content nav a:hover {
    color: #0066D6 !important;
    text-decoration: underline !important;
    transform: translateX(2px);
}

.wf-footer-sub-section {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #f1f5f9;
}

.wf-footer-sub-section-title {
    font-size: 13.5px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.wf-footer-sub-section nav {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.wf-footer-sub-section nav a {
    color: #475569 !important;
    font-size: 13px;
    text-decoration: none !important;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    line-height: 1.4;
}

.wf-footer-sub-section nav a:hover {
    color: #0066D6 !important;
    text-decoration: underline !important;
    transform: translateX(2px);
}

/* Brand Area & Contact */
.wf-footer-logo-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.wf-footer-logo-wrapper img {
    height: 32px;
    width: auto;
}

.wf-footer-logo-wrapper span {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    white-space: nowrap;
}

.wf-footer-brand-desc {
    font-size: 12.5px !important;
    color: #64748b !important;
    line-height: 1.5;
    margin: 0 0 16px 0 !important;
}

.wf-footer-company-reg {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-top: 12px;
}

.wf-footer-company-reg span {
    font-size: 11px;
    color: #64748b;
    font-family: monospace;
}

/* Trust Bar / Security badges */
.wf-footer-trust-bar {
    max-width: 1200px;
    margin: 40px auto 0 auto;
    padding: 20px 24px;
    border-top: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.wf-trust-badges-left {
    display: flex;
    align-items: center;
    gap: 24px;
}

.wf-trust-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
}

.wf-trust-badge i {
    color: #0066D6;
    font-size: 16px;
}

/* Bottom elements */
.wf-footer-bottom-bar-new {
    background-color: #f1f5f9;
    padding: 24px 0;
    border-top: 1px solid #e2e8f0;
}

.wf-footer-bottom-container-new {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 24px;
}

.wf-connect-section {
    display: flex;
    align-items: center;
    gap: 16px;
}

.wf-connect-section span {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
}

.wf-social-icons-new {
    display: flex;
    gap: 8px;
}

.wf-social-icon-new {
    width: 32px;
    height: 32px;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 50%;
    color: #475569 !important;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    text-decoration: none !important;
    transition: all 0.2s;
}

.wf-social-icon-new.facebook:hover {
    background: #1877f2;
    border-color: #1877f2;
    color: #fff !important;
}
.wf-social-icon-new.linkedin:hover {
    background: #0077b5;
    border-color: #0077b5;
    color: #fff !important;
}
.wf-social-icon-new.instagram:hover {
    background: #e1306c;
    border-color: #e1306c;
    color: #fff !important;
}
.wf-social-icon-new.twitter:hover {
    background: #0f1419;
    border-color: #0f1419;
    color: #fff !important;
}
.wf-social-icon-new.youtube:hover {
    background: #ff0000;
    border-color: #ff0000;
    color: #fff !important;
}

/* Security Trust Badges like Entrust, VeriSign */
.wf-security-logos {
    display: flex;
    align-items: center;
    gap: 12px;
}

.wf-security-logo-badge {
    font-size: 11.5px;
    font-weight: 700;
    color: #64748b;
    border: 1px solid #cbd5e1;
    padding: 6px 12px;
    border-radius: 4px;
    background: #fff;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.wf-security-logo-badge i {
    color: #10b981;
}

/* Group select */
.wf-group-dropdown select {
    background: #fff;
    border: 1px solid #cbd5e1;
    color: #475569;
    padding: 8px 16px;
    font-size: 13px;
    border-radius: 4px;
    font-weight: 600;
    outline: none;
    cursor: pointer;
}

/* Legal Copyright strip */
.wf-footer-copyright-strip {
    background: #fff;
    padding: 16px 0;
    border-top: 1px solid #e2e8f0;
}

.wf-footer-copyright-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    font-size: 12.5px;
    color: #64748b;
}

.wf-copyright-links {
    display: flex;
    gap: 16px;
}

.wf-copyright-links a {
    color: #64748b !important;
    text-decoration: none !important;
}

.wf-copyright-links a:hover {
    color: #0066D6 !important;
    text-decoration: underline !important;
}

/* Responsive Overrides */
@media (max-width: 1024px) {
    .wf-footer-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }
}

@media (max-width: 768px) {
    .wf-footer-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    .wf-footer-trust-bar {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    .wf-trust-badges-left {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .wf-footer-bottom-container-new {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
}

@media (max-width: 568px) {
    .wf-footer-grid {
        grid-template-columns: 1fr;
        gap: 0;
    }
    .wf-footer-col {
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 4px;
    }
    .wf-footer-col-title {
        margin-bottom: 0 !important;
        padding: 16px 0 !important;
        cursor: pointer;
    }
    .wf-footer-col-title::after {
        content: "\f078";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        font-size: 11px;
        transition: transform 0.2s ease;
    }
    .wf-footer-col.active .wf-footer-col-title::after {
        transform: rotate(180deg);
    }
    .wf-footer-col-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.25s ease-out;
    }
    .wf-footer-col.active .wf-footer-col-content {
        max-height: 500px;
        padding-bottom: 16px;
    }
}
</style>

<?php
$hide_footer_cta = isset($footer_cta_hide) && $footer_cta_hide === true;
$footer_title = isset($footer_cta_title) ? $footer_cta_title : "Need Funds For Your Business?";
$footer_desc = isset($footer_cta_desc) ? $footer_cta_desc : "Apply today and receive quick approval with transparent documentation.";
?>

<?php if (!$hide_footer_cta): ?>
<div class="wf-footer-cta-strip">
    <div class="wf-footer-cta-container">
        <div class="wf-footer-cta-text">
            <h3><?php echo $footer_title; ?></h3>
            <p><?php echo $footer_desc; ?></p>
        </div>
        <div class="wf-footer-cta-buttons">
            <a href="<?php echo $prefix; ?>apply" class="wf-footer-btn-primary">Apply Now</a>
            <a href="<?php echo $prefix; ?>contact-us" class="wf-footer-btn-secondary">Talk To Expert</a>
        </div>
    </div>
</div>
<?php endif; ?>

<footer class="wf-main-footer-container" role="contentinfo">
    <div class="wf-footer-grid">
        <!-- Column 1 -->
        <div class="wf-footer-col brand-col">
            <div class="wf-footer-logo-wrapper">
                <img src="<?php echo $prefix; ?>assets/images/logo1.webp" alt="Waqt Finance Logo">
                <span>WAQT Finance</span>
            </div>
            <p class="wf-footer-brand-desc">Waqt Finance is a registered NBFC providing quick and flexible loan solutions across India.</p>
            
            <div class="wf-footer-sub-section">
                <h5 class="wf-footer-sub-section-title">Investor Relations</h5>
                <nav aria-label="Investor Relations Links">
                    <a href="<?php echo $prefix; ?>policies">Corporate Overview</a>
                    <a href="<?php echo $prefix; ?>fair-practice-code">Fair Practice Code</a>
                </nav>
            </div>
        </div>
        
        <!-- Column 2 -->
        <div class="wf-footer-col accordion-col">
            <h4 class="wf-footer-col-title">About & Products</h4>
            <div class="wf-footer-col-content">
                <nav aria-label="Loan Products Links">
                    <a href="<?php echo $prefix; ?>about-us">About us</a>
                    <a href="<?php echo $prefix; ?>personal-loan">Personal Loan</a>
                    <a href="<?php echo $prefix; ?>business-loan">Business Loan</a>
                    <a href="<?php echo $prefix; ?>vehicle-loan">Vehicle Loan</a>
                    <a href="<?php echo $prefix; ?>pay-day-loan">Payday Loan</a>
                    <a href="<?php echo $prefix; ?>edi-loan">EDI Loan</a>
                    <a href="<?php echo $prefix; ?>loan-against-property">Loan Against Property</a>
                </nav>
            </div>
        </div>
        
        <!-- Column 3 -->
        <div class="wf-footer-col accordion-col">
            <h4 class="wf-footer-col-title">Calculators & Tools</h4>
            <div class="wf-footer-col-content">
                <nav aria-label="Calculators Links">
                    <a href="<?php echo $prefix; ?>emi-calculator">Personal Loan EMI</a>
                    <a href="<?php echo $prefix; ?>business-loan-apply">Business Loan EMI</a>
                    <a href="<?php echo $prefix; ?>eligibility-calculator">Eligibility Calculator</a>
                    <a href="<?php echo $prefix; ?>free-credit-score">Free Credit Score</a>
                </nav>
            </div>
        </div>
        
        <!-- Column 4 -->
        <div class="wf-footer-col accordion-col">
            <h4 class="wf-footer-col-title">Customer Service</h4>
            <div class="wf-footer-col-content">
                <nav aria-label="Customer Service Links">
                    <a href="<?php echo $prefix; ?>faqs">Important Information</a>
                    <a href="<?php echo $prefix; ?>contact-us">Write to us</a>
                    <a href="<?php echo $prefix; ?>grievance-redressal-policy">Grievance Redressal</a>
                    <a href="<?php echo $prefix; ?>refund-cancellation">Refund Policy</a>
                </nav>
            </div>
        </div>
        
        <!-- Column 5 -->
        <div class="wf-footer-col accordion-col">
            <h4 class="wf-footer-col-title">Rates & Areas</h4>
            <div class="wf-footer-col-content">
                <nav aria-label="Rates and Areas Links">
                    <a href="<?php echo $prefix; ?>interest-rate-policy">Interest Rates</a>
                    <a href="<?php echo $prefix; ?>policies">Fees & Charges</a>
                    <hr style="border-color: rgba(0,0,0,0.06); margin: 8px 0;">
                    <span style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 4px; letter-spacing: 0.5px;">Areas Served</span>
                    <?php
                    $footer_locations = [];
                    if (function_exists('db')) {
                        try {
                            $footer_loc_res = db()->query("SELECT name, slug FROM location_pages WHERE status = 'published' ORDER BY name ASC");
                            if ($footer_loc_res) {
                                $footer_locations = $footer_loc_res->fetch_all(MYSQLI_ASSOC);
                            }
                        } catch (Throwable $e) {
                            error_log($e->getMessage());
                        }
                    }
                    if (empty($footer_locations)) {
                        $footer_locations = [
                            ['slug' => 'delhi-ncr', 'name' => 'Delhi NCR'],
                            ['slug' => 'delhi', 'name' => 'Delhi'],
                            ['slug' => 'noida', 'name' => 'Noida & Greater Noida'],
                            ['slug' => 'gurugram', 'name' => 'Gurugram']
                        ];
                    }
                    foreach ($footer_locations as $loc): ?>
                        <a href="<?php echo $prefix; ?>loan-in-<?php echo htmlspecialchars($loc['slug']); ?>"><?php echo htmlspecialchars($loc['name']); ?></a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>
        
        <!-- Column 6 -->
        <div class="wf-footer-col accordion-col">
            <h4 class="wf-footer-col-title">Legal & Others</h4>
            <div class="wf-footer-col-content">
                <nav aria-label="Legal Links">
                    <a href="<?php echo $prefix; ?>privacy-policy">Privacy Policy</a>
                    <a href="<?php echo $prefix; ?>terms-condition">Terms & Conditions</a>
                    <a href="<?php echo $prefix; ?>#"><i class="fas fa-lock" style="font-size: 10px; margin-right: 4px;"></i> Admin Login</a>
                </nav>
                
                <div class="wf-footer-sub-section">
                    <h5 class="wf-footer-sub-section-title">Contact us</h5>
                    <nav>
                        <a href="tel:9217086610"><i class="fas fa-phone-alt" style="margin-right: 6px; color: #0066D6;"></i> +91 92170 86610</a>
                        <a href="mailto:customercare@waqtfinance.com"><i class="fas fa-envelope" style="margin-right: 6px; color: #0066D6;"></i> customercare@waqtfinance.com</a>
                        <span style="display: flex; align-items: flex-start; color: #475569; font-size: 13px; line-height: 1.4; margin-top: 4px;">
                            <i class="fas fa-map-marker-alt" style="color: #0066D6; margin-top: 3px; font-size: 14px; margin-right: 6px;"></i>
                            <span>BSI Business Park, Sector 63, Noida, UP - 201301</span>
                        </span>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Trust Bar -->
    <div class="wf-footer-trust-bar">
        <div class="wf-trust-badges-left">
            <div class="wf-trust-badge">
                <i class="fas fa-landmark"></i>
                <span>RBI Registered</span>
            </div>
            <div class="wf-trust-badge">
                <i class="fas fa-bolt"></i>
                <span>Fast Approval</span>
            </div>
            <div class="wf-trust-badge">
                <i class="fas fa-lock"></i>
                <span>Data Privacy Shield</span>
            </div>
        </div>
        <div class="wf-connect-section">
            <span>Connect With Us</span>
            <div class="wf-social-icons-new">
                <a href="https://www.facebook.com/waqtfinance" aria-label="Facebook" class="wf-social-icon-new facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.linkedin.com/company/waqt-finance-pvt-ltd/" aria-label="LinkedIn" class="wf-social-icon-new linkedin"><i class="fab fa-linkedin-in"></i></a>
                <a href="https://www.instagram.com/waqt.finance" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="wf-social-icon-new instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="Twitter X" class="wf-social-icon-new twitter"><i class="fab fa-x-twitter"></i></a>
                <a href="#" aria-label="YouTube" class="wf-social-icon-new youtube"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>
    
    <!-- Legal Copyright strip -->
    <div class="wf-footer-copyright-strip">
        <div class="wf-footer-copyright-container">
            <div>
                &copy; 2026 WAQT Finance Pvt Ltd. All Rights Reserved. | CIN: U65923DL2022PTC396001 | NBFC Reg No: B.10.00143
            </div>
            <div class="wf-copyright-links">
                <a href="<?php echo $prefix; ?>sitemap.xml">Sitemap</a>
            </div>
        </div>
    </div>
</footer>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const accordionTitles = document.querySelectorAll(".wf-footer-col-title");
    accordionTitles.forEach(title => {
        title.addEventListener("click", function() {
            if (window.innerWidth <= 568) {
                const parentCol = this.closest(".wf-footer-col");
                parentCol.classList.toggle("active");
            }
        });
    });
});
</script>

<div class="sticky-sunrise"></div>

<a href="#" class="scroll-top" id="scroll-top" aria-label="Scroll to top">
    <i class="fa fa-arrow-up"></i>
</a>

<!-- Global Dependencies (Deferred for FCP & PageSpeed Performance) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" defer></script>
<!--<script src="<?php echo $prefix; ?>assets/js/bootstrap.min.js" defer></script> -->

<?php if (isset($load_odometer) && $load_odometer): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/odometer.js/0.4.8/odometer.min.js" defer></script>
<?php endif; ?>

<?php if (isset($load_intl_tel) && $load_intl_tel): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js" defer></script>
<?php endif; ?>

<?php 
$load_chart = (isset($current_page_name) && in_array($current_page_name, ['emi-calculator.php', 'eligibility-calculator.php', 'pay-day-loan.php', 'payday-apply.php'], true));
if ($load_chart): 
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
<?php endif; ?>

<?php if (isset($load_sweetalert) && $load_sweetalert): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
<?php endif; ?>

<?php if (isset($load_aos) && $load_aos): ?>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js" defer></script>
<?php endif; ?>

<?php if (isset($load_slick) && $load_slick): ?>
<script src="<?php echo $prefix; ?>assets/js/slick.min.js" defer></script>
<?php endif; ?>

<script src="<?php echo $prefix; ?>assets/js/script.js" defer></script>


<!-- Optimized JS Synchronization -->
<script>
document.addEventListener("DOMContentLoaded", function() {
$(document).ready(function() {

    // 1. Sliders (Initialized only if elements exist)
    if ($('.team-slider:not(.slick-initialized)').length) {
        $('.team-slider:not(.slick-initialized)').slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            arrows: true,
            prevArrow: '<button class="slick-arrow-custom slick-prev-custom"><svg width="18" height="18" viewBox="0 0 20 20"><path d="M12.5 3L6 10l6.5 7" fill="none" stroke="#fbbf24" stroke-width="2"/></svg></button>',
            nextArrow: '<button class="slick-arrow-custom slick-next-custom"><svg width="18" height="18" viewBox="0 0 20 20"><path d="M7.5 3L14 10l-6.5 7" fill="none" stroke="#fbbf24" stroke-width="2"/></svg></button>',
            dots: true,
            infinite: true,
            autoplay: true,
            autoplaySpeed: 3000,
            responsive: [
                { breakpoint: 1200, settings: { slidesToShow: 3 } },
                { breakpoint: 768, settings: { slidesToShow: 2 } },
                { breakpoint: 576, settings: { slidesToShow: 1 } }
            ]
        });
    }

    if ($('.hero_slider_wrap:not(.slick-initialized)').length) {
        $('.hero_slider_wrap:not(.slick-initialized)').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            infinite: true,
            autoplay: true,
            autoplaySpeed: 3000,
            arrows: false,
            dots: true
        });
    }

    if ($('.testimonial-slider:not(.slick-initialized)').length) {
        $('.testimonial-slider:not(.slick-initialized)').slick({
            slidesToShow: 3,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 1500,
            arrows: false,
            dots: true,
            responsive: [
                { breakpoint: 992, settings: { slidesToShow: 2 } },
                { breakpoint: 768, settings: { slidesToShow: 1 } }
            ]
        });
    }

    // 2. AOS (Initializes globally)
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: "ease-out-cubic",
            once: true
        });
    }

    // 3. Odometer (Observer-based)
    if ($('.odometer').length) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    el.innerHTML = el.dataset.count;
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.5 });
        $('.odometer').each(function() { observer.observe(this); });
    }

    // 4. Tab Logic
    $('.loan-tabs li').on('click', function() {
        let tab = $(this).data('tab');
        $('.loan-tabs li').removeClass('active');
        $(this).addClass('active');
        $('.tab-content').removeClass('active');
        $('#' + tab).addClass('active');
    });

    // 5. Exit Intent Popup
    let modalEl = document.getElementById("loanModal");
    if (modalEl) {
        let popupShown = false;
        function showPopup() {
            if (popupShown) return;
            let modal = new bootstrap.Modal(modalEl);
            modal.show();
            popupShown = true;
        }
        window.addEventListener("scroll", () => {
            if (window.scrollY + window.innerHeight > document.body.offsetHeight * 0.5) showPopup();
        });
        document.addEventListener("mouseleave", (e) => {
            if (e.clientY < 10) showPopup();
        });
    }

    // 6. Form Utility Functions
    function allowOnlyNumbers(id, maxLength) {
        let input = document.getElementById(id);
        if (!input) return;
        input.addEventListener("input", function() {
            this.value = this.value.replace(/\D/g, "").slice(0, maxLength);
            validateField(this, maxLength);
        });
    }

    function validateField(input, length) {
        input.classList.remove("is-valid", "is-invalid");
        if (input.value.length === 0) return;
        if (input.value.length === length) {
            input.classList.add("is-valid");
        } else {
            input.classList.add("is-invalid");
            input.classList.add("shake");
            setTimeout(() => input.classList.remove("shake"), 300);
        }
    }

    allowOnlyNumbers("mobile", 10);
    allowOnlyNumbers("pincode", 6);

    // 7. Loan Form Submission
    const loanForm = document.getElementById("loanForm");
    if (loanForm) {
        loanForm.addEventListener("submit", function(e) {
            e.preventDefault();
            let name = document.getElementById("fullName");
            let mobile = document.getElementById("mobile");
            let pincode = document.getElementById("pincode");
            let valid = true;

            [name, mobile, pincode].forEach(input => input.classList.remove("is-invalid"));

            if (!/^[A-Za-z ]{3,}$/.test(name.value.trim())) { name.classList.add("is-invalid"); valid = false; }
            if (!/^[6-9]\d{9}$/.test(mobile.value.trim())) { mobile.classList.add("is-invalid"); valid = false; }
            if (!/^\d{6}$/.test(pincode.value.trim())) { pincode.classList.add("is-invalid"); valid = false; }

            if (!valid) return;

            fetch("form-actions/submit-loan.php", {
                method: "POST",
                headers: {
                    "X-CSRF-Token": window.WAQT_CSRF_TOKEN || ""
                },
                body: new FormData(this)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    Swal.fire({ icon: "success", title: "Success 🎉", text: "We will contact you shortly!", confirmButtonColor: "#ff6a1a" });
                    if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
                    this.reset();
                }
            });
        });
    }

    // 8. General EMI Calculator (Table Based)
    if (document.getElementById('loan-amount-input')) {
        const formatCurrency = (amt) => "₹" + amt.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        
        const calculateEMI = () => {
            let P = parseFloat($('#loan-amount-input').val()),
                R = parseFloat($('#interest-rate-input').val()) / 100 / 12,
                N = parseFloat($('#loan-term-input').val());

            if (isNaN(P) || isNaN(R) || isNaN(N)) return;

            let emi = (P * R * Math.pow(1 + R, N)) / (Math.pow(1 + R, N) - 1);
            let balance = P, totalInt = 0, totalPay = 0;
            let html = '';

            for (let m = 1; m <= N; m++) {
                let inter = balance * R, princ = emi - inter;
                balance -= princ;
                totalInt += inter; totalPay += emi;
                html += `<tr><td>${m}</td><td>${formatCurrency(emi)}</td><td>${formatCurrency(princ)}</td><td>${formatCurrency(inter)}</td><td>${formatCurrency(Math.max(0, balance))}</td></tr>`;
            }
            $('#emi-table tbody').html(html);
            $('#total-row').html(`Total Interest: ${formatCurrency(totalInt)} | Total Payment: ${formatCurrency(totalPay)}`);
            $('#emi-table').removeClass('hidden');
        };

        $('#calculate-btn').on('click', calculateEMI);
        $('#reset-btn').on('click', () => { $('#emi-table').addClass('hidden'); $('#emi-table tbody').html(''); });
        
        // Sync sliders
        $('#loan-term-slider').on('input', function() { $('#loan-term-input').val(this.value); $('#loan-term-value').text(this.value + ' months'); });
    }

    // 9. Simplified Slider EMI Calc
    if (document.getElementById('loanSlider')) {
        const updateSimpleEMI = () => {
            let P = parseFloat($('#loanSlider').val()), R = 13/12/100, N = 60;
            let emi = (P * R * Math.pow(1 + R, N)) / (Math.pow(1 + R, N) - 1);
            $('#amount').text("₹ " + P.toLocaleString());
            $('#emi').text("₹ " + Math.round(emi).toLocaleString());
        };
        $('#loanSlider').on('input', updateSimpleEMI);
        updateSimpleEMI();
    }

    // 10. Payday / Short-term Loan Calc (Chart based)
    if (document.getElementById('loan') && document.getElementById('chart') && document.getElementById('days') && document.getElementById('fee')) {
        let loanChart;
        const updatePayday = () => {
            let P = parseFloat($('#loan').val()), D = parseFloat($('#days').val()), R = parseFloat($('#rate').val()), F = parseFloat($('#fee').val());
            let interest = P * (R / 100) * D, processing = P * (F / 100);
            
            $('#loanVal').text(P); $('#daysVal').text(D); $('#rateVal').text(R); $('#feeVal').text(F);
            $('#interest').text(interest.toFixed(2) + " INR"); $('#processing').text(processing.toFixed(2) + " INR");
            $('#total').text((P + interest).toFixed(2) + " INR"); $('#apr').text((((interest + processing) / P) * (365 / D) * 100).toFixed(2) + "%");

            if (loanChart) loanChart.destroy();
            loanChart = new Chart(document.getElementById("chart"), {
                type: 'pie',
                data: {
                    labels: ["Principal", "Interest", "Fees"],
                    datasets: [{ data: [P, interest, processing], backgroundColor: ["#4CAF50", "#FF9800", "#2196F3"] }]
                }
            });
        }
        $('#loan, #days, #rate, #fee').on('input', updatePayday);
        updatePayday();
    }
});
});

// Global Helpers
function plbOpenTab(evt, tabName) {
    let tabcontent = document.getElementsByClassName("plb-tabcontent");
    for (let i = 0; i < tabcontent.length; i++) tabcontent[i].style.display = "none";
    let tablinks = document.getElementsByClassName("plb-tablinks");
    for (let i = 0; i < tablinks.length; i++) tablinks[i].className = tablinks[i].className.replace(" plb-active", "");
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " plb-active";
}

// Loan-flow forms: clear stale field errors as soon as the user edits that field.
(function () {
    function clearFieldErrors(target) {
        if (!target) return;

        const group = target.closest(".lf-form-group");
        const scope = group || target.closest("form") || document;

        scope.querySelectorAll(".lf-error-text, .error, [id$='Error'], [id$='error']").forEach((el) => {
            el.innerHTML = "";
            el.textContent = "";
            if (el.style) el.style.display = "";
        });

        if (group) {
            group.querySelectorAll(".lf-error-border").forEach((el) => el.classList.remove("lf-error-border"));
        }

        target.classList.remove("lf-error-border");

        const prefixWrap = target.closest(".lf-input-prefix-wrap");
        if (prefixWrap) prefixWrap.classList.remove("lf-error-border");

        const uploadCard = target.closest(".lf-upload-card");
        if (uploadCard) uploadCard.classList.remove("lf-error-border");
    }

    document.addEventListener("input", function (event) {
        if (event.target.matches(".lf-input, .lf-select, .lf-textarea, .lf-upload-input, .lf-otp-box")) {
            clearFieldErrors(event.target);
        }
    });

    document.addEventListener("change", function (event) {
        if (event.target.matches(".lf-input, .lf-select, .lf-textarea, .lf-upload-input, .lf-check-wrap input, .lf-emp-group input")) {
            clearFieldErrors(event.target);
        }
    });

    document.addEventListener("click", function (event) {
        const toggle = event.target.closest(".lf-toggle-btn");
        if (toggle) clearFieldErrors(toggle);
    });
})();

// Global Auto-fill query parameters (like city) in forms
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const cityParam = urlParams.get('city');
    if (cityParam) {
        const cityInput = document.getElementById('city') || document.getElementById('personalCity');
        if (cityInput) {
            const cityName = cityParam.charAt(0).toUpperCase() + cityParam.slice(1).replace('-', ' ');
            cityInput.value = cityName;
            cityInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }
});

// Global Live Number Formatting with Indian Comma Separator
function formatCurrencyValue(target) {
    if (!target) return;
    const path = window.location.pathname;
    if (path.includes("emi-calculator") || path.includes("eligibility-calculator")) {
        return;
    }
    if (
        target.matches("input[data-currency], .wf-currency-input") ||
        (target.tagName === "INPUT" && 
         (!target.type || target.type === "text") && 
         target.getAttribute("inputmode") === "numeric" &&
         /amount|turnover|income|sales|price|salary|value|emi/i.test(target.id || target.name || ""))
    ) {
        let rawValue = target.value.replace(/,/g, '').replace(/[^0-9]/g, '').slice(0, 9);
        target.value = rawValue ? Number(rawValue).toLocaleString('en-IN') : "";
    }
}

document.addEventListener("input", function (event) {
    formatCurrencyValue(event.target);
});

document.addEventListener("change", function (event) {
    formatCurrencyValue(event.target);
});

// Format already populated values on load (e.g. from saved drafts)
document.addEventListener("DOMContentLoaded", function () {
    const path = window.location.pathname;
    if (path.includes("emi-calculator") || path.includes("eligibility-calculator")) {
        return;
    }
    setTimeout(function() {
        document.querySelectorAll("input").forEach(formatCurrencyValue);
    }, 150);
});
</script>

<!-- Global WhatsApp support widget: loaded once for every public page using the shared footer. -->
<script
    src="https://console.authkey.io/js/main.js"
    id="authkey-chat-widget"
    widget-id="b8577a2e-b547-40ec-b990-7a6b20f4a733"
    defer></script>
<noscript>
    <a href="https://wa.me/919217086610?text=Hello%20Waqt%20Finance%2C%20I%20need%20assistance."
       target="_blank"
       rel="noopener noreferrer"
       aria-label="Chat with Waqt Finance on WhatsApp"
       style="position:fixed;right:20px;bottom:20px;z-index:9999;background:#25d366;color:#fff;border-radius:999px;padding:12px 16px;font-weight:700;text-decoration:none;">
        WhatsApp Support
    </a>
</noscript>
</body>
</html>
