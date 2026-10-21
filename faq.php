<?php
$faq_items = isset($faq_items) && is_array($faq_items) ? $faq_items : [];
$faq_categories = isset($faq_categories) && is_array($faq_categories) ? $faq_categories : [];
?>
<main class="wf-faq-page" id="main-content">
    <section class="wf-faq-hero" aria-labelledby="faq-page-title">
        <div class="container">
            <nav class="wf-faq-breadcrumb" aria-label="Breadcrumb">
                <a href="./">Home</a><span aria-hidden="true">/</span><span aria-current="page">Loan FAQs</span>
            </nav>
            <div class="wf-faq-hero-copy">
                <span class="wf-faq-eyebrow"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Clear answers before you apply</span>
                <h1 id="faq-page-title">How can we help?</h1>
                <p>Understand eligibility, documents, rates, fees, repayment and application safety.</p>
            </div>
            <div class="wf-faq-search-wrap">
                <label for="faqSearch">Search frequently asked questions</label>
                <div class="wf-faq-search">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input id="faqSearch" type="search" placeholder="Try “processing fee”, “KFS” or “credit score”" autocomplete="off">
                    <button id="faqSearchClear" type="button" aria-label="Clear FAQ search" hidden><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>
            </div>
        </div>
    </section>

    <section class="wf-faq-content-section" aria-labelledby="all-faq-title">
        <div class="container">
            <div class="wf-faq-layout">
                <aside class="wf-faq-aside" aria-label="FAQ categories">
                    <h2>Browse by topic</h2>
                    <div class="wf-faq-filters" role="group" aria-label="Filter FAQs by topic">
                        <button type="button" class="active" data-faq-category="all" aria-pressed="true">All questions <span><?php echo count($faq_items); ?></span></button>
                        <?php foreach ($faq_categories as $category_key => $category_label): ?>
                            <button type="button" data-faq-category="<?php echo htmlspecialchars($category_key, ENT_QUOTES, 'UTF-8'); ?>" aria-pressed="false">
                                <?php echo htmlspecialchars($category_label, ENT_QUOTES, 'UTF-8'); ?>
                                <span><?php echo count(array_filter($faq_items, static fn(array $item): bool => $item['category'] === $category_key)); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="wf-faq-trust-card">
                        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                        <strong>Stay safe</strong>
                        <p>Never share an OTP, PIN, CVV or banking password.</p>
                    </div>
                </aside>

                <div class="wf-faq-main">
                    <div class="wf-faq-section-head">
                        <div><span>Support centre</span><h2 id="all-faq-title">Frequently asked questions</h2></div>
                        <p id="faqResultCount" aria-live="polite"><?php echo count($faq_items); ?> questions</p>
                    </div>

                    <div class="wf-faq-accordion" id="faqAccordion">
                        <?php foreach ($faq_items as $index => $item):
                            $number = $index + 1;
                            $panel_id = 'faq-panel-' . $number;
                            $button_id = 'faq-button-' . $number;
                            $search_text = strtolower($item['question'] . ' ' . $item['answer']);
                        ?>
                            <article class="wf-faq-card" id="faq-<?php echo $number; ?>" data-category="<?php echo htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8'); ?>" data-search="<?php echo htmlspecialchars($search_text, ENT_QUOTES, 'UTF-8'); ?>">
                                <h3>
                                    <button type="button" id="<?php echo $button_id; ?>" aria-expanded="false" aria-controls="<?php echo $panel_id; ?>">
                                        <span class="wf-faq-question-text"><?php echo htmlspecialchars($item['question'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="wf-faq-icon" aria-hidden="true"><i class="fa-solid fa-plus"></i></span>
                                    </button>
                                </h3>
                                <div class="wf-faq-panel" id="<?php echo $panel_id; ?>" aria-labelledby="<?php echo $button_id; ?>" style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out;">
                                    <div class="wf-faq-answer">
                                        <p><?php echo htmlspecialchars($item['answer'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <?php if (!empty($item['links'])): ?><div class="wf-faq-answer-links">
                                            <?php foreach ($item['links'] as $link): ?><a href="<?php echo htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a><?php endforeach; ?>
                                        </div><?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="wf-faq-empty" id="faqEmpty" hidden>
                        <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
                        <h3>No matching answer found</h3><p>Try a shorter search or contact our support team.</p>
                    </div>

                    <div class="wf-faq-disclosure">
                        <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                        <div><strong>Review the total cost before accepting a loan</strong><p>Your sanction terms and Key Facts Statement contain the applicable APR, repayment schedule, fees and charges for your offer.</p></div>
                        <a href="interest-rate-policy">Interest rate policy <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>

            <section class="wf-faq-contact" aria-labelledby="faq-contact-title">
                <div><span>Still need help?</span><h2 id="faq-contact-title">Talk to our support team</h2><p>Use verified contact details and keep your application or loan reference ready.</p></div>
                <div class="wf-faq-contact-actions"><a class="wf-faq-btn secondary" href="tel:9217086610"><i class="fa-solid fa-phone" aria-hidden="true"></i> Call support</a><a class="wf-faq-btn primary" href="contact-us">Contact us <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
            </section>
            <p class="wf-faq-reviewed">Information last reviewed: July 2026. Individual loan terms are governed by the final sanction documents, KFS and loan agreement.</p>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('faqAccordion');
    if (!root) return;
    const page = document.querySelector('.wf-faq-page');
    const siteHeader = document.querySelector('header');
    const cards = Array.from(root.querySelectorAll('.wf-faq-card'));
    const filters = Array.from(document.querySelectorAll('[data-faq-category]'));
    const search = document.getElementById('faqSearch');
    const clear = document.getElementById('faqSearchClear');
    const count = document.getElementById('faqResultCount');
    const empty = document.getElementById('faqEmpty');
    let category = 'all';

    function updateStickyOffset() {
        if (!page || !siteHeader || window.innerWidth <= 991) return;
        const headerStyle = getComputedStyle(siteHeader);
        const isOverlaying = siteHeader.classList.contains('sticky') || headerStyle.position === 'fixed';
        const offset = isOverlaying ? Math.ceil(siteHeader.getBoundingClientRect().height) + 16 : 20;
        page.style.setProperty('--faq-sticky-offset', offset + 'px');
    }

    let stickyFrame = 0;
    function scheduleStickyOffset() {
        if (stickyFrame) return;
        stickyFrame = requestAnimationFrame(function () { stickyFrame = 0; updateStickyOffset(); });
    }
    updateStickyOffset();
    window.addEventListener('scroll', scheduleStickyOffset, { passive: true });
    window.addEventListener('resize', scheduleStickyOffset);
    if ('ResizeObserver' in window && siteHeader) new ResizeObserver(scheduleStickyOffset).observe(siteHeader);

    function closeCard(card) {
        const button = card.querySelector('button[aria-expanded]');
        const panel = card.querySelector('.wf-faq-panel');
        button.setAttribute('aria-expanded', 'false');
        panel.style.maxHeight = "0px";
        card.classList.remove('open');
    }

    root.addEventListener('click', function (event) {
        const button = event.target.closest('button[aria-expanded]');
        if (!button || !root.contains(button)) return;
        const card = button.closest('.wf-faq-card');
        const panel = card.querySelector('.wf-faq-panel');
        const willOpen = button.getAttribute('aria-expanded') !== 'true';
        
        cards.forEach(closeCard);
        
        if (willOpen) {
            button.setAttribute('aria-expanded', 'true');
            panel.style.maxHeight = panel.scrollHeight + "px";
            card.classList.add('open');
            history.replaceState(null, '', '#' + card.id);
        }
    });

    function applyFilters() {
        const query = search.value.trim().toLowerCase();
        let visible = 0;
        cards.forEach(function (card) {
            const matchesCategory = category === 'all' || card.dataset.category === category;
            const matchesSearch = !query || card.dataset.search.includes(query);
            card.hidden = !(matchesCategory && matchesSearch);
            if (!card.hidden) visible++; else closeCard(card);
        });
        clear.hidden = !query;
        count.textContent = visible + (visible === 1 ? ' question' : ' questions');
        empty.hidden = visible !== 0;
    }

    filters.forEach(function (filter) {
        filter.addEventListener('click', function () {
            category = filter.dataset.faqCategory;
            filters.forEach(function (item) { const active = item === filter; item.classList.toggle('active', active); item.setAttribute('aria-pressed', String(active)); });
            applyFilters();
        });
    });
    search.addEventListener('input', applyFilters);
    clear.addEventListener('click', function () { search.value = ''; search.focus(); applyFilters(); });

    const hashCard = location.hash && root.querySelector(location.hash);
    if (hashCard) {
        const button = hashCard.querySelector('button[aria-expanded]');
        button.click();
        setTimeout(function () { hashCard.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'center' }); }, 0);
    }
});
</script>
