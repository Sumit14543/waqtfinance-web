<?php
$current_page = basename($_SERVER['PHP_SELF'], ".php");
?>
<link rel="stylesheet" href="<?php echo $prefix ?? ''; ?>assets/css/responsive-header-fixes.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/responsive-header-fixes.css'); ?>">

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
        <i data-lucide="mail"></i>
        <span style="margin-right:20px">customercare@waqtfinance.com</span>
        <i data-lucide="phone"></i>
        9217086610
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
        $header_locations = [];
        if (function_exists('db')) {
            try {
                $header_loc_res = db()->query("SELECT name, slug FROM location_pages WHERE status = 'published' ORDER BY name ASC");
                if ($header_loc_res) {
                    $header_locations = $header_loc_res->fetch_all(MYSQLI_ASSOC);
                }
            } catch (Throwable $e) {
                error_log($e->getMessage());
            }
        }
        if (empty($header_locations)) {
            $header_locations = [
                ['slug' => 'delhi-ncr', 'name' => 'Delhi NCR'],
                ['slug' => 'delhi', 'name' => 'Delhi'],
                ['slug' => 'noida', 'name' => 'Noida & Greater Noida'],
                ['slug' => 'gurugram', 'name' => 'Gurugram']
            ];
        }
        $header_active_slugs = array_column($header_locations, 'slug');
        $header_active_pages = array_map(fn($s) => 'loan-in-' . $s, $header_active_slugs);
        $is_loc_active = in_array($current_page, $header_active_pages, true);
        ?>
        <div class="wf-nav-dropdown">
          <a href="#" class="wf-dropdown-trigger <?= $is_loc_active ? 'active' : '' ?>">Locations <i class="fas fa-chevron-down" style="font-size: 10px; margin-left: 2px;"></i></a>
          <div class="wf-dropdown-menu">
            <?php foreach ($header_locations as $loc): ?>
              <a href="<?php echo $prefix; ?>loan-in-<?php echo htmlspecialchars($loc['slug']); ?>" class="<?= ($current_page == 'loan-in-' . $loc['slug']) ? 'active' : '' ?>"><?php echo htmlspecialchars($loc['name']); ?></a>
            <?php endforeach; ?>
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
<div class="offcanvas offcanvas-start" tabindex="-1" id="staticBackdrop" aria-labelledby="staticBackdropLabel" style="width: 280px; background: #ffffff;">
  <div class="offcanvas-header" style="border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between;">
    <div class="loan-logo">
      <a href="/">
        <img src="<?php echo $prefix; ?>assets/images/logo1.webp" alt="Waqt Finance Logo" style="height: 36px; width: auto;">
      </a>
    </div>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close" style="font-size: 14px; color: #475569; background: none; border: none; padding: 0; cursor: pointer;"><i class="bi bi-x-lg"></i></button>
  </div>
  <div class="offcanvas-body" style="padding: 20px; display: flex; flex-direction: column; gap: 20px; box-sizing: border-box; overflow-y: auto; height: calc(100% - 70px);">
    <div class="nav-item-custom">
      <a href="<?php echo $prefix; ?>repay" class="bank-nav-link">
        <i class="bi bi-cash-coin"></i>
        <span>Repay</span>
      </a>
    </div>
    
        <!-- About Us Link -->
    <div class="nav-item-custom">
      <a href="<?php echo $prefix; ?>about-us" class="bank-nav-link">
        <i class="bi bi-info-circle"></i>
        <span>About Us</span>
      </a>
    </div>
    
    <div class="nav-item-custom">
      <a href="<?php echo $prefix; ?>policies" class="bank-nav-link">
        <i class="bi bi-file-earmark-lock"></i>
        <span>Media Center & Policies</span>
      </a>
    </div>

    <!-- LOAN PRODUCTS SECTION -->
    <div class="wf-drawer-nav-group">
      <span class="wf-drawer-group-label" style="font-family: 'Inter', sans-serif !important;">Loan Products</span>
      
      <div class="nav-item-custom">
        <a href="<?php echo $prefix; ?>personal-loan" class="bank-nav-link">
          <i class="bi bi-person"></i>
          <span>Personal Loan</span>
        </a>
      </div>
      
      <div class="nav-item-custom">
        <a href="<?php echo $prefix; ?>business-loan" class="bank-nav-link">
          <i class="bi bi-briefcase"></i>
          <span>Business Loan</span>
        </a>
      </div>
      
      <div class="nav-item-custom">
        <a href="<?php echo $prefix; ?>vehicle-loan" class="bank-nav-link">
          <i class="bi bi-car-front"></i>
          <span>Vehicle Loan</span>
        </a>
      </div>
      
      <div class="nav-item-custom">
        <a href="<?php echo $prefix; ?>edi-loan" class="bank-nav-link">
          <i class="bi bi-cash-stack"></i>
          <span>EDI Loan</span>
        </a>
      </div>
      
      <div class="nav-item-custom">
        <a href="<?php echo $prefix; ?>pay-day-loan" class="bank-nav-link">
          <i class="bi bi-calendar-event"></i>
          <span>Payday Loan</span>
        </a>
      </div>
      
      <div class="nav-item-custom">
        <a href="<?php echo $prefix; ?>loan-against-property" class="bank-nav-link">
          <i class="bi bi-house-door"></i>
          <span>Loan Against Property</span>
        </a>
      </div>
      
      <div class="nav-item-custom">
        <a href="<?php echo $prefix; ?>shopkeeper-business-loan" class="bank-nav-link">
          <i class="bi bi-shop"></i>
          <span>Shopkeeper Business Loan</span>
        </a>
      </div>

      <div class="nav-item-custom">
        <a href="<?php echo $prefix; ?>partner" class="bank-nav-link">
          <i class="bi bi-share"></i>
          <span>Partner Program</span>
        </a>
      </div>
    </div>

    <!-- RESOURCES SECTION -->
    <div class="wf-drawer-nav-group">
      <span class="wf-drawer-group-label" style="font-family: 'Inter', sans-serif !important;">Resources</span>
      
      <div class="nav-item-custom">
        <a href="<?php echo $prefix; ?>blog" class="bank-nav-link">
          <i class="bi bi-journal-text"></i>
          <span>Blog</span>
        </a>
      </div>
      
      <div class="nav-item-custom">
        <a href="<?php echo $prefix; ?>apply" class="bank-nav-link">
          <i class="bi bi-file-earmark-text"></i>
          <span>Apply</span>
        </a>
      </div>

      <div class="nav-item-custom">
        <a href="<?php echo $prefix; ?>contact-us" class="bank-nav-link">
          <i class="bi bi-people"></i>
          <span>Contact</span>
        </a>
      </div>
    </div>

    <!-- Support Help Card at bottom -->
    <div class="wf-drawer-support-card" style="margin-top: 12px; padding: 16px; background: #eaf3ff; border: 1px solid #d0e3fc; border-radius: 12px; display: flex; gap: 12px; align-items: center; box-sizing: border-box;">
        <div style="background: #ffffff; color: #18529c; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; box-shadow: 0 2px 4px rgba(24, 82, 156, 0.05);">
            <i class="bi bi-headset"></i>
        </div>
        <div>
            <span style="font-size: 11.5px; font-weight: 600; color: #64748b; display: block; text-transform: uppercase; letter-spacing: 0.5px; font-family: 'Inter', sans-serif !important;">Need Help?</span>
            <a href="tel:9217086610" style="font-size: 14.5px; font-weight: 800; color: #18529c; text-decoration: none; display: block; margin: 1px 0 2px 0; font-family: 'Inter', sans-serif !important;">+91 92170 86610</a>
            <span style="font-size: 11px; color: #64748b; font-weight: 500; display: block; font-family: 'Inter', sans-serif !important;">Loan support available</span>
        </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const currentPath = window.location.pathname.toLowerCase();
    const links = document.querySelectorAll(".bank-nav-link");
    let matched = false;
    
    // Sort links by length of href descending to match more specific routes first
    const sortedLinks = Array.from(links).sort((a, b) => {
        return (b.getAttribute("href") || "").length - (a.getAttribute("href") || "").length;
    });
    
    for (const link of sortedLinks) {
        const href = link.getAttribute("href");
        if (!href) continue;
        
        // Remove trailing and leading slashes and .php extension to get clean names
        const cleanHref = href.replace(/^\/|\.php$/g, "").toLowerCase();
        const cleanPath = currentPath.replace(/^\/|\.php$/g, "").toLowerCase();
        
        if (cleanHref === "" && (cleanPath === "" || cleanPath === "index")) {
            link.classList.add("active");
            matched = true;
            break;
        } else if (cleanHref !== "" && (cleanPath === cleanHref || cleanPath.startsWith(cleanHref + "/") || cleanPath.endsWith("/" + cleanHref))) {
            // Special checks for sub-application matching
            if (cleanHref === "edi-loan" && cleanPath.startsWith("edi-loan")) {
                link.classList.add("active");
                matched = true;
                break;
            }
            if (cleanHref === "pay-day-loan" && cleanPath.startsWith("payday")) {
                link.classList.add("active");
                matched = true;
                break;
            }
            
            link.classList.add("active");
            matched = true;
            break;
        }
    }
    
    // Fallback default active if nothing matched
    if (!matched) {
        const homeLink = Array.from(links).find(l => l.getAttribute("href") === "/");
        if (homeLink) homeLink.classList.add("active");
    }

    // Locations collapse toggler for mobile menu
    const locTrigger = document.querySelector('a[href="#locationsCollapse"]');
    const locCollapse = document.getElementById('locationsCollapse');
    if (locTrigger && locCollapse) {
        locTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            const isOpen = locCollapse.classList.contains('show');
            if (isOpen) {
                locCollapse.classList.remove('show');
                locTrigger.setAttribute('aria-expanded', 'false');
            } else {
                locCollapse.classList.add('show');
                locTrigger.setAttribute('aria-expanded', 'true');
            }
        });
    }
});
</script>
<style>
/* ==========================================================================
   PREMIUM MOBILE DRAWER CSS OVERRIDES
   ========================================================================== */
