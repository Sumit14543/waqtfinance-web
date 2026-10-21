<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';

$slug = trim((string) ($_GET['slug'] ?? ''));

if ($slug === '') {
    header("Location: blog.php");
    exit;
}

$conn = db();
$stmt = $conn->prepare("SELECT * FROM blogs WHERE slug = ? AND status = 'published' LIMIT 1");
$stmt->bind_param('s', $slug);
$stmt->execute();
$blog = $stmt->get_result()->fetch_assoc();

if ($blog) {
    $conn->query("UPDATE blogs SET views = views + 1 WHERE id = " . (int)$blog['id']);
    $blog['views'] = (int)($blog['views'] ?? 0) + 1;
}

if (!$blog) {
    // Return 404 response
    http_response_code(404);
    $page_title = "Article Not Found | Waqt Finance";
    $meta_description = "The requested financial article could not be found.";
    $path_prefix = '../';
    include 'includes/header.php';
    include 'includes/main-header.php';
    ?>
    <div style="text-align: center; padding: 100px 20px; min-height: 50vh; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #080c16; color: #ffffff;">
        <i class="fa-solid fa-file-circle-exclamation" style="font-size: 64px; color: #FFB800; margin-bottom: 20px;"></i>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 32px; font-weight: 700; margin-bottom: 10px;">Article Not Found</h1>
        <p style="color: #9ca3af; max-width: 500px; line-height: 1.6; margin-bottom: 25px;">We couldn't find the article you were looking for. It may have been moved, renamed, or deleted.</p>
        <a href="../blog" style="padding: 12px 24px; background: linear-gradient(135deg, #FFB800, #FF8A00); color: #080c16; font-weight: 700; border-radius: 8px; text-decoration: none; transition: transform 0.3s;">Browse All Articles</a>
    </div>
    <?php
    include 'includes/footer.php';
    exit;
}

// Map SEO details
$page_title = !empty($blog['meta_title']) ? $blog['meta_title'] : ($blog['title'] . " | Waqt Finance Blog");
$meta_description = !empty($blog['meta_description']) ? $blog['meta_description'] : ($blog['excerpt'] ?? substr(strip_tags($blog['content']), 0, 160));
$meta_keywords = !empty($blog['meta_keywords']) ? $blog['meta_keywords'] : "waqt finance blog, finance tips, " . $blog['category'] . " loan";
$canonical_url = "https://waqtfinance.com/blog/" . $blog['slug'];
$meta_image = "https://waqtfinance.com/" . ltrim((string)$blog['image_url'], '/');

// Structured Data Schema for Blog Posting
ob_start();
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "<?php echo htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8'); ?>",
  "image": "https://www.waqtfinance.com/<?php echo htmlspecialchars($blog['image_url'], ENT_QUOTES, 'UTF-8'); ?>",
  "genre": "<?php echo htmlspecialchars($blog['category'], ENT_QUOTES, 'UTF-8'); ?>",
  "keywords": "<?php echo htmlspecialchars($meta_keywords, ENT_QUOTES, 'UTF-8'); ?>",
  "wordcount": "<?php echo str_word_count(strip_tags($blog['content'])); ?>",
  "publisher": {
    "@type": "Organization",
    "name": "Waqt Finance",
    "logo": {
      "@type": "ImageObject",
      "url": "https://www.waqtfinance.com/assets/images/logo.png"
    }
  },
  "datePublished": "<?php echo date('c', strtotime($blog['created_at'])); ?>",
  "dateModified": "<?php echo date('c', strtotime($blog['updated_at'])); ?>",
  "description": "<?php echo htmlspecialchars($meta_description, ENT_QUOTES, 'UTF-8'); ?>",
  "articleBody": "<?php echo htmlspecialchars(strip_tags($blog['content']), ENT_QUOTES, 'UTF-8'); ?>",
  "author": {
    "@type": "Person",
    "name": "<?php echo htmlspecialchars($blog['author_name'] ?? 'Waqt Finance Team', ENT_QUOTES, 'UTF-8'); ?>",
    "jobTitle": "<?php echo htmlspecialchars($blog['author_designation'] ?? 'Financial Writer', ENT_QUOTES, 'UTF-8'); ?>"
  }
  <?php if (!empty($blog['reviewed_by'])): ?>,
  "editor": {
    "@type": "Person",
    "name": "<?php echo htmlspecialchars($blog['reviewed_by'], ENT_QUOTES, 'UTF-8'); ?>"
  }
  <?php endif; ?>
}
</script>
<?php
$blog_faqs = json_decode((string) ($blog['faqs'] ?? '[]'), true) ?? [];
if (!empty($blog_faqs)):
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    <?php
    $faq_entities = [];
    foreach ($blog_faqs as $faq) {
        if (!empty($faq['q']) && !empty($faq['a'])) {
            $faq_entities[] = [
                '@type' => 'Question',
                'name' => $faq['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['a']
                ]
            ];
        }
    }
    echo substr(json_encode($faq_entities, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), 1, -1);
    ?>
  ]
}
</script>
<?php
endif;
$extra_head = ob_get_clean();

