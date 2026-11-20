<?php
$current_page = basename($_SERVER['PHP_SELF'], ".php");
?>
<link rel="stylesheet" href="<?php echo $prefix ?? ''; ?>assets/css/responsive-header-fixes.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/responsive-header-fixes.css'); ?>">
<link rel="stylesheet" href="<?php echo $prefix ?? ''; ?>assets/css/site-responsive.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/site-responsive.css'); ?>">

<div class="loan-topbar">
  <marquee behavior="scroll" scrollamount="5">
    Make smarter financial moves today and take confident steps toward a future filled with financial stability, growth, and long-term success with Waqt Finance guiding you at every stage of your journey.
  </marquee>
</div>

<!-- category bar -->
<header>
  <div class="loan-category-bar">
    <div class="loan-category-inner">
      <div class="loan-left-menu">
        <a href="<?php echo $prefix; ?>edi-loan">
          <div class="loan-menu-item <?= ($current_page == 'edi-loan' || $current_page == 'edi-loan-apply') ? 'loan-active' : '' ?>">
            <i data-lucide="building"></i>
            EDI
          </div>
        </a>

        <a href="<?php echo $prefix; ?>pay-day-loan">
          <div class="loan-menu-item <?= ($current_page == 'pay-day-loan' || $current_page == 'payday-apply') ? 'loan-active' : '' ?>">
            <i data-lucide="calendar"></i>
            PayDay
          </div>
        </a>

        <a href="<?php echo $prefix; ?>personal-loan">
          <div class="loan-menu-item <?= ($current_page == 'personal-loan' || $current_page == 'personal-loan-apply') ? 'loan-active' : '' ?>">
            <i data-lucide="user"></i>
            Personal
          </div>
        </a>

        <a href="<?php echo $prefix; ?>business-loan">
          <div class="loan-menu-item <?= ($current_page == 'bussiness-loan' || $current_page == 'business-loan' || $current_page == 'business-loan-apply') ? 'loan-active' : '' ?>">
            <i data-lucide="briefcase"></i>
            Business
          </div>
        </a>

        <a href="<?php echo $prefix; ?>vehicle-loan">
          <div class="loan-menu-item <?= ($current_page == 'vehicle-loan' || $current_page == 'vehicle-loan-apply') ? 'loan-active' : '' ?>">
            <i data-lucide="car"></i>
            Vehicle
          </div>
        </a>

        <a href="<?php echo $prefix; ?>shopkeeper-business-loan">
          <div class="loan-menu-item <?= ($current_page == 'shopkeeper-business-loan') ? 'loan-active' : '' ?>">
            <i data-lucide="store"></i>
            Shopkeeper Business Loan
          </div>
        </a>

        <a href="<?php echo $prefix; ?>emi-calculator">
          <div class="loan-menu-item <?= ($current_page == 'emi-calculator') ? 'loan-active' : '' ?>">
            <i data-lucide="calculator"></i>
            EMI Calculator
          </div>
        </a>

        <a href="<?php echo $prefix; ?>free-credit-score">
          <div class="loan-menu-item <?= ($current_page == 'free-credit-score') ? 'loan-active' : '' ?>">
            <i data-lucide="bar-chart-3"></i>
            CIBIL Score
          </div>
        </a>

        <a href="<?php echo $prefix; ?>pay-online">
          <div class="loan-menu-item <?= ($current_page == 'pay-online') ? 'loan-active' : '' ?>">
            <i data-lucide="credit-card"></i>
            Pay Online
          </div>
        </a>
      </div>

      <div class="loan-phone">
        <a href="mailto:customercare@waqtfinance.com"><i data-lucide="mail" aria-hidden="true"></i><span>customercare@waqtfinance.com</span></a>
        <a href="tel:+919217086610"><i data-lucide="phone" aria-hidden="true"></i><span>+91 92170 86610</span></a>
      </div>
    </div>
  </div>

  <!-- navbar -->
  <div class="loan-navbar">
    <div class="loan-navbar-inner">
      <div class="loan-logo">
        <a href="/">
          <img src="<?php echo $prefix; ?>assets/images/logo1.webp" alt="Waqt Finance Logo">
        </a>
      </div>

      <!-- hamburger & mobile actions -->
      <div class="loan-mobile-actions">
        <a href="<?php echo $prefix; ?>apply" class="btn-mobile-apply">Apply Now</a>
        <button class="loan-hamburger" data-bs-toggle="offcanvas" data-bs-target="#staticBackdrop" aria-controls="staticBackdrop" aria-label="Open Navigation Menu" style="background: none; border: none; padding: 0; color: inherit; cursor: pointer;">
          <i data-lucide="menu"></i>
        </button>
      </div>

      <div class="loan-nav-links" id="loanNavMenu">
        <a href="/" class="<?= ($current_page == 'index' || $current_page == '') ? 'active' : '' ?>">Home</a>
        <a href="<?php echo $prefix; ?>about-us" class="<?= ($current_page == 'about-us') ? 'active' : '' ?>">About</a>
        <a href="<?php echo $prefix; ?>products" class="<?= ($current_page == 'products') ? 'active' : '' ?>">Products</a>
        <?php
        // Published locations from Admin > Location CMS (falls back to config/location-pages.php if the table is missing).
        $header_locations = [];
        try {
            require_once __DIR__ . '/location_data.php';
            $header_locations = array_map(static fn(array $l): array => ['slug' => $l['slug'], 'name' => $l['name']], waqt_list_locations());
        } catch (Throwable $e) {
            error_log('Header locations: ' . $e->getMessage());
        }
        // Location pages are served by location-detail.php / product-city-landing.php, so use their slug for highlighting.
        $header_current_loc = isset($location_key) ? (string) $location_key : (isset($city_key) ? (string) $city_key : '');
        $is_loc_active = $header_current_loc !== '' || $current_page === 'locations';
        ?>
        <div class="wf-nav-dropdown">
          <a href="#" class="wf-dropdown-trigger <?= $is_loc_active ? 'active' : '' ?>">Locations <i class="fas fa-chevron-down" style="font-size: 10px; margin-left: 2px;"></i></a>
          <div class="wf-dropdown-menu">
            <?php foreach ($header_locations as $loc): ?>
              <a href="<?php echo $prefix; ?>loan-in-<?php echo htmlspecialchars($loc['slug']); ?>" class="<?= ($header_current_loc === $loc['slug']) ? 'active' : '' ?>"><?php echo htmlspecialchars($loc['name']); ?></a>
            <?php endforeach; ?>
            <a href="<?php echo $prefix; ?>locations" class="wf-dropdown-all <?= ($current_page === 'locations') ? 'active' : '' ?>">All locations <i class="fas fa-arrow-right" style="font-size: 10px; margin-left: 4px;"></i></a>
          </div>
        </div>
        <a href="<?php echo $prefix; ?>faqs" class="<?= ($current_page == 'faqs' || $current_page == 'faq') ? 'active' : '' ?>">FAQs</a>
        <a href="<?php echo $prefix; ?>repay" class="<?= ($current_page == 'repay') ? 'active' : '' ?>">Repay</a>
        <a href="<?php echo $prefix; ?>policies" class="<?= ($current_page == 'policies') ? 'active' : '' ?>">Media Center & Policies</a>
        <a href="<?php echo $prefix; ?>contact-us" class="<?= ($current_page == 'contact-us') ? 'active' : '' ?>">Contact</a>
      </div>

      <div class="loan-actions">
        <a href="<?php echo $prefix; ?>blog" class="loan-btn loan-tools text-decoration-none d-inline-flex align-items-center justify-content-center">
            Blog
        </a>
        <a href="<?php echo $prefix; ?>partner" class="loan-btn loan-quick text-decoration-none d-inline-flex align-items-center justify-content-center">
            Partner Program
        </a>
        <a href="<?php echo $prefix; ?>apply" class="loan-btn loan-apply text-decoration-none d-inline-flex align-items-center justify-content-center">
            Apply
        </a>
      </div>
    </div>
  </div>