.wf-custom-drawer {
    max-width: 86vw !important;
    width: 320px !important;
    height: 100dvh !important;
    background: #ffffff !important;
    border: none !important;
    box-shadow: 16px 0 32px rgba(15, 23, 42, 0.08) !important;
}

/* Backdrop Overlay styling */
.offcanvas-backdrop.show {
    background-color: rgba(15, 23, 42, 0.55) !important;
    opacity: 1 !important;
    transition: opacity 180ms ease !important;
}

.wf-custom-drawer.offcanvas {
    transition: transform 220ms ease !important;
}

/* Offcanvas Header Styles */
.wf-custom-drawer .offcanvas-header {
    padding: 20px 24px !important;
    border-bottom: 1px solid #e5edf6 !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    background: #ffffff !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 100 !important;
}

.wf-drawer-header-brand {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
}

.wf-drawer-close-btn {
    width: 36px !important;
    height: 36px !important;
    border-radius: 50% !important;
    background: #f1f5f9 !important;
    border: none !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    opacity: 0.8 !important;
    cursor: pointer !important;
    padding: 0 !important;
    transition: all 0.2s ease !important;
}

.wf-drawer-close-btn:hover {
    background: #e2e8f0 !important;
    opacity: 1 !important;
    transform: rotate(90deg) !important;
}