// Use path prefix to resolve assets correctly since path looks like /blog-content/<slug>
$path_prefix = '../';
include 'includes/header.php';
include 'includes/main-header.php';
?>

<div id="readingProgressBar"></div>

<style>
    /* Premium Detail Page Overrides */
    #readingProgressBar {
        position: fixed;
        top: 0;
        left: 0;
        height: 4px;
        background: linear-gradient(90deg, #FFB800, #FF8A00);
        width: 0%;
        z-index: 99999;
        transition: width 0.1s;
    }
    
    body {
        background-color: #ffffff !important;
    }
    
    .blog-wrapper-x92 {
        background-color: #ffffff !important;
        padding-top: 40px !important;
        padding-bottom: 60px !important;
    }
    
    .blog-main-x92 {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        color: #1e293b !important;
        padding: 0 !important;
        border-radius: 0 !important;
    }
    
    .blog-main-x92 h1, 
    .blog-main-x92 h2, 
    .blog-main-x92 h3, 
    .blog-main-x92 h4 {
        color: #0c1a30 !important;
        font-family: 'Inter', sans-serif !important;
        font-weight: 800 !important;
    }
    
    .blog-main-x92 h2 {
        border-bottom: 2px solid #f1f5f9 !important;
        padding-bottom: 12px !important;
        margin-top: 40px !important;
        font-size: 24px !important;
    }
    
    .blog-main-x92 p, 
    .blog-main-x92 li {
        color: #334155 !important;
        font-size: 16.5px !important;
        line-height: 1.85 !important;
        margin-bottom: 24px !important;
    }

    .blog-sidebar-x92 {
        position: sticky;
        top: 150px;
        align-self: flex-start;
    }
    
    .widget-x92 {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04) !important;
        color: #1e293b !important;
        border-radius: 20px !important;
        padding: 25px !important;
        margin-bottom: 25px !important;
    }
    
    .widget-x92 h3 {
        color: #0c1a30 !important;
        font-family: 'Outfit', sans-serif !important;
        border-bottom: 2px solid #f1f5f9 !important;
        padding-bottom: 10px !important;
        font-weight: 700;
        font-size: 15px !important;
    }
    
    .category-list-x92 li {
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 8px 0 !important;
    }
    
    .category-list-x92 li a {
        color: #475569 !important;
        font-weight: 600;
        font-size: 13.5px !important;
        text-decoration: none;
        display: block;
        transition: all 0.3s;
    }
    
    .category-list-x92 li a:hover {
        color: #18529c !important;
        padding-left: 6px;
    }

    /* E-E-A-T metadata row styles inside content box */
    .eeat-meta-row {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 30px;
    }

    /* Table of Contents sidebar widget active line */
    #tocList li {
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 6px 0 !important;
    }
    #tocList li a {
        color: #475569 !important;
        font-weight: 500;
        font-size: 13.5px !important;
        text-decoration: none;
        display: block;
        transition: all 0.2s;
        border-left: 2px solid transparent;
        padding-left: 0px;
    }
    #tocList li a:hover {
        color: #18529c !important;
        border-left: 2px solid #FFB800;
        padding-left: 8px;
    }

    /* Rich content styles inside content body */
    .blog-content-rich blockquote {
        border-left: 4px solid #FFB800 !important;
        background: #f8fafc !important;
        padding: 15px 20px !important;
        margin: 25px 0 !important;
        border-radius: 0 8px 8px 0 !important;
        font-style: italic !important;
        color: #475569 !important;
    }
    
    .blog-content-rich ul {
        list-style: none !important;
        padding-left: 10px !important;
    }
    
    .blog-content-rich ul li {
        position: relative !important;
        padding-left: 24px !important;
        margin-bottom: 10px !important;
    }
    
    .blog-content-rich ul li::before {
        content: "\f00c" !important;
        font-family: "Font Awesome 6 Free" !important;
        font-weight: 900 !important;
        position: absolute !important;
        left: 0 !important;
        top: 2px !important;
        color: #10b981 !important;
        font-size: 12px !important;
    }
    
    .blog-content-rich table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin: 25px 0 !important;
        font-size: 14px !important;
    }
    
    .blog-content-rich th {
        background: #f1f5f9 !important;
        color: #0c1a30 !important;
        font-weight: 700 !important;
        padding: 12px 16px !important;
        border: 1px solid #e2e8f0 !important;
    }
    
    .blog-content-rich td {
        padding: 12px 16px !important;
        border: 1px solid #e2e8f0 !important;
        color: #334155 !important;
    }
