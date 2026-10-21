<?php
declare(strict_types=1);
require_once __DIR__ . '/config/db.php';

$conn = db();

// Fetch all published blogs
$all_blogs = [];
$res = $conn->query("SELECT * FROM blogs WHERE status = 'published' ORDER BY id DESC");
if ($res) {
    $all_blogs = $res->fetch_all(MYSQLI_ASSOC);
}

// 1. Separate Featured Post (the latest post with a cover image)
$featured_post = null;
foreach ($all_blogs as $b) {
    if (!empty($b['image_url']) && strpos($b['image_url'], 'assets/') !== false) {
        $featured_post = $b;
        break;
    }
}

// 2. Fetch Trending Blogs (top 3 by views)
$trending_blogs = [];
$trend_res = $conn->query("SELECT * FROM blogs WHERE status = 'published' ORDER BY views DESC LIMIT 3");
if ($trend_res) {
    $trending_blogs = $trend_res->fetch_all(MYSQLI_ASSOC);
}

// 3. Group remaining blogs by category
$blogs_by_cat = [
    'personal' => [],
    'lap' => [],
    'business' => [],
    'vehicle' => [],
    'payday' => [],
    'edi' => []
];

foreach ($all_blogs as $b) {
    $cat = $b['category'];
    if (isset($blogs_by_cat[$cat])) {
        $blogs_by_cat[$cat][] = $b;
    } else {
        $blogs_by_cat['personal'][] = $b;
    }
}

$page_title = "Personal & Business Loan Tips, Guides | Waqt Finance";
$meta_description = "Practical guides on personal, business, property, vehicle, payday & EDI loans — from Waqt Finance, an RBI-registered NBFC serving Delhi NCR.";
$meta_keywords = "loan blogs, personal loan tips, business loan guides, loan against property blog, vehicle loan India, payday loan insights";
$canonical_url = "https://waqtfinance.com/blog";

ob_start();
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Blog",
  "name": "Waqt Finance Loan Blogs",
  "url": "https://www.waqtfinance.com/blog",
  "description": "Loan blogs covering personal loan, business loan, loan against property, vehicle loan, payday loan and EDI loan topics in India.",
  "publisher": {
    "@type": "Organization",
    "name": "Waqt Finance",
    "logo": {
      "@type": "ImageObject",
      "url": "https://www.waqtfinance.com/assets/images/logo.png"
    }
  }
}
</script>
<?php
$extra_head = ob_get_clean();

include 'includes/header.php';
include 'includes/main-header.php';
?>