.wf-drawer-close-btn i {
    font-size: 16px !important;
    color: #475569 !important;
}

/* Offcanvas Body Styles */
.wf-custom-drawer .offcanvas-body {
    padding: 24px !important;
    overflow-y: auto !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 24px !important;
    scrollbar-width: thin !important;
    box-sizing: border-box !important;
}

.wf-custom-drawer .offcanvas-body::-webkit-scrollbar {
    width: 4px !important;
}

.wf-custom-drawer .offcanvas-body::-webkit-scrollbar-thumb {
    background: #cbd5e1 !important;
    border-radius: 4px !important;
}

/* Menu Group and Item Styles */
.wf-drawer-nav-group {
    display: flex !important;
    flex-direction: column !important;
    gap: 4px !important;
}

.wf-drawer-group-label {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #64748b !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
    margin-bottom: 8px !important;
    margin-top: 4px !important;
    display: block !important;
}

.wf-custom-drawer .bank-nav-link {
    display: flex !important;
    align-items: center !important;
    gap: 14px !important;
    height: 48px !important;
    padding: 0 16px !important;
    border-radius: 8px !important;
    font-size: 14.5px !important;
    font-weight: 500 !important;
    color: #0f2742 !important;
    text-decoration: none !important;
    transition: all 150ms ease !important;
    box-sizing: border-box !important;
    border-left: 4px solid transparent !important;
    background: transparent !important;
    box-shadow: none !important;
    transform: none !important;
}

.wf-custom-drawer .bank-nav-link i {
    font-size: 18px !important;
    color: #64748b !important;
    width: 20px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.wf-custom-drawer .bank-nav-link:hover {
    background: #f8fafc !important;
    color: #0b5cad !important;
    transform: translateX(4px) !important;
}

.wf-custom-drawer .bank-nav-link.active {
    background: #eaf3ff !important;
    color: #0b5cad !important;
    font-weight: 600 !important;
    border-left-color: #0b5cad !important;
}

.wf-custom-drawer .bank-nav-link.active i {
    color: #0b5cad !important;
}

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