</header>

<script>
  lucide.createIcons();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var drawer = document.getElementById('staticBackdrop');
    if (!drawer || !window.bootstrap) return;
    drawer.addEventListener('hidden.bs.offcanvas', function () {
      document.querySelectorAll('.offcanvas-backdrop').forEach(function (el) { el.remove(); });
      document.body.classList.remove('offcanvas-backdrop', 'modal-open');
      document.body.style.removeProperty('overflow');
      document.body.style.removeProperty('padding-right');
    });
  });
</script>

<!-- Mobile Navigation Drawer (Offcanvas) -->
<?php
$wf_home = ($prefix ?? '') !== '' ? $prefix : './';
$wf_cur = static fn(array $pages): bool => in_array($current_page, $pages, true);
$wf_link = static function (string $href, string $icon, string $label, bool $active) use ($prefix): string {
    return '<a href="' . htmlspecialchars(($prefix ?? '') . ($href !== '' ? $href : './'), ENT_QUOTES, 'UTF-8') . '" class="bank-nav-link' . ($active ? ' active" aria-current="page' : '') . '">'
        . '<i class="bi ' . $icon . '" aria-hidden="true"></i><span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span></a>';
};
?>
<div class="offcanvas offcanvas-start wf-custom-drawer" tabindex="-1" id="staticBackdrop" aria-labelledby="wfDrawerTitle">
  <div class="offcanvas-header">
    <a href="<?php echo htmlspecialchars($wf_home, ENT_QUOTES, 'UTF-8'); ?>" class="wf-drawer-header-brand" aria-label="Waqt Finance home">
      <img src="<?php echo $prefix; ?>assets/images/logo1.webp" alt="" width="40" height="40">
      <span id="wfDrawerTitle">Waqt Finance</span>
    </a>
    <button type="button" class="wf-drawer-close-btn" data-bs-dismiss="offcanvas" aria-label="Close menu"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
  </div>

  <div class="offcanvas-body">
    <div class="wf-drawer-cta">
      <a href="<?php echo $prefix; ?>apply" class="wf-drawer-cta-primary"><i class="bi bi-lightning-charge" aria-hidden="true"></i> Apply for a loan</a>
      <a href="<?php echo $prefix; ?>repay" class="wf-drawer-cta-secondary<?php echo $wf_cur(['repay']) ? ' active' : ''; ?>"><i class="bi bi-cash-coin" aria-hidden="true"></i> Repay / Pay EMI</a>
    </div>

    <nav class="wf-drawer-nav-group" aria-label="Main">
      <?php
      echo $wf_link('', 'bi-house-door', 'Home', $wf_cur(['index', '']));
      echo $wf_link('about-us', 'bi-info-circle', 'About Us', $wf_cur(['about-us']));
      echo $wf_link('products', 'bi-grid', 'All Products', $wf_cur(['products']));
      ?>
      <div class="wf-drawer-acc">
        <button type="button" class="bank-nav-link wf-drawer-acc-btn<?php echo $is_loc_active ? ' active' : ''; ?>" aria-expanded="<?php echo $is_loc_active ? 'true' : 'false'; ?>" aria-controls="wfDrawerLocations">
          <i class="bi bi-geo-alt" aria-hidden="true"></i><span>Locations</span><i class="bi bi-chevron-down wf-drawer-acc-icon" aria-hidden="true"></i>
        </button>
        <div id="wfDrawerLocations" class="wf-drawer-acc-panel"<?php echo $is_loc_active ? '' : ' hidden'; ?>>
          <?php foreach ($header_locations as $loc): $on = $header_current_loc === $loc['slug']; ?>
            <a href="<?php echo $prefix; ?>loan-in-<?php echo htmlspecialchars($loc['slug'], ENT_QUOTES, 'UTF-8'); ?>"<?php echo $on ? ' class="active" aria-current="page"' : ''; ?>><?php echo htmlspecialchars($loc['name'], ENT_QUOTES, 'UTF-8'); ?></a>
          <?php endforeach; ?>
          <a href="<?php echo $prefix; ?>locations"<?php echo $current_page === 'locations' ? ' class="active" aria-current="page"' : ''; ?>>All locations</a>
        </div>
      </div>
      <?php
      echo $wf_link('faqs', 'bi-question-circle', 'FAQs', $wf_cur(['faqs', 'faq']));
      echo $wf_link('policies', 'bi-file-earmark-text', 'Policies & Documents', $wf_cur(['policies']));
      echo $wf_link('contact-us', 'bi-envelope', 'Contact Us', $wf_cur(['contact-us']));
      ?>
    </nav>

    <nav class="wf-drawer-nav-group" aria-labelledby="wfDrawerLoans">
      <span class="wf-drawer-group-label" id="wfDrawerLoans">Loan products</span>
      <?php
      echo $wf_link('personal-loan', 'bi-person', 'Personal Loan', $wf_cur(['personal-loan', 'personal-loan-apply']));
      echo $wf_link('business-loan', 'bi-briefcase', 'Business Loan', $wf_cur(['business-loan', 'business-loan-apply']));
      echo $wf_link('pay-day-loan', 'bi-calendar-event', 'Payday Loan', $wf_cur(['pay-day-loan', 'payday-apply']));
      echo $wf_link('edi-loan', 'bi-cash-stack', 'EDI Loan', $wf_cur(['edi-loan', 'edi-loan-apply']));
      echo $wf_link('vehicle-loan', 'bi-car-front', 'Vehicle Loan', $wf_cur(['vehicle-loan', 'vehicle-loan-apply']));
      echo $wf_link('loan-against-property', 'bi-house', 'Loan Against Property', $wf_cur(['loan-against-property', 'loan-against-property-apply']));
      echo $wf_link('shopkeeper-business-loan', 'bi-shop', 'Shopkeeper Business Loan', $wf_cur(['shopkeeper-business-loan']));
      ?>
    </nav>

    <nav class="wf-drawer-nav-group" aria-labelledby="wfDrawerTools">
      <span class="wf-drawer-group-label" id="wfDrawerTools">Tools and more</span>
      <?php
      echo $wf_link('emi-calculator', 'bi-calculator', 'EMI Calculator', $wf_cur(['emi-calculator']));
      echo $wf_link('free-credit-score', 'bi-speedometer2', 'Free CIBIL Score', $wf_cur(['free-credit-score']));
      echo $wf_link('pay-online', 'bi-qr-code', 'Pay Online (UPI QR)', $wf_cur(['pay-online']));
      echo $wf_link('partner', 'bi-people', 'Partner Program', $wf_cur(['partner']));
      echo $wf_link('blog', 'bi-journal-text', 'Blog', $wf_cur(['blog', 'blog-detail']));
      ?>
    </nav>

    <div class="wf-drawer-support-card">
      <span class="wf-drawer-support-icon" aria-hidden="true"><i class="bi bi-headset"></i></span>
      <div>
        <span class="wf-drawer-support-label">Need help?</span>
        <a href="tel:+919217086610">+91 92170 86610</a>
        <a href="mailto:customercare@waqtfinance.com" class="wf-drawer-support-mail">customercare@waqtfinance.com</a>
        <span class="wf-drawer-support-hours">Mon to Sat, 10 AM to 6 PM</span>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Locations accordion inside the mobile drawer
    var accBtn = document.querySelector('.wf-drawer-acc-btn');
    var accPanel = document.getElementById('wfDrawerLocations');
    if (accBtn && accPanel) {
        accBtn.addEventListener('click', function () {
            var open = accBtn.getAttribute('aria-expanded') === 'true';
            accBtn.setAttribute('aria-expanded', open ? 'false' : 'true');
            accPanel.hidden = open;
        });
    }
    // While the drawer is open, hide floating widgets (WhatsApp chat) that would sit on top of it
    var drawer = document.getElementById('staticBackdrop');
    if (drawer) {
        drawer.addEventListener('show.bs.offcanvas', function () { document.documentElement.classList.add('wf-drawer-open'); });
        drawer.addEventListener('hidden.bs.offcanvas', function () { document.documentElement.classList.remove('wf-drawer-open'); });
    }
});
</script>
<style>
/* ==========================================================================
   PREMIUM MOBILE DRAWER CSS OVERRIDES
   ========================================================================== */