<!-- Premium Custom CSS Style Tag -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

    /* Variables override */
    :root {
        --blog-primary: #0c1a30;
        --blog-accent: #FFB800;
        --blog-text-dark: #1e293b;
        --blog-text-muted: #64748b;
        --blog-bg-light: #f8fafc;
        --blog-border: #e2e8f0;
    }

    body {
        background-color: #fcfdfe;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    /* Hero section styling */
    .blog-hero-section {
        background: linear-gradient(135deg, #070d19 0%, #0c1a30 100%);
        padding: 60px 0 100px 0;
        color: #ffffff;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .blog-hero-section::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 60px;
        background: #fcfdfe;
        clip-path: polygon(0 100%, 100% 100%, 100% 0);
    }
    .blog-hero-title {
        font-family: 'Outfit', sans-serif;
        font-size: 42px;
        font-weight: 800;
        letter-spacing: -0.5px;
        margin-bottom: 12px;
        background: linear-gradient(120deg, #ffffff, #FFB800);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .blog-hero-subtitle {
        font-size: 16px;
        color: #94a3b8;
        max-width: 600px;
        margin: 0 auto 30px auto;
        line-height: 1.6;
    }
    .blog-search-container {
        max-width: 500px;
        margin: 0 auto;
        position: relative;
    }
    .blog-search-input {
        width: 100%;
        padding: 16px 20px 16px 50px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        color: #ffffff;
        font-size: 14.5px;
        outline: none;
        backdrop-filter: blur(10px);
        transition: all 0.3s;
    }
    .blog-search-input:focus {
        border-color: var(--blog-accent);
        background: rgba(255, 255, 255, 0.12);
        box-shadow: 0 0 0 4px rgba(255, 184, 0, 0.25);
    }
    .blog-search-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--blog-accent);
        font-size: 16px;
    }

    /* Split layout (Featured + Trending) */
    .blog-split-container {
        margin-top: -60px;
        position: relative;
        z-index: 10;
        margin-bottom: 50px;
    }

    /* Featured card */
    .featured-card {
        background: #ffffff;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid var(--blog-border);
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: row;
        height: 100%;
    }
    @media (max-width: 768px) {
        .featured-card {
            flex-direction: column;
        }
    }
    .featured-img-container {
        flex: 1.1;
        overflow: hidden;
        position: relative;
        min-height: 250px;
    }
    .featured-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }
    .featured-card:hover .featured-img {
        transform: scale(1.05);
    }
    .featured-content {
        flex: 1;
        padding: 35px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .tag-category {
        background: rgba(12, 26, 48, 0.06);
        color: var(--blog-primary);
        padding: 5px 12px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
        align-self: flex-start;
        margin-bottom: 15px;
    }
    .featured-title {
        font-family: 'Outfit', sans-serif;
        font-size: 24px;
        font-weight: 800;
        color: var(--blog-primary);
        line-height: 1.3;
        margin-bottom: 12px;
    }
    .featured-excerpt {
        font-size: 13.5px;
        color: var(--blog-text-muted);
        line-height: 1.6;
        margin-bottom: 20px;
    }
    .featured-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 12.5px;
        color: var(--blog-text-muted);
        border-top: 1px solid var(--blog-border);
        padding-top: 15px;
    }

    /* Trending sidebar */
    .trending-sidebar {
        background: #ffffff;
        border: 1px solid var(--blog-border);
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
        height: 100%;
    }
    .trending-header {
        font-family: 'Outfit', sans-serif;
        font-size: 18px;
        font-weight: 700;
        color: var(--blog-primary);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .trending-item {
        display: flex;
        gap: 15px;
        padding-bottom: 15px;
        margin-bottom: 15px;
        border-bottom: 1px solid var(--blog-border);
    }
    .trending-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
        margin-bottom: 0;
    }
    .trending-num {
        font-family: 'Outfit', sans-serif;
        font-size: 24px;
        font-weight: 800;
        color: rgba(255, 184, 0, 0.25);
        line-height: 1;
    }
    .trending-info h4 {
        font-size: 13.5px;
        font-weight: 700;
        color: var(--blog-primary);
        line-height: 1.4;
        margin-bottom: 5px;
    }
    .trending-info h4 a {
        color: inherit;
        text-decoration: none;
        transition: color 0.2s;
    }
    .trending-info h4 a:hover {
        color: var(--blog-accent);
    }
    .trending-meta {
        font-size: 11px;
        color: var(--blog-text-muted);
        display: flex;
        gap: 10px;
    }

    /* Tabs pill navigators */
    .modern-tabs-container {
        display: flex;
        justify-content: center;
        margin-bottom: 40px;
        overflow-x: auto;
        padding: 5px 0;
    }
    .modern-tabs {
        display: flex;
        background: #ffffff;
        padding: 6px;
        border-radius: 50px;
        border: 1px solid var(--blog-border);
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .modern-tab-btn {
        background: none;
        border: none;
        padding: 10px 24px;
        font-size: 13.5px;
        font-weight: 700;
        border-radius: 50px;
        color: var(--blog-text-muted);
        cursor: pointer;
        transition: all 0.3s;
        white-space: nowrap;
        outline: none;
    }
    .modern-tab-btn:hover {
        color: var(--blog-primary);
    }
    .modern-tab-btn.active {
        background: linear-gradient(135deg, #0c1a30 0%, #18529c 100%);
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(12, 26, 48, 0.2);
    }

    /* Grid of Blog Cards */
    .blog-cards-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }
    @media (max-width: 900px) {
        .blog-cards-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 600px) {
        .blog-cards-grid {
            grid-template-columns: 1fr;
        }
    }
    .modern-blog-card {
        background: #ffffff;
        border: 1px solid var(--blog-border);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01), 0 2px 4px -1px rgba(0,0,0,0.01);
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .modern-blog-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05), 0 10px 10px -5px rgba(0,0,0,0.03);
        border-color: rgba(24, 82, 156, 0.15);
    }
    .card-img-container {
        height: 200px;
        overflow: hidden;
        position: relative;
    }
    .card-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .modern-blog-card:hover .card-img {
        transform: scale(1.06);
    }
    .card-body-content {
        padding: 24px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    .card-title-link {
        font-family: 'Outfit', sans-serif;
        font-size: 17px;
        font-weight: 700;
        color: var(--blog-primary);
        line-height: 1.4;
        margin-bottom: 10px;
        text-decoration: none;
        transition: color 0.2s;
    }
    .modern-blog-card:hover .card-title-link {
        color: #18529c;
    }
    .card-excerpt {
        font-size: 13px;
        color: var(--blog-text-muted);
        line-height: 1.6;
        margin-bottom: 18px;
        flex: 1;
    }
    .card-read-btn {
        color: #FF8A00;
        font-weight: 700;
        font-size: 13.5px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: auto;
        align-self: flex-start;
        transition: gap 0.3s;
    }
    .card-read-btn:hover {
        gap: 10px;
    }
    .card-metadata {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        color: var(--blog-text-muted);
        border-top: 1px solid var(--blog-border);
        padding-top: 15px;
        margin-top: 15px;
    }

    /* Newsletter Subscribe card */
    .blog-subscribe-card {
        background: linear-gradient(135deg, #070d19 0%, #18529c 100%);
        border-radius: 20px;
        padding: 50px 40px;
        color: #ffffff;
        text-align: center;
        margin-top: 70px;
        margin-bottom: 60px;
        box-shadow: 0 15px 30px rgba(12, 26, 48, 0.2);
    }
    .sub-title {
        font-family: 'Outfit', sans-serif;
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 10px;
    }
    .sub-desc {
        color: #94a3b8;
        font-size: 14.5px;
        max-width: 500px;
        margin: 0 auto 25px auto;
    }
    .sub-form {
        max-width: 480px;
        margin: 0 auto;
        display: flex;
        gap: 10px;
    }
    @media (max-width: 480px) {
        .sub-form {
            flex-direction: column;
        }
    }
    .sub-input {
        flex: 1;
        padding: 14px 18px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.15);
        background: rgba(255,255,255,0.08);
        color: #ffffff;
        font-size: 13.5px;
        outline: none;
    }
    .sub-input:focus {
        border-color: var(--blog-accent);
        background: rgba(255,255,255,0.12);
    }
    .sub-btn {
        padding: 14px 28px;
        background: var(--blog-accent);
        color: var(--blog-primary);
        font-weight: 700;
        font-size: 13.5px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .sub-btn:hover {
        transform: translateY(-1px);
    }

    .no-blogs-card {
        text-align: center;
        padding: 60px 20px;
        background: #ffffff;
        border: 1px solid var(--blog-border);
        border-radius: 16px;
        color: var(--blog-text-muted);
        grid-column: 1 / -1;
    }

    /* Pagination styling */
    .modern-pagination-container {
        margin-top: 40px;
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
        gap: 8px;
    }
    .page-link-btn {
        border: 1px solid var(--blog-border);
        background: #ffffff;
        color: var(--blog-primary);
        padding: 8px 16px;
        font-size: 13.5px;
        font-weight: 700;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        outline: none;
    }
    .page-link-btn:hover:not(:disabled) {
        border-color: var(--blog-primary);
        background: var(--blog-bg-light);
    }
    .page-link-btn.active {
        background: linear-gradient(135deg, #0c1a30 0%, #18529c 100%);
        color: #ffffff;
        border-color: var(--blog-primary);
        box-shadow: 0 4px 10px rgba(12, 26, 48, 0.15);
    }
    .page-link-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    /* 🌌 Dynamic Staggered Card Animation */
    @keyframes blogCardFadeInUp {
        from {
            opacity: 0;
            transform: translateY(16px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .blog-card-animate {
        animation: blogCardFadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
</style>

<!-- Blog Hero section -->
<div class="blog-hero-section">
    <div class="container">
        <h1 class="blog-hero-title">Financial Knowledge Hub</h1>
        <p class="blog-hero-subtitle">Expert guides, smart finance tips, and dynamic updates to help you make informed borrowing and investment decisions.</p>
        <div class="blog-search-container">
            <i class="fa fa-search blog-search-icon"></i>
            <input type="text" id="blogSearch" class="blog-search-input" placeholder="Search guides by title, keywords or topic...">
            <i class="fa fa-times-circle" id="clearSearchBtn" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.6); cursor: pointer; font-size: 18px; display: none; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='rgba(255,255,255,0.6)'"></i>
        </div>
    </div>
</div>

<div class="container">
    <!-- Featured & Trending Section -->
    <div class="row g-4 blog-split-container">
        <!-- Featured Article -->
        <div class="col-lg-8">
            <?php if ($featured_post): ?>
                <div class="featured-card">
                    <div class="featured-img-container">
                        <img src="<?php echo htmlspecialchars($featured_post['image_url'] ?? 'assets/images/default-blog.jpg', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($featured_post['title'], ENT_QUOTES, 'UTF-8'); ?>" class="featured-img">
                    </div>
                    <div class="featured-content">
                        <span class="tag-category"><?php echo htmlspecialchars($featured_post['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <h2 class="featured-title"><?php echo htmlspecialchars($featured_post['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p class="featured-excerpt"><?php echo htmlspecialchars($featured_post['excerpt'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        <div class="featured-meta">
                            <span><i class="fa fa-clock" style="color:var(--blog-accent); margin-right: 4px;"></i> <?php echo htmlspecialchars($featured_post['read_time'] ?? '5 Min Read', ENT_QUOTES, 'UTF-8'); ?></span>
                            <span>&bull;</span>
                            <span><i class="fa fa-eye" style="color:var(--blog-accent); margin-right: 4px;"></i> <?php echo number_format((int)($featured_post['views'] ?? 0)); ?> Views</span>
                            <a href="blog/<?php echo htmlspecialchars($featured_post['slug'], ENT_QUOTES, 'UTF-8'); ?>" style="margin-left: auto; text-decoration: none;" class="card-read-btn">Read Article &rarr;</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Trending Sidebar -->
        <div class="col-lg-4">
            <div class="trending-sidebar">
                <div class="trending-header">
                    <i class="fa fa-fire" style="color: #FF8A00; margin-right: 8px; font-size: 19px;"></i> Popular Reads
                </div>
                <?php 
                $count = 1;
                foreach ($trending_blogs as $tb): 
                ?>
                    <div class="trending-item">
                        <span class="trending-num"><?php echo sprintf("%02d", $count++); ?></span>
                        <div class="trending-info">
                            <h4><a href="blog/<?php echo htmlspecialchars($tb['slug'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($tb['title'], ENT_QUOTES, 'UTF-8'); ?></a></h4>
                            <div class="trending-meta">
                                <span><i class="fa fa-eye"></i> <?php echo number_format((int)($tb['views'] ?? 0)); ?> Views</span>
                                <span>&bull;</span>
                                <span style="text-transform: capitalize;"><?php echo htmlspecialchars($tb['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="modern-tabs-container">
        <div class="modern-tabs">
            <button class="modern-tab-btn active" onclick="switchCategoryTab(event, 'personal')"><i class="fa fa-wallet" style="margin-right: 6px;"></i> Personal Loan</button>
            <button class="modern-tab-btn" onclick="switchCategoryTab(event, 'lap')"><i class="fa fa-home" style="margin-right: 6px;"></i> Loan Against Property</button>
            <button class="modern-tab-btn" onclick="switchCategoryTab(event, 'business')"><i class="fa fa-briefcase" style="margin-right: 6px;"></i> Business Loan</button>
            <button class="modern-tab-btn" onclick="switchCategoryTab(event, 'vehicle')"><i class="fa fa-car" style="margin-right: 6px;"></i> Vehicle Loan</button>
            <button class="modern-tab-btn" onclick="switchCategoryTab(event, 'payday')"><i class="fa fa-bolt" style="margin-right: 6px;"></i> PayDay Loan</button>
            <button class="modern-tab-btn" onclick="switchCategoryTab(event, 'edi')"><i class="fa fa-building" style="margin-right: 6px;"></i> EDI Loan</button>
        </div>
    </div>

    <!-- Tab Contents -->
    <?php foreach ($blogs_by_cat as $categoryName => $blogs): ?>
        <div id="tab-<?php echo $categoryName; ?>" class="blog-tab-content-block <?php echo $categoryName === 'personal' ? '' : 'd-none'; ?>">
            <div class="blog-cards-grid" id="grid-<?php echo $categoryName; ?>">
                <?php if (empty($blogs)): ?>
                    <div class="no-blogs-card">
                        <i class="fa-regular fa-folder-open" style="font-size: 40px; color: var(--blog-text-muted); margin-bottom: 12px;"></i>
                        <p>No guides posted in this category yet. Stay tuned!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($blogs as $b): ?>
                        <div class="blog-card-wrapper" data-title="<?php echo htmlspecialchars(strtolower($b['title'] . ' ' . ($b['excerpt'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>">
                            <article class="modern-blog-card">
                                <div class="card-img-container">
                                    <img src="<?php echo htmlspecialchars($b['image_url'] ?? 'assets/images/default-blog.jpg', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($b['title'], ENT_QUOTES, 'UTF-8'); ?>" class="card-img" loading="lazy">
                                </div>
                                <div class="card-body-content">
                                    <span class="tag-category" style="background: rgba(24, 82, 156, 0.05); color: #18529c; margin-bottom: 12px; font-size: 10px;"><?php echo htmlspecialchars($b['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <a href="blog/<?php echo htmlspecialchars($b['slug'], ENT_QUOTES, 'UTF-8'); ?>" class="card-title-link">
                                        <?php echo htmlspecialchars($b['title'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                    <p class="card-excerpt">
                                        <?php echo htmlspecialchars($b['excerpt'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                    <a href="blog/<?php echo htmlspecialchars($b['slug'], ENT_QUOTES, 'UTF-8'); ?>" class="card-read-btn">Read Article &rarr;</a>
                                    <div class="card-metadata">
                                        <span><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($b['read_time'] ?? '5 Min Read', ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span><i class="fa-regular fa-eye"></i> <?php echo number_format((int)($b['views'] ?? 0)); ?> Views</span>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div id="pagination-<?php echo $categoryName; ?>" class="modern-pagination-container"></div>
        </div>
    <?php endforeach; ?>

    <!-- Premium Newsletter Subscribe Card -->
    <div class="blog-subscribe-card">
        <h3 class="sub-title">Stay Ahead on Your Finances</h3>
        <p class="sub-desc">Join our newsletter to receive the latest updates, credit scoring guides, and financial planning tips directly in your inbox.</p>
        <form class="sub-form" onsubmit="event.preventDefault(); Swal.fire('Subscribed!', 'Thank you for joining our financial newsletter.', 'success');">
            <input type="email" class="sub-input" placeholder="Enter your email address" required>
            <button type="submit" class="sub-btn">Subscribe Now</button>
        </form>
    </div>
</div>

<!-- SweetAlert2 for newsletter alert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Toggle active category tabs and manage pagination
    const activeCategoryStates = {};
    const itemsPerPage = 6;

    function initPagination(category) {
        activeCategoryStates[category] = {
            currentPage: 1
        };
        updatePaginationForCategory(category);
    }

    function updatePaginationForCategory(category) {
        const tabEl = document.getElementById('tab-' + category);
        if (!tabEl) return;

        const cards = Array.from(tabEl.querySelectorAll('.blog-card-wrapper'));
        // Filter cards that are visible (not hidden by search query)
        const visibleCards = cards.filter(card => !card.classList.contains('d-none'));

        const state = activeCategoryStates[category] || { currentPage: 1 };
        const totalPages = Math.ceil(visibleCards.length / itemsPerPage);

        // Clamp currentPage
        if (state.currentPage > totalPages) {
            state.currentPage = Math.max(1, totalPages);
        }

        const startIndex = (state.currentPage - 1) * itemsPerPage;
        const endIndex = state.currentPage * itemsPerPage;

        // Toggle visibility of cards based on pagination index
        visibleCards.forEach((card, idx) => {
            if (idx >= startIndex && idx < endIndex) {
                card.style.display = 'block';
                // Trigger staggered entrance animation
                card.classList.remove('blog-card-animate');
                void card.offsetWidth; // Force reflow
                card.classList.add('blog-card-animate');
                const localIdx = idx - startIndex;
                card.style.animationDelay = `${localIdx * 45}ms`;
            } else {
                card.style.display = 'none';
                card.style.animationDelay = '';
            }
        });

        // Hide completely non-visible cards (from search filter)
        cards.forEach(card => {
            if (card.classList.contains('d-none')) {
                card.style.display = 'none';
            }
        });

        // Render Pagination Buttons
        const pagContainer = document.getElementById('pagination-' + category);
        if (!pagContainer) return;

        if (totalPages <= 1) {
            pagContainer.innerHTML = '';
            return;
        }

        let html = '';
        // Prev Button
        html += `<button class="page-link-btn" ${state.currentPage === 1 ? 'disabled' : ''} onclick="changePage('${category}', ${state.currentPage - 1})">Prev</button>`;

        // Page Numbers
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="page-link-btn ${state.currentPage === i ? 'active' : ''}" onclick="changePage('${category}', ${i})">${i}</button>`;
        }

        // Next Button
        html += `<button class="page-link-btn" ${state.currentPage === totalPages ? 'disabled' : ''} onclick="changePage('${category}', ${state.currentPage + 1})">Next</button>`;

        pagContainer.innerHTML = html;
    }

    window.changePage = function(category, pageNum) {
        if (!activeCategoryStates[category]) {
            activeCategoryStates[category] = { currentPage: 1 };
        }
        activeCategoryStates[category].currentPage = pageNum;
        updatePaginationForCategory(category);
        
        // Scroll smoothly to start of tab content grid
        const grid = document.getElementById('grid-' + category);
        if (grid) {
            grid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    window.switchCategoryTab = function(evt, category) {
        // Hide all category blocks
        const blocks = document.querySelectorAll('.blog-tab-content-block');
        blocks.forEach(block => block.classList.add('d-none'));

        // Deactivate all tab buttons
        const buttons = document.querySelectorAll('.modern-tab-btn');
        buttons.forEach(btn => btn.classList.remove('active'));

        // Show active block & activate tab
        const activeBlock = document.getElementById('tab-' + category);
        if (activeBlock) {
            activeBlock.classList.remove('d-none');
        }
        evt.currentTarget.classList.add('active');
        
        // Reset search filter input and clear button
        const searchInput = document.getElementById('blogSearch');
        if (searchInput) searchInput.value = '';
        const clearBtn = document.getElementById('clearSearchBtn');
        if (clearBtn) clearBtn.style.display = 'none';

        // Remove no search results warnings if any
        const noResultsAlerts = document.querySelectorAll('.no-blogs-card');
        noResultsAlerts.forEach(nr => {
            if (nr.id && nr.id.startsWith('no-search-results-')) {
                nr.remove();
            }
        });

        const wrappers = document.querySelectorAll('.blog-card-wrapper');
        wrappers.forEach(w => w.classList.remove('d-none'));

        // Reset page to 1 and update pagination
        if (!activeCategoryStates[category]) {
            activeCategoryStates[category] = { currentPage: 1 };
        }
        activeCategoryStates[category].currentPage = 1;
        updatePaginationForCategory(category);
    };

    // Enhanced search filtering helper
    function performSearch(query) {
        const activeBlock = document.querySelector('.blog-tab-content-block:not(.d-none)');
        if (!activeBlock) return;

        const category = activeBlock.id.replace('tab-', '');
        const cards = activeBlock.querySelectorAll('.blog-card-wrapper');
        let matchedCount = 0;

        cards.forEach(card => {
            const searchString = card.getAttribute('data-title') || '';
            if (searchString.includes(query)) {
                card.classList.remove('d-none');
                matchedCount++;
            } else {
                card.classList.add('d-none');
            }
        });

        // Dynamic creation/display of No Results Alert
        const grid = document.getElementById('grid-' + category);
        let noResults = document.getElementById('no-search-results-' + category);
        
        if (matchedCount === 0) {
            if (!noResults && grid) {
                noResults = document.createElement('div');
                noResults.id = 'no-search-results-' + category;
                noResults.className = 'no-blogs-card';
                noResults.style.textAlign = 'center';
                noResults.style.width = '100%';
                noResults.style.gridColumn = '1 / -1';
                noResults.style.padding = '40px 20px';
                noResults.innerHTML = `
                    <i class="bi bi-search" style="font-size: 40px; color: var(--blog-text-muted); margin-bottom: 12px; display: block;"></i>
                    <h5 style="color: #0c1a30; font-weight: 700; margin-bottom: 6px;">No Results Found</h5>
                    <p style="color: #64748b; font-size: 14px; margin-bottom: 15px;">We couldn't find any articles matching "${query}".</p>
                    <button class="btn" style="background: #18529c; color: #ffffff; font-weight: 600; padding: 8px 20px; border-radius: 8px; border:none;" onclick="resetCategorySearch('${category}')">Reset Search</button>
                `;
                grid.appendChild(noResults);
            } else if (noResults) {
                noResults.style.display = 'block';
                noResults.querySelector('p').textContent = `We couldn't find any articles matching "${query}".`;
            }
        } else {
            if (noResults) {
                noResults.style.display = 'none';
            }
        }

        // Reset page to 1 and recalculate pagination
        if (activeCategoryStates[category]) {
            activeCategoryStates[category].currentPage = 1;
        }
        updatePaginationForCategory(category);
    }

    // Dynamic search filtering in the active tab
    const blogSearch = document.getElementById('blogSearch');
    const clearSearchBtn = document.getElementById('clearSearchBtn');

    if (blogSearch) {
        blogSearch.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            if (clearSearchBtn) {
                clearSearchBtn.style.display = query.length > 0 ? 'block' : 'none';
            }
            performSearch(query);
        });
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            if (blogSearch) {
                blogSearch.value = '';
                this.style.display = 'none';
                performSearch('');
            }
        });
    }

    // Global reset search function
    window.resetCategorySearch = function(category) {
        if (blogSearch) {
            blogSearch.value = '';
        }
        if (clearSearchBtn) {
            clearSearchBtn.style.display = 'none';
        }
        performSearch('');
    };

    // Initialize pagination for all categories on load
    document.addEventListener('DOMContentLoaded', () => {
        ['personal', 'lap', 'business', 'vehicle', 'payday', 'edi'].forEach(cat => {
            initPagination(cat);
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