</style>

<div class="blog-wrapper-x92">

    <!-- Main Content -->
    <div class="blog-main-x92">

        <!-- Breadcrumbs -->
        <nav class="blog-breadcrumbs" style="margin-bottom: 25px; font-size: 13.5px; color: #64748b;">
            <a href="../" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#18529c'" onmouseout="this.style.color='#64748b'">Home</a>
            <span style="margin: 0 8px; color: #cbd5e1;">/</span>
            <a href="../blog" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#18529c'" onmouseout="this.style.color='#64748b'">Blog</a>
            <span style="margin: 0 8px; color: #cbd5e1;">/</span>
            <span style="color: #0c1a30; font-weight: 500; text-transform: capitalize;"><?php echo htmlspecialchars($blog['category'], ENT_QUOTES, 'UTF-8'); ?> Loan</span>
        </nav>

        <!-- Category Tag -->
        <div style="margin-bottom: 12px;">
            <span style="background: rgba(24, 82, 156, 0.06); padding: 6px 14px; border-radius: 8px; font-weight: 700; color: #18529c; text-transform: uppercase; font-size: 10.5px; letter-spacing: 0.5px;">
                <?php echo htmlspecialchars($blog['category'], ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </div>

        <!-- H1 Blog Title -->
        <h1 style="font-size: 38px !important; line-height: 1.3 !important; font-weight: 800 !important; margin-bottom: 24px !important; color: #0c1a30; letter-spacing: -0.5px; font-family: 'Inter', sans-serif !important;">
            <?php echo htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8'); ?>
        </h1>

        <!-- E-E-A-T Credential Row -->
        <div class="eeat-meta-row" style="display: flex; flex-wrap: wrap; gap: 20px; font-size: 13.5px; color: #475569; align-items: center; background: #ffffff; border: 1px solid #f1f5f9; border-left: 4px solid #FFB800; border-radius: 12px; padding: 18px 24px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.015);">
            <!-- Written By -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 34px; height: 34px; background: linear-gradient(135deg, #18529c 0%, #0c1a30 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ffffff; font-weight: 700; font-size: 12px; box-shadow: 0 4px 10px rgba(24, 82, 156, 0.15);">
                    <?php echo strtoupper(substr($blog['author_name'] ?? 'W', 0, 1)); ?>
                </div>
                <div style="line-height: 1.35;">
                    <span style="font-size: 9.5px; color: #94a3b8; display: block; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700;">Written By</span>
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <strong style="color: #0c1a30; font-weight: 700; font-size: 14.5px;"><?php echo htmlspecialchars($blog['author_name'] ?? 'Waqt Finance Team', ENT_QUOTES, 'UTF-8'); ?></strong>
                        <i class="fa-solid fa-circle-check" style="color: #FFB800; font-size: 12px;" title="Verified Author"></i>
                    </div>
                    <?php if (!empty($blog['author_designation'])): ?>
                        <span style="font-size: 11.5px; color: #64748b; font-weight: 500;"><?php echo htmlspecialchars($blog['author_designation'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reviewed By -->
            <?php if (!empty($blog['reviewed_by'])): ?>
                <div style="display: flex; align-items: center; gap: 10px; border-left: 1px solid #f1f5f9; padding-left: 20px;">
                    <div style="width: 34px; height: 34px; background: rgba(16, 185, 129, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 14px;">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <div style="line-height: 1.35;">
                        <span style="font-size: 9.5px; color: #94a3b8; display: block; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700;">Reviewed By</span>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <strong style="color: #0c1a30; font-weight: 700; font-size: 14.5px;"><?php echo htmlspecialchars($blog['reviewed_by'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span style="font-size: 9px; background: #d1fae5; color: #065f46; padding: 2px 6px; border-radius: 4px; font-weight: 800; margin-left: 4px; display: inline-flex; align-items: center; gap: 2px;"><i class="fa-solid fa-circle-check" style="font-size: 8px;"></i> Verified Expert</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Read Time & Date -->
            <div style="margin-left: auto; display: flex; gap: 12px; align-items: center;">
                <span style="background: #f8fafc; border: 1px solid #f1f5f9; padding: 6px 12px; border-radius: 20px; font-size: 12px; color: #475569; font-weight: 600;"><i class="fa-solid fa-clock" style="margin-right: 5px; color: #FF8A00;"></i> <?php echo htmlspecialchars($blog['read_time'] ?? '5 Min Read', ENT_QUOTES, 'UTF-8'); ?></span>
                <span style="background: #f8fafc; border: 1px solid #f1f5f9; padding: 6px 12px; border-radius: 20px; font-size: 12px; color: #475569; font-weight: 600;"><i class="fa-solid fa-calendar-alt" style="margin-right: 5px; color: #FF8A00;"></i> <?php echo date('d M Y', strtotime($blog['created_at'])); ?></span>
                <span style="background: #f8fafc; border: 1px solid #f1f5f9; padding: 6px 12px; border-radius: 20px; font-size: 12px; color: #475569; font-weight: 600;"><i class="fa-solid fa-eye" style="margin-right: 5px; color: #FF8A00;"></i> <?php echo number_format((int)($blog['views'] ?? 0)); ?> Views</span>
            </div>
        </div>

        <!-- Featured Image -->
        <?php if (!empty($blog['image_url'])): ?>
            <div style="margin-bottom: 30px; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.03); border: 1px solid #f1f5f9; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.01)';" onmouseout="this.style.transform='none';">
                <img src="../<?php echo htmlspecialchars($blog['image_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8'); ?>" class="feature-img-x92" style="width: 100%; max-height: 420px; object-fit: cover; display: block;">
            </div>
        <?php endif; ?>

        <!-- Article Content -->
        <div class="blog-content-rich">
            <?php echo waqt_sanitize_rich_html((string) $blog['content']); ?>
        </div>

        <!-- Contextual Call-to-Action (CTA) Banner -->
        <?php if (!empty($blog['cta_text']) && !empty($blog['cta_link'])): ?>
            <div class="blog-cta-box" style="background: linear-gradient(135deg, rgba(24, 82, 156, 0.15), rgba(8, 12, 22, 0.5)); border: 1px solid rgba(24, 82, 156, 0.3); border-radius: 16px; padding: 25px; margin-top: 40px; display: flex; flex-direction: row; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);">
                <div style="flex: 1; min-width: 250px;">
                    <span style="font-size: 11px; text-transform: uppercase; font-weight: 800; color: #FFB800; letter-spacing: 1px; display: block; margin-bottom: 6px;"><i class="fa-solid fa-gift"></i> Exclusive Finance Offer</span>
                    <h3 style="color: #ffffff; font-size: 20px; font-weight: 700; margin: 0 0 10px 0; font-family: 'Outfit', sans-serif; border: none; padding: 0;"><?php echo htmlspecialchars($blog['cta_text'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 12px; font-size: 13px; color: #94a3b8;">
                        <span><i class="fa-solid fa-circle-check" style="color: #10b981; margin-right: 4px;"></i> 100% Paperless</span>
                        <span>&bull;</span>
                        <span><i class="fa-solid fa-circle-check" style="color: #10b981; margin-right: 4px;"></i> Quick Disbursal</span>
                        <span>&bull;</span>
                        <span><i class="fa-solid fa-circle-check" style="color: #10b981; margin-right: 4px;"></i> Secure Process</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center;">
                    <a href="<?php echo htmlspecialchars($blog['cta_link'], ENT_QUOTES, 'UTF-8'); ?>" style="text-decoration: none; padding: 12px 26px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #FFB800, #FF8A00); color: #0c1a30; border-radius: 8px; border: none; font-size: 14px; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 15px rgba(255, 184, 0, 0.3);" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='none';">
                        Apply Online <i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Blog FAQs Section -->
        <?php if (!empty($blog_faqs)): ?>
            <div class="blog-faq-section" style="margin-top: 50px;">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 24px; font-weight: 700; color: #0c1a30; margin-bottom: 20px; border-bottom: 2px solid #cbd5e1; padding-bottom: 10px;">Frequently Asked Questions</h2>
                <div class="blog-faq-list" style="display: flex; flex-direction: column; gap: 15px;">
                    <?php foreach ($blog_faqs as $idx => $faq): ?>
                        <div class="blog-faq-item" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; transition: all 0.3s;">
                            <button class="blog-faq-toggle" onclick="toggleBlogFaq(this)" style="width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; background: transparent; border: none; outline: none; cursor: pointer; text-align: left; font-family: 'Inter', sans-serif; font-size: 15px; font-weight: 700; color: #0c1a30; gap: 15px;">
                                <span><?php echo htmlspecialchars($faq['q'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <i class="fa-solid fa-plus" style="font-size: 14px; color: #FF8A00; transition: transform 0.3s;"></i>
                            </button>
                            <div class="blog-faq-answer" style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; color: #475569; font-size: 14.5px; line-height: 1.6;">
                                <div style="padding: 0 24px 18px 24px;">
                                    <?php echo htmlspecialchars($faq['a'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <script>
                function toggleBlogFaq(btn) {
                    const item = btn.closest('.blog-faq-item');
                    const answer = item.querySelector('.blog-faq-answer');
                    const icon = btn.querySelector('i');
                    const allItems = document.querySelectorAll('.blog-faq-item');
                    
                    // Close others
                    allItems.forEach(i => {
                        if (i !== item) {
                            i.querySelector('.blog-faq-answer').style.maxHeight = '0px';
                            i.querySelector('i').className = 'fa-solid fa-plus';
                            i.style.background = '#f8fafc';
                        }
                    });

                    if (answer.style.maxHeight && answer.style.maxHeight !== '0px') {
                        answer.style.maxHeight = '0px';
                        icon.className = 'fa-solid fa-plus';
                        item.style.background = '#f8fafc';
                    } else {
                        answer.style.maxHeight = answer.scrollHeight + "px";
                        icon.className = 'fa-solid fa-minus';
                        item.style.background = '#ffffff';
                    }
                }
            </script>
        <?php endif; ?>

    </div>

    <!-- Sidebar -->
    <div class="blog-sidebar-x92">

        <div class="widget-x92 search-box-x92">
            <h3>Search</h3>
            <form action="../blog" method="GET" style="position:relative;">
                <input type="text" name="search" placeholder="Search guides..." style="width: 100%; padding: 10px 14px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; color: #0c1a30; outline: none;">
            </form>
        </div>

        <!-- Table of Contents Widget (Populated dynamically) -->
        <div class="widget-x92 TOC-widget" id="tocWidget" style="display:none;">
            <h3>Table of Contents</h3>
            <ul class="category-list-x92" id="tocList">
            </ul>
        </div>

        <!-- Share Widget -->
        <div class="widget-x92">
            <h3>Share Guide</h3>
            <div style="display: flex; gap: 12px; margin-top: 15px;">
                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($blog['title'] . ' ' . $canonical_url); ?>" target="_blank" rel="noopener noreferrer" style="width: 40px; height: 40px; background: rgba(37, 211, 102, 0.1); color: #25D366; border: 1px solid rgba(37, 211, 102, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; transition: all 0.3s;" onmouseover="this.style.background='#25D366'; this.style.color='#ffffff'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='rgba(37, 211, 102, 0.1)'; this.style.color='#25D366'; this.style.transform='none';"><i class="fab fa-whatsapp"></i></a>
                
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($canonical_url); ?>" target="_blank" rel="noopener noreferrer" style="width: 40px; height: 40px; background: rgba(10, 102, 194, 0.1); color: #0A66C2; border: 1px solid rgba(10, 102, 194, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; transition: all 0.3s;" onmouseover="this.style.background='#0A66C2'; this.style.color='#ffffff'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='rgba(10, 102, 194, 0.1)'; this.style.color='#0A66C2'; this.style.transform='none';"><i class="fab fa-linkedin-in"></i></a>
                
                <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($blog['title']); ?>&url=<?php echo urlencode($canonical_url); ?>" target="_blank" rel="noopener noreferrer" style="width: 40px; height: 40px; background: rgba(15, 23, 42, 0.05); color: #0f172a; border: 1px solid rgba(15, 23, 42, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; transition: all 0.3s;" onmouseover="this.style.background='#0f172a'; this.style.color='#ffffff'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='rgba(15, 23, 42, 0.05)'; this.style.color='#0f172a'; this.style.transform='none';"><i class="fa-brands fa-x-twitter"></i></a>
                
                <button onclick="navigator.clipboard.writeText(<?php echo json_encode($canonical_url, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>); Swal.fire({title: 'Link Copied!', text: 'Article link copied to clipboard.', icon: 'success', timer: 1500, showConfirmButton: false});" style="width: 40px; height: 40px; background: rgba(255, 184, 0, 0.1); color: #FFB800; border: 1px solid rgba(255, 184, 0, 0.15); border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 15px; transition: all 0.3s; outline: none;" onmouseover="this.style.background='#FFB800'; this.style.color='#0c1a30'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='rgba(255, 184, 0, 0.1)'; this.style.color='#FFB800'; this.style.transform='none';"><i class="fa-regular fa-copy"></i></button>
            </div>
        </div>

        <!-- Sidebar CTA Card -->
        <?php
        $cat = $blog['category'] ?? 'personal';
        $cta_title = "Need a Personal Loan?";
        $cta_desc = "Get instant disbursal with zero collateral. Apply online in 5 minutes!";
        $apply_link = "../personal-loan-apply";

        if ($cat === 'business') {
            $cta_title = "Grow Your Business";
            $cta_desc = "MSME business loans up to ₹50 Lakhs with minimum documentation.";
            $apply_link = "../business-loan-apply";
        } else if ($cat === 'lap') {
            $cta_title = "Loan Against Property";
            $cta_desc = "Unlock the value of your property with low interest rate mortgages.";
            $apply_link = "../loan-against-property-apply";
        } else if ($cat === 'vehicle') {
            $cta_title = "Dream Vehicle Loan";
            $cta_desc = "New and used car loans starting at 7.49% per annum.";
            $apply_link = "../vehicle-loan-apply";
        } else if ($cat === 'payday') {
            $cta_title = "Salary Advance Loan";
            $cta_desc = "Short on cash before payday? Get instant credit to cover emergencies.";
            $apply_link = "../payday-apply";
        } else if ($cat === 'edi') {
            $cta_title = "Start Your Business";
            $cta_desc = "Apply for government-supported EDI Loans to launch your startup.";
            $apply_link = "../edi-loan-apply";
        }
        ?>
        <div class="widget-x92" style="background: linear-gradient(135deg, #070d19 0%, #18529c 100%) !important; color: #ffffff !important; text-align: center; border: none !important; margin-bottom: 25px;">
            <span style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: #FFB800; letter-spacing: 1px; display: block; margin-bottom: 6px;"><i class="fa-solid fa-bolt"></i> Instant Decision</span>
            <h3 style="color: #ffffff !important; border-bottom: none !important; font-size: 18px !important; margin-bottom: 8px !important; padding: 0 !important; font-weight: 700;"><?php echo $cta_title; ?></h3>
            <p style="color: #cbd5e1 !important; font-size: 13px !important; line-height: 1.5 !important; margin-bottom: 18px !important;"><?php echo $cta_desc; ?></p>
            <a href="<?php echo $apply_link; ?>" style="text-decoration: none; display: block; padding: 12px 20px; background: #FFB800; color: #0c1a30; font-weight: 700; border-radius: 8px; font-size: 13px; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">Apply Online Now</a>
        </div>

        <div class="widget-x92">
            <h3>Loan Categories</h3>
            <ul class="category-list-x92">
                <li><a href="../blog">Personal Loan</a></li>
                <li><a href="../blog">Business Loan</a></li>
                <li><a href="../blog">Loan Against Property</a></li>
                <li><a href="../blog">Vehicle Loan</a></li>
                <li><a href="../blog">PayDay Loan</a></li>
                <li><a href="../blog">EDI Loan</a></li>
            </ul>
        </div>

    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Dynamic Table of Contents Generator
    const headings = document.querySelectorAll(".blog-content-rich h2");
    const tocWidget = document.getElementById("tocWidget");
    const tocList = document.getElementById("tocList");

    if (headings.length > 0 && tocWidget && tocList) {
        tocWidget.style.display = "block";
        headings.forEach((heading, index) => {
            // Generate id if not present
            if (!heading.id) {
                heading.id = "heading-" + index;
            }
            
            const li = document.createElement("li");
            const a = document.createElement("a");
            a.href = "#" + heading.id;
            a.textContent = heading.textContent;
            
            // smooth scroll helper
            a.addEventListener("click", function(e) {
                e.preventDefault();
                const targetEl = document.getElementById(heading.id);
                if (targetEl) {
                    targetEl.scrollIntoView({
                        behavior: "smooth",
                        block: "start"
                    });
                }
            });

            li.appendChild(a);
            tocList.appendChild(li);
        });
    }

    // Reading Progress Bar handler
    window.addEventListener('scroll', function() {
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = height > 0 ? (winScroll / height) * 100 : 0;
        const bar = document.getElementById("readingProgressBar");
        if (bar) {
            bar.style.width = scrolled + "%";
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