.wf-custom-drawer {
    max-width: 88vw !important;
    width: 330px !important;
    height: 100dvh !important;
    background: #ffffff !important;
    border: none !important;
    box-shadow: 16px 0 32px rgba(15, 23, 42, 0.12) !important;
    font-family: 'Inter', sans-serif !important;
}
.offcanvas-backdrop.show {
    background-color: rgba(15, 23, 42, 0.55) !important;
    opacity: 1 !important;
}
.wf-custom-drawer.offcanvas { transition: transform 220ms ease !important; }
@media (prefers-reduced-motion: reduce) { .wf-custom-drawer.offcanvas { transition: none !important; } }

/* Header */
.wf-custom-drawer .offcanvas-header {
    padding: 14px 16px 14px 18px !important;
    border-bottom: 1px solid #e5edf6 !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    background: #ffffff !important;
    flex-shrink: 0;
}
.wf-drawer-header-brand { display: flex !important; align-items: center !important; gap: 10px !important; text-decoration: none !important; }
.wf-drawer-header-brand img { width: 40px; height: 40px; }
.wf-drawer-header-brand span { font-size: 17px; font-weight: 800; color: #0b213a; }
.wf-drawer-close-btn {
    width: 40px !important; height: 40px !important; border-radius: 50% !important;
    background: #f1f5f9 !important; border: none !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
    cursor: pointer !important; padding: 0 !important; color: #0f2742 !important;
}
.wf-drawer-close-btn:hover { background: #e2e8f0 !important; }
.wf-drawer-close-btn i { font-size: 16px !important; }

/* Body */
.wf-custom-drawer .offcanvas-body {
    padding: 16px 14px 24px !important;
    overflow-y: auto !important;
    overscroll-behavior: contain;
    display: flex !important;
    flex-direction: column !important;
    gap: 18px !important;
    scrollbar-width: thin !important;
    box-sizing: border-box !important;
}
.wf-custom-drawer .offcanvas-body::-webkit-scrollbar { width: 4px !important; }
.wf-custom-drawer .offcanvas-body::-webkit-scrollbar-thumb { background: #cbd5e1 !important; border-radius: 4px !important; }

/* Quick actions */
.wf-drawer-cta { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.wf-drawer-cta a {
    display: flex; flex-direction: column; align-items: flex-start; gap: 6px;
    padding: 12px; border-radius: 12px; font-size: 13.5px; font-weight: 700; line-height: 1.25;
    text-decoration: none !important; min-height: 72px;
}
.wf-drawer-cta i { font-size: 18px; }
.wf-drawer-cta-primary { background: #18529c; color: #fff !important; }
.wf-drawer-cta-primary:hover { background: #0d3f7b; }
.wf-drawer-cta-secondary { background: #eaf3ff; color: #0b3f7f !important; border: 1px solid #d0e3fc; }
.wf-drawer-cta-secondary.active { border-color: #18529c; }

/* Groups and links */
.wf-drawer-nav-group { display: flex !important; flex-direction: column !important; gap: 2px !important; }
.wf-drawer-nav-group + .wf-drawer-nav-group { border-top: 1px solid #eef2f7; padding-top: 14px; }
.wf-drawer-group-label {
    font-size: 11.5px !important; font-weight: 700 !important; color: #64748b !important;
    text-transform: uppercase !important; letter-spacing: 0.8px !important;
    margin: 0 0 6px !important; padding: 0 12px !important; display: block !important;
}
.wf-custom-drawer .bank-nav-link {
    display: flex !important; align-items: center !important; gap: 14px !important;
    width: 100% !important; min-height: 46px !important; height: auto !important;
    padding: 10px 12px !important; border: 0 !important; border-radius: 10px !important;
    font-size: 15px !important; font-weight: 500 !important; color: #0f2742 !important;
    text-decoration: none !important; text-align: left !important;
    background: transparent !important; box-shadow: none !important; transform: none !important;
    position: relative; cursor: pointer; font-family: inherit !important; margin: 0 !important;
}
.wf-custom-drawer .bank-nav-link i { font-size: 18px !important; color: #64748b !important; width: 20px !important; flex-shrink: 0; text-align: center; }
.wf-custom-drawer .bank-nav-link:hover { background: #f5f8fc !important; color: #0b5cad !important; }
.wf-custom-drawer .bank-nav-link.active { background: #eaf3ff !important; color: #0b5cad !important; font-weight: 700 !important; }
.wf-custom-drawer .bank-nav-link.active::before { content: ''; position: absolute; left: 0; top: 10px; bottom: 10px; width: 3px; border-radius: 3px; background: #0b5cad; }
.wf-custom-drawer .bank-nav-link.active i { color: #0b5cad !important; }
.wf-custom-drawer a:focus-visible, .wf-custom-drawer button:focus-visible { outline: 3px solid #93c5fd !important; outline-offset: 1px; }

/* Locations accordion */
.wf-drawer-acc-icon { margin-left: auto; font-size: 13px !important; transition: transform .2s ease; }
.wf-drawer-acc-btn[aria-expanded="true"] .wf-drawer-acc-icon { transform: rotate(180deg); }
.wf-drawer-acc-panel { margin: 2px 0 6px 44px; padding-left: 10px; border-left: 2px solid #e2e8f0; display: flex; flex-direction: column; }
.wf-drawer-acc-panel[hidden] { display: none; }
.wf-drawer-acc-panel a { padding: 9px 10px; border-radius: 8px; font-size: 14.5px; color: #334155 !important; text-decoration: none !important; }
.wf-drawer-acc-panel a:hover { background: #f5f8fc; color: #0b5cad !important; }
.wf-drawer-acc-panel a.active { color: #0b5cad !important; font-weight: 700; background: #eaf3ff; }

/* Support card */
.wf-drawer-support-card { display: flex; gap: 12px; align-items: flex-start; padding: 14px; background: #f5f9ff; border: 1px solid #d0e3fc; border-radius: 12px; }
.wf-drawer-support-icon { background: #fff; color: #18529c; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.wf-drawer-support-card a { display: block; font-size: 15px; font-weight: 800; color: #18529c !important; text-decoration: none !important; margin: 2px 0; }
.wf-drawer-support-card a.wf-drawer-support-mail { font-size: 13px; font-weight: 600; word-break: break-all; }
.wf-drawer-support-label { font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .5px; display: block; }
.wf-drawer-support-hours { font-size: 12px; color: #64748b; display: block; margin-top: 2px; }

/* Hide floating chat widgets while the drawer is open */
html.wf-drawer-open #authkey-chat-widget,
html.wf-drawer-open [id^="authkey"],
html.wf-drawer-open [class*="authkey"],
html.wf-drawer-open .whatsapp-float,
html.wf-drawer-open .wf-whatsapp-float,
html.wf-drawer-open #scroll-top { display: none !important; }

/* Mobile top navbar overrides */
@media (max-width: 991px) {
    header {
        display: flex !important;
        flex-direction: column !important;
    }
    
    .loan-navbar {
        order: 1 !important;
        position: relative !important;
        z-index: 10 !important;
        background: #ffffff !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    
    .loan-navbar-inner {
        height: 60px !important;
        padding: 0 16px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }
    
    .loan-logo {
        display: flex !important;
        align-items: center !important;
    }
    
    .loan-logo img {
        height: 40px !important;
        width: auto !important;
        position: static !important;
        margin: 0 !important;
        top: auto !important;
        left: auto !important;
    }
    
    .loan-hamburger {
        position: static !important;
        margin: 0 !important;
        width: 44px !important;
        height: 44px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        left: auto !important;
    }
    
    .loan-hamburger i {
        font-size: 24px !important;
        color: #0f2742 !important;
    }
    
    .loan-category-bar {
        order: 2 !important;
        margin-left: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        overflow: hidden !important;
    }
    
    .loan-category-inner {
        width: 100% !important;
        justify-content: flex-start !important;
        padding: 0 !important;
    }
    
    .loan-left-menu {
        width: 100% !important;
        overflow-x: auto !important;
        display: flex !important;
        flex-wrap: nowrap !important;
        -webkit-overflow-scrolling: touch !important;
        scrollbar-width: none !important;
        padding: 10px 16px !important;
        gap: 12px !important;
    }
    
    .loan-left-menu::-webkit-scrollbar {
        display: none !important;
    }
}

    /* Header Dropdown styles */
    .wf-nav-dropdown {
        position: relative;
        display: inline-block;
    }
    .wf-dropdown-trigger {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        text-decoration: none;
        color: #0b213a;
        font-weight: 500;
        font-size: 15px;
        padding: 8px 12px;
        transition: color 0.2s ease;
    }
    .wf-dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        background-color: #ffffff;
        min-width: 160px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        z-index: 1000;
        padding: 8px 0;
        margin-top: 10px;
    }
    .wf-dropdown-menu::before {
        content: '';
        position: absolute;
        top: -6px;
        left: 50%;
        transform: translateX(-50%) rotate(45deg);
        width: 12px;
        height: 12px;
        background: #ffffff;
        border-left: 1px solid #e2e8f0;
        border-top: 1px solid #e2e8f0;
    }
    /* Invisible bridge to prevent hover loss over the 10px gap */
    .wf-dropdown-menu::after {
        content: '';
        position: absolute;
        top: -15px;
        left: 0;
        right: 0;
        height: 15px;
        background: transparent;
        z-index: -1;
    }
    .wf-dropdown-menu a {
        display: block;
        padding: 8px 16px;
        color: #334155 !important;
        text-decoration: none !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        transition: background 0.2s ease, color 0.2s ease;
        text-align: left;
    }
    .wf-dropdown-menu a:hover {
        background-color: #f1f5f9;
        color: #18529c !important;
    }
    .wf-dropdown-menu a.active {
        color: #18529c !important;
        background-color: #f1f5f9;
        font-weight: 700 !important;
    }
    .wf-nav-dropdown:hover .wf-dropdown-menu {
        display: block;
    }
    .loan-mobile-actions {
        display: none;
    }
    .btn-mobile-apply {
        display: none;
    }
    .collapse:not(.show) {
        display: none !important;
    }
    .collapse.show {
        display: block !important;
    }
    @media (max-width: 991px) {
        .loan-mobile-actions {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
        }
        .btn-mobile-apply {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: linear-gradient(135deg, #0056D6 0%, #003CA8 100%) !important;
            color: #ffffff !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            padding: 6px 14px !important;
            border-radius: 50px !important;
            text-decoration: none !important;
            box-shadow: 0 4px 10px rgba(0, 86, 214, 0.15) !important;
            transition: all 0.2s ease !important;
            border: none !important;
            white-space: nowrap !important;
        }
        .btn-mobile-apply:active {
            transform: scale(0.96) !important;
            box-shadow: 0 2px 6px rgba(0, 86, 214, 0.1) !important;
        }
        .bank-nav-link .bi-chevron-down {
            transition: transform 0.2s ease !important;
        }
        .bank-nav-link[aria-expanded="true"] .bi-chevron-down {
            transform: rotate(180deg) !important;
        }
    }
</style>
