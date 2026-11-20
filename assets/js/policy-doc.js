/*
 * Policy documents: table of contents with scroll-spy, reading progress,
 * scrollable tables, section links, print button and reading time.
 * Everything here is an enhancement; the page reads fine without it.
 */
(function () {
    'use strict';

    var paper = document.querySelector('.wf-policy-paper');
    var tabs = document.querySelector('.wf-policy-nav-tabs');
    var navWrap = document.querySelector('.wf-policy-nav-wrap');
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function slugify(text, used) {
        var base = text.toLowerCase()
            .replace(/^[\s\d.ivx)]+(?=[a-z])/i, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .slice(0, 60) || 'section';
        var slug = base, n = 2;
        while (used[slug] || document.getElementById(slug)) { slug = base + '-' + n++; }
        used[slug] = true;
        return slug;
    }

    function cleanLabel(text) {
        return text.replace(/\s+/g, ' ').replace(/[:\s]+$/, '').trim();
    }

    // "2. GRIEVANCE REDRESSAL OFFICER" reads better as "2. Grievance Redressal Officer" in a menu.
    var SMALL_WORDS = /^(a|an|and|as|at|by|for|in|of|on|or|the|to|with)$/;
    function menuLabel(text) {
        var letters = text.replace(/[^A-Za-z]/g, '');
        if (letters.length < 4 || letters !== letters.toUpperCase()) return text;
        return text.toLowerCase().split(' ').map(function (word, i) {
            if (i > 0 && SMALL_WORDS.test(word)) return word;
            return word.replace(/[a-z]/, function (c) { return c.toUpperCase(); });
        }).join(' ');
    }

    /* ---- Policy tabs: keep the active tab in view and show which edge scrolls ---- */
    if (tabs) {
        var active = tabs.querySelector('.wf-policy-tab-btn.active');
        if (active) {
            active.setAttribute('aria-current', 'page');
            if (tabs.scrollWidth > tabs.clientWidth) {
                var offset = active.getBoundingClientRect().left - tabs.getBoundingClientRect().left + tabs.scrollLeft;
                tabs.scrollLeft = offset - (tabs.clientWidth - active.offsetWidth) / 2;
            }
        }
        var updateEdges = function () {
            var max = tabs.scrollWidth - tabs.clientWidth;
            tabs.classList.toggle('has-more-left', max > 1 && tabs.scrollLeft > 1);
            tabs.classList.toggle('has-more-right', max > 1 && tabs.scrollLeft < max - 1);
        };
        updateEdges();
        tabs.addEventListener('scroll', updateEdges, { passive: true });
        window.addEventListener('resize', updateEdges);
    }

    /* ---- Print / save as PDF ---- */
    document.querySelectorAll('[data-pd-print]').forEach(function (btn) {
        btn.addEventListener('click', function () { window.print(); });
    });

    /* ---- Reading time ---- */
    var readingTarget = paper || document.querySelector('.wf-legal-content');
    var readingSlot = document.querySelector('[data-pd-reading]');
    if (readingTarget && readingSlot) {
        var words = (readingTarget.textContent || '').trim().split(/\s+/).length;
        var minutes = Math.max(1, Math.round(words / 200));
        readingSlot.innerHTML = '<i class="far fa-clock" aria-hidden="true"></i> ' + minutes + ' min read';
        readingSlot.hidden = false;
    }

    if (!paper) return;

    /* ---- Scrollable tables ---- */
    paper.querySelectorAll('table').forEach(function (table) {
        if (table.parentElement.classList.contains('pd-table-scroll')) return;
        var wrap = document.createElement('div');
        wrap.className = 'pd-table-scroll';
        wrap.setAttribute('role', 'region');
        wrap.setAttribute('tabindex', '0');
        var label = table.querySelector('th') ? table.querySelector('th').textContent : 'Table';
        wrap.setAttribute('aria-label', cleanLabel(label));
        table.parentNode.insertBefore(wrap, table);
        wrap.appendChild(table);
    });

    /* ---- Headings that make up the table of contents ---- */
    var headings = Array.prototype.slice.call(paper.querySelectorAll(':scope > h3'));
    if (!headings.length) {
        // Older documents number their sections with <h5>; use only the numbered ones.
        headings = Array.prototype.slice.call(paper.querySelectorAll('h5')).filter(function (h) {
            return /^\s*\d+\./.test(h.textContent);
        });
        headings.forEach(function (h) { h.classList.add('pd-section'); });
    }
    if (headings.length < 3) return;

    var used = {};
    var items = headings.map(function (h) {
        var label = cleanLabel(h.textContent);
        if (!h.id) h.id = slugify(label, used);
        var anchor = document.createElement('a');
        anchor.className = 'pd-anchor';
        anchor.href = '#' + h.id;
        anchor.setAttribute('aria-label', 'Link to section: ' + label);
        anchor.textContent = '#';
        h.appendChild(anchor);
        return { heading: h, label: label };
    });

    function buildList() {
        var ol = document.createElement('ol');
        items.forEach(function (item) {
            var li = document.createElement('li');
            var a = document.createElement('a');
            a.href = '#' + item.heading.id;
            a.textContent = menuLabel(item.label);
            a.setAttribute('data-target', item.heading.id);
            li.appendChild(a);
            ol.appendChild(li);
        });
        return ol;
    }

    // Desktop: sticky sidebar next to the document.
    var layout = document.createElement('div');
    layout.className = 'pd-layout';
    var toc = document.createElement('nav');
    toc.className = 'pd-toc';
    toc.setAttribute('aria-label', 'On this page');
    var title = document.createElement('p');
    title.className = 'pd-toc-title';
    title.textContent = 'On this page';
    toc.appendChild(title);
    toc.appendChild(buildList());
    paper.parentNode.insertBefore(layout, paper);
    layout.appendChild(toc);
    layout.appendChild(paper);

    // Small screens: a collapsible list at the top of the document.
    var details = document.createElement('details');
    details.className = 'pd-toc-mobile';
    var summary = document.createElement('summary');
    summary.innerHTML = '<span>On this page</span><i class="fas fa-chevron-down" aria-hidden="true"></i>';
    details.appendChild(summary);
    details.appendChild(buildList());
    var firstHeading = paper.querySelector(':scope > h2');
    if (firstHeading) firstHeading.insertAdjacentElement('afterend', details);
    else paper.insertBefore(details, paper.firstChild);
    details.addEventListener('click', function (e) {
        if (e.target.tagName === 'A') details.open = false;
    });

    // Smooth scroll that respects reduced motion; keep the URL hash in sync.
    document.querySelectorAll('.pd-toc a, .pd-toc-mobile a, .pd-anchor').forEach(function (a) {
        a.addEventListener('click', function (e) {
            var target = document.getElementById(a.getAttribute('href').slice(1));
            if (!target) return;
            e.preventDefault();
            target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
            if (history.replaceState) history.replaceState(null, '', '#' + target.id);
        });
    });

    // Scroll-spy: highlight the section currently being read.
    var links = toc.querySelectorAll('a');
    function setActive(id) {
        links.forEach(function (link) {
            var on = link.getAttribute('data-target') === id;
            link.classList.toggle('is-active', on);
            if (on) link.setAttribute('aria-current', 'location'); else link.removeAttribute('aria-current');
            if (on && toc.scrollHeight > toc.clientHeight) {
                var top = link.offsetTop - toc.clientHeight / 2;
                toc.scrollTop = Math.max(0, top);
            }
        });
    }

    var stickyTop = 190;
    function onScroll() {
        // The sticky policy tabs end where the readable area begins.
        if (navWrap) stickyTop = Math.max(120, Math.min(260, navWrap.getBoundingClientRect().bottom + 16));
        var current = items[0].heading.id;
        for (var i = 0; i < items.length; i++) {
            if (items[i].heading.getBoundingClientRect().top - stickyTop <= 48) current = items[i].heading.id;
            else break;
        }
        setActive(current);

        if (progress) {
            var rect = paper.getBoundingClientRect();
            var total = rect.height - (window.innerHeight - stickyTop);
            var done = Math.min(1, Math.max(0, (stickyTop - rect.top) / (total > 0 ? total : 1)));
            progress.style.transform = 'scaleX(' + done.toFixed(3) + ')';
        }
    }

    // Reading progress along the bottom edge of the sticky policy tabs.
    var progress = null;
    if (navWrap) {
        progress = document.createElement('div');
        progress.className = 'pd-progress';
        progress.setAttribute('aria-hidden', 'true');
        navWrap.appendChild(progress);
    }

    var ticking = false;
    window.addEventListener('scroll', function () {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(function () { onScroll(); ticking = false; });
    }, { passive: true });
    window.addEventListener('resize', onScroll);
    onScroll();
})();
