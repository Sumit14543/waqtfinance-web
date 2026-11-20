<?php
declare(strict_types=1);

$pageTitle = 'Location Product Content';
$pageSubtitle = 'Edit the content shown on product + city pages such as /personal-loan-in-delhi';

include 'header.php';
require_once __DIR__ . '/../includes/location_data.php';

$conn = db();
$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

if (!waqt_location_table_exists($conn, 'location_product_templates')) {
    echo '<div class="data-card" style="max-width:760px;"><h2 class="card-title-text"><i class="fa-solid fa-database" style="color:var(--warning); margin-right:8px;"></i> One-time setup needed</h2>'
        . '<p style="margin:12px 0 0; line-height:1.6;">The website is showing the default product content from <code>config/location-product-templates.php</code>. To edit it here, run <code>storage/migrations/2026_11_19_location_cms.sql</code> on the database, then <code>php storage/maintenance/seed_location_data.php --apply</code>.</p></div>';
    include 'footer.php';
    return;
}

$templates = waqt_location_product_templates(true);
$selected = (string) ($_GET['product'] ?? array_key_first($templates));
if (!isset($templates[$selected])) {
    $selected = (string) array_key_first($templates);
}
$t = $templates[$selected];
$lines = static fn($list): string => implode("\n", array_map('strval', is_array($list) ? $list : []));
$qaRows = static function ($list): array {
    $rows = [];
    foreach (is_array($list) ? $list : [] as $item) {
        if (is_array($item)) $rows[] = ['q' => (string) ($item['q'] ?? ''), 'a' => (string) ($item['a'] ?? '')];
    }
    return $rows;
};
$sampleCity = waqt_list_locations()[0]['slug'] ?? 'delhi';
?>
<style>
    /* Same form styles as Location CMS (those are defined inside locations.php). */
    .form-control-adm { width: 100%; padding: 10px 14px; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a; font-size: 14px; font-family: inherit; outline: none; transition: border-color .2s, box-shadow .2s; }
    .form-control-adm:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(24, 82, 156, .12); }
    textarea.form-control-adm { resize: vertical; line-height: 1.5; }
    .btn-secondary-adm { background: #f1f5f9; color: #475569; font-weight: 600; font-size: 13.5px; padding: 10px 16px; border-radius: 8px; border: 1px solid #cbd5e1; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
    .btn-secondary-adm:hover { background: #e2e8f0; color: #0f172a; }
    .lp-layout { display: grid; grid-template-columns: 260px minmax(0, 1fr); gap: 24px; align-items: start; }
    .lp-nav { position: sticky; top: 20px; display: grid; gap: 6px; }
    .lp-nav a { display: flex; justify-content: space-between; align-items: center; gap: 8px; padding: 12px 14px; border-radius: 10px; border: 1px solid var(--border-color); background: #fff; color: var(--text-main); text-decoration: none; font-weight: 600; font-size: 14px; }
    .lp-nav a.active { border-color: var(--primary); background: #eff6ff; color: var(--primary); }
    .lp-nav small { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: #dcfce7; color: #166534; }
    .lp-nav small.off { background: #f1f5f9; color: #64748b; }
    .lp-section { border-top: 1px solid var(--border-color); padding-top: 18px; margin-top: 18px; }
    .lp-section h3 { font-size: 15px; font-weight: 800; margin: 0 0 4px; }
    .lp-section > p { font-size: 13px; color: var(--text-muted); margin: 0 0 12px; }
    .lp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 220px), 1fr)); gap: 14px; }
    .lp-field label { display: block; font-size: 12.5px; font-weight: 700; margin-bottom: 6px; color: var(--text-main); }
    .lp-field small { display: block; font-size: 12px; color: var(--text-muted); margin-top: 4px; }
    .lp-field textarea { min-height: 160px; }
    .lp-qa-row { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.6fr) auto; gap: 8px; margin-bottom: 8px; }
    .lp-qa-row textarea { min-height: 44px; }
    .lp-remove { border: 1px solid #fecaca; background: #fff; color: #b91c1c; border-radius: 8px; width: 38px; cursor: pointer; }
    .lp-add { border: 1px dashed var(--border-color); background: #fff; border-radius: 8px; padding: 8px 12px; font-weight: 600; cursor: pointer; color: var(--primary); }
    .lp-actions { position: sticky; bottom: 0; background: #fff; border-top: 1px solid var(--border-color); padding: 14px 0 0; margin-top: 22px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
    @media (max-width: 991px) { .lp-layout { grid-template-columns: 1fr; } .lp-nav { position: static; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); } .lp-qa-row { grid-template-columns: 1fr; } }
</style>

<div class="lp-layout">
    <nav class="lp-nav" aria-label="Products">
        <?php foreach ($templates as $key => $tpl): ?>
            <a href="location-products.php?product=<?php echo $h(urlencode($key)); ?>" class="<?php echo $key === $selected ? 'active' : ''; ?>"<?php echo $key === $selected ? ' aria-current="page"' : ''; ?>>
                <span><?php echo $h($tpl['name'] ?? $key); ?></span>
                <small class="<?php echo !empty($tpl['is_active']) ? '' : 'off'; ?>"><?php echo !empty($tpl['is_active']) ? 'Live' : 'Hidden'; ?></small>
            </a>
        <?php endforeach; ?>
        <a href="locations.php"><span><i class="fa-solid fa-arrow-left"></i> Back to locations</span></a>
    </nav>

    <form class="data-card" id="lpForm" action="api.php" method="post" data-ajax-form novalidate>
        <div class="card-header-flex">
            <h2 class="card-title-text"><?php echo $h($t['name'] ?? $selected); ?></h2>
            <a class="btn-secondary-adm" href="../<?php echo $h($selected . '-in-' . $sampleCity); ?>" target="_blank" rel="noopener"><i class="fa-solid fa-eye"></i> View live page</a>
        </div>
        <p style="font-size:13.5px; color:var(--text-muted); margin:6px 0 0;">Changes apply to <code><?php echo $h($selected); ?>-in-*</code> pages in every city. A city can still override the title, description, amount, tenure, interest and FAQs from its own page in Location CMS.</p>
        <input type="hidden" name="product_key" value="<?php echo $h($selected); ?>">

        <div class="lp-section">
            <h3>Basics</h3>
            <div class="lp-grid">
                <div class="lp-field"><label for="f_name">Product name</label><input id="f_name" name="name" class="form-control-adm" value="<?php echo $h($t['name'] ?? ''); ?>" required maxlength="80"></div>
                <div class="lp-field"><label for="f_apply">Apply page</label><input id="f_apply" name="apply_url" class="form-control-adm" value="<?php echo $h($t['apply_url'] ?? ''); ?>" maxlength="120"><small>A site page such as personal-loan-apply.</small></div>
                <div class="lp-field"><label for="f_active">Show on website</label><select id="f_active" name="is_active" class="form-control-adm"><option value="1"<?php echo !empty($t['is_active']) ? ' selected' : ''; ?>>Yes, live</option><option value="0"<?php echo empty($t['is_active']) ? ' selected' : ''; ?>>No, hidden (pages return 404)</option></select></div>
                <div class="lp-field"><label for="f_sort">Display order</label><input id="f_sort" name="sort_order" type="number" min="0" max="9999" class="form-control-adm" value="<?php echo (int) ($t['sort_order'] ?? 0); ?>"></div>
            </div>
            <div class="lp-field" style="margin-top:14px;"><label for="f_desc">Short description</label><textarea id="f_desc" name="desc" class="form-control-adm" maxlength="600"><?php echo $h($t['desc'] ?? ''); ?></textarea><small>Shown on location product cards.</small></div>
        </div>

        <div class="lp-section">
            <h3>Amount, tenure and rate</h3>
            <p>Keep these the same as the main product page and your interest rate policy.</p>
            <div class="lp-grid">
                <div class="lp-field"><label for="f_range">Amount range (text)</label><input id="f_range" name="range_text" class="form-control-adm" value="<?php echo $h($t['range_text'] ?? ''); ?>" maxlength="80"><small>e.g. ₹50,000 to ₹25 Lakh</small></div>
                <div class="lp-field"><label for="f_min">Minimum amount (text)</label><input id="f_min" name="min_amount" class="form-control-adm" value="<?php echo $h($t['min_amount'] ?? ''); ?>" maxlength="40"></div>
                <div class="lp-field"><label for="f_max">Maximum amount for calculator (₹)</label><input id="f_max" name="max_amount_val" type="number" min="1000" step="1000" class="form-control-adm" value="<?php echo (int) ($t['max_amount_val'] ?? 0); ?>"></div>
                <div class="lp-field"><label for="f_tenure">Tenure (text)</label><input id="f_tenure" name="tenure" class="form-control-adm" value="<?php echo $h($t['tenure'] ?? ''); ?>" maxlength="60"></div>
                <div class="lp-field"><label for="f_dtenure">Default calculator tenure (months)</label><input id="f_dtenure" name="default_tenure" type="number" min="1" max="360" class="form-control-adm" value="<?php echo (int) ($t['default_tenure'] ?? 12); ?>"></div>
                <div class="lp-field"><label for="f_interest">Interest (text)</label><input id="f_interest" name="interest" class="form-control-adm" value="<?php echo $h($t['interest'] ?? ''); ?>" maxlength="80"></div>
                <div class="lp-field"><label for="f_drate">Default calculator rate (% p.a.)</label><input id="f_drate" name="default_rate" type="number" min="1" max="60" step="0.01" class="form-control-adm" value="<?php echo $h($t['default_rate'] ?? 12); ?>"></div>
            </div>
        </div>

        <div class="lp-section">
            <h3>Lists</h3>
            <p>One item per line.</p>
            <div class="lp-grid">
                <div class="lp-field"><label for="f_features">Features</label><textarea id="f_features" name="features" class="form-control-adm"><?php echo $h($lines($t['features'] ?? [])); ?></textarea></div>
                <div class="lp-field"><label for="f_elig">Eligibility</label><textarea id="f_elig" name="eligibility" class="form-control-adm"><?php echo $h($lines($t['eligibility'] ?? [])); ?></textarea></div>
                <div class="lp-field"><label for="f_docs">Documents</label><textarea id="f_docs" name="documents" class="form-control-adm"><?php echo $h($lines($t['documents'] ?? [])); ?></textarea></div>
                <div class="lp-field"><label for="f_benefits">Benefits</label><textarea id="f_benefits" name="benefits" class="form-control-adm"><?php echo $h($lines($t['benefits_vs_risks']['benefits'] ?? [])); ?></textarea></div>
                <div class="lp-field"><label for="f_risks">Risks and considerations</label><textarea id="f_risks" name="risks" class="form-control-adm"><?php echo $h($lines($t['benefits_vs_risks']['risks'] ?? [])); ?></textarea></div>
            </div>
        </div>

        <?php
        $qaSections = [
            'choice_items' => ['When this loan is the right choice', 'Situation and explanation.', $qaRows($t['choice_scenarios']['items'] ?? []), 'Situation', 'Why this loan fits'],
            'common_mistakes' => ['Common mistakes to avoid', 'Mistake and why it matters.', $qaRows($t['common_mistakes'] ?? []), 'Mistake', 'Explanation'],
            'faqs' => ['FAQs', 'Shown on every city page for this product and in Google FAQ results.', $qaRows($t['faqs'] ?? []), 'Question', 'Answer'],
        ];
        foreach ($qaSections as $field => [$title, $help, $rows, $qLabel, $aLabel]): ?>
        <div class="lp-section">
            <h3><?php echo $h($title); ?></h3>
            <p><?php echo $h($help); ?></p>
            <?php if ($field === 'choice_items'): ?>
                <div class="lp-field" style="margin-bottom:10px;"><label for="f_choice_title">Section heading</label><input id="f_choice_title" name="choice_title" class="form-control-adm" value="<?php echo $h($t['choice_scenarios']['title'] ?? ''); ?>" maxlength="120"></div>
            <?php endif; ?>
            <div class="lp-qa" data-field="<?php echo $h($field); ?>" data-q-label="<?php echo $h($qLabel); ?>" data-a-label="<?php echo $h($aLabel); ?>">
                <?php foreach ($rows as $row): ?>
                    <div class="lp-qa-row"><input class="form-control-adm" aria-label="<?php echo $h($qLabel); ?>" value="<?php echo $h($row['q']); ?>" maxlength="200"><textarea class="form-control-adm" aria-label="<?php echo $h($aLabel); ?>" maxlength="1500"><?php echo $h($row['a']); ?></textarea><button type="button" class="lp-remove" aria-label="Remove row"><i class="fa-solid fa-trash"></i></button></div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="lp-add" data-add="<?php echo $h($field); ?>"><i class="fa-solid fa-plus"></i> Add row</button>
        </div>
        <?php endforeach; ?>

        <div class="lp-actions">
            <button type="submit" class="btn-primary-adm" id="lpSave"><i class="fa-solid fa-floppy-disk"></i> Save product content</button>
            <button type="button" class="btn-secondary-adm" id="lpReset"><i class="fa-solid fa-rotate-left"></i> Restore defaults</button>
            <span id="lpStatus" role="status" style="font-size:13px; color:var(--text-muted);"></span>
        </div>
    </form>
</div>

<script>
(function () {
    const form = document.getElementById('lpForm');
    const status = document.getElementById('lpStatus');
    const csrf = <?php echo json_encode($_SESSION['csrf_token'] ?? ''); ?>;

    function rowHtml(qLabel, aLabel) {
        const row = document.createElement('div');
        row.className = 'lp-qa-row';
        row.innerHTML = '<input class="form-control-adm" maxlength="200"><textarea class="form-control-adm" maxlength="1500"></textarea><button type="button" class="lp-remove" aria-label="Remove row"><i class="fa-solid fa-trash"></i></button>';
        row.children[0].setAttribute('aria-label', qLabel);
        row.children[1].setAttribute('aria-label', aLabel);
        return row;
    }
    form.addEventListener('click', function (event) {
        const remove = event.target.closest('.lp-remove');
        if (remove) { remove.closest('.lp-qa-row').remove(); return; }
        const add = event.target.closest('[data-add]');
        if (add) {
            const box = form.querySelector('.lp-qa[data-field="' + add.dataset.add + '"]');
            const row = rowHtml(box.dataset.qLabel, box.dataset.aLabel);
            box.appendChild(row);
            row.children[0].focus();
        }
    });

    function collect() {
        const data = new FormData(form);
        form.querySelectorAll('.lp-qa').forEach(function (box) {
            const rows = [...box.querySelectorAll('.lp-qa-row')].map(function (row) {
                return { q: row.children[0].value.trim(), a: row.children[1].value.trim() };
            }).filter(function (r) { return r.q !== '' && r.a !== ''; });
            data.set(box.dataset.field, JSON.stringify(rows));
        });
        data.set('csrf_token', csrf);
        return data;
    }

    let cleanState = JSON.stringify(Array.from(form.querySelectorAll('input, textarea, select')).map(el => el.value));
    function state() { return JSON.stringify(Array.from(form.querySelectorAll('input, textarea, select')).map(el => el.value)); }
    window.addEventListener('beforeunload', e => { if (state() !== cleanState) { e.preventDefault(); e.returnValue = ''; } });
    let saving = false;
    function send(action, data) {
        if (saving) return;
        saving = true;
        const submittedState = state();
        form.querySelectorAll('button').forEach(btn => btn.disabled = true);
        data.set('action', action);
        status.textContent = 'Saving…';
        return fetch('api.php', { method: 'POST', headers: { 'X-CSRF-Token': csrf }, body: data })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                status.textContent = res.message || '';
                status.style.color = res.status === 'success' ? 'var(--success)' : 'var(--danger)';
                if (res.status === 'success') cleanState = submittedState;
                if (res.status === 'success' && action === 'reset_location_product') window.location.reload();
            })
            .catch(function () { status.textContent = 'Could not save. Please try again.'; status.style.color = 'var(--danger)'; })
            .finally(() => { saving = false; form.querySelectorAll('button').forEach(btn => btn.disabled = false); });
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        if (!form.reportValidity()) return;
        for (const row of form.querySelectorAll('.lp-qa-row')) {
            const q = row.children[0], a = row.children[1];
            if (Boolean(q.value.trim()) !== Boolean(a.value.trim())) { status.textContent = 'Complete both fields in each row, or remove the row.'; (q.value.trim() ? a : q).focus(); return; }
        }
        if (!form.querySelector('#f_name').value.trim()) { status.textContent = 'Product name is required.'; status.style.color = 'var(--danger)'; return; }
        send('save_location_product', collect());
    });
    document.getElementById('lpReset').addEventListener('click', function () {
        if (!confirm('Restore the default content for this product? Your edits for this product will be removed.')) return;
        const data = new FormData();
        data.set('product_key', form.querySelector('[name="product_key"]').value);
        data.set('csrf_token', csrf);
        send('reset_location_product', data);
    });
})();
</script>

<?php include 'footer.php'; ?>
