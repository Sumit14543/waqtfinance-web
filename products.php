<?php
$page_title = "Find the Right Loan for You | Waqt Finance Products";
$meta_description = "From personal loans to business finance — Waqt Finance offers 7 loan types with clear eligibility criteria and fast digital approval. Compare now.";
$canonical_url = "https://waqtfinance.com/products";
$meta_image = "assets/images/loan-hero.webp";
$meta_image_alt = "Explore Waqt Finance loan products";
$breadcrumb_label = "Loan Products";

$loan_products = [
    ['key'=>'personal','audience'=>'personal','icon'=>'fa-user','name'=>'Personal Loan','eyebrow'=>'Everyday personal needs','description'=>'A general-purpose unsecured loan journey for eligible salaried or self-employed applicants.','best'=>'Medical needs, travel, education, wedding or planned expenses','security'=>'Generally unsecured','learn'=>'personal-loan','apply'=>'personal-loan-apply'],
    ['key'=>'payday','audience'=>'personal','icon'=>'fa-calendar-check','name'=>'Payday Loan','eyebrow'=>'Short-term cash-flow support','description'=>'A short-duration application journey intended for eligible salaried applicants facing a temporary cash-flow gap.','best'=>'Time-sensitive expenses before the next salary cycle','security'=>'Generally unsecured','learn'=>'pay-day-loan','apply'=>'payday-apply'],
    ['key'=>'business','audience'=>'business','icon'=>'fa-briefcase','name'=>'Business Loan','eyebrow'=>'Funding for enterprises','description'=>'Business finance assessed using turnover, cash flow, continuity, banking and the overall applicant profile.','best'=>'Working capital, inventory, equipment or business expansion','security'=>'Product dependent','learn'=>'business-loan','apply'=>'business-loan-apply'],
    ['key'=>'shopkeeper','audience'=>'business','icon'=>'fa-store','name'=>'Shopkeeper Business Loan','eyebrow'=>'Designed for retail businesses','description'=>'A focused business-loan journey for eligible shopkeepers and small retail establishments.','best'=>'Stock purchase, store improvement and operating expenses','security'=>'Product dependent','learn'=>'shopkeeper-business-loan','apply'=>'business-loan-apply'],
    ['key'=>'edi','audience'=>'business','icon'=>'fa-building-columns','name'=>'EDI Loan','eyebrow'=>'Structured business application','description'=>'A dedicated application journey for eligible businesses seeking finance through the EDI product process.','best'=>'Eligible enterprise and institutional funding requirements','security'=>'Assessment dependent','learn'=>'edi-loan','apply'=>'edi-loan-apply'],
    ['key'=>'vehicle','audience'=>'secured','icon'=>'fa-car-side','name'=>'Vehicle Loan','eyebrow'=>'Finance for mobility','description'=>'Finance for an eligible vehicle purchase, subject to applicant, vehicle and documentation assessment.','best'=>'New or used eligible vehicle purchase','security'=>'Vehicle-backed','learn'=>'vehicle-loan','apply'=>'vehicle-loan-apply'],
    ['key'=>'lap','audience'=>'secured','icon'=>'fa-house-chimney','name'=>'Loan Against Property','eyebrow'=>'Unlock property value','description'=>'A secured loan assessed against an eligible property, income, repayment capacity and legal verification.','best'=>'Larger personal or business funding requirements','security'=>'Property-backed','learn'=>'loan-against-property','apply'=>'loan-against-property-apply'],
];

$digital_brands = [
    ['name'=>'GeetPay','image'=>'geetpay.webp','alt'=>'GeetPay by Waqt Finance','url'=>'https://geetpay.in/','external'=>true],
    ['name'=>'Loan in Wallet','image'=>'loan-in-1.webp','alt'=>'Loan in Wallet','url'=>'https://www.loaninwallet.com/','external'=>true],
    ['name'=>'Urgent Loan Now','image'=>'argent-loan-1.webp','alt'=>'Urgent Loan Now','url'=>'https://www.urgentloannow.com/','external'=>true],
    ['name'=>'Waqt Money','image'=>'waqt-money-new.webp','alt'=>'Waqt Money','url'=>'https://waqtmoney.com/','external'=>true],
    ['name'=>'Salary Waves','image'=>'salary.webp','alt'=>'Salary Waves','url'=>'https://www.salarywaves.com/','external'=>true],
    ['name'=>'Aapka Money','image'=>'aapka-money.webp','alt'=>'Aapka Money','url'=>'https://www.aapkamoney.com/','external'=>true],
    ['name'=>'Udhar Capital','image'=>'udhaar_logo.webp','alt'=>'Udhar Capital','url'=>'https://udharcapital.com/','external'=>true],
];

$seo_schema = [
    '@type'=>'CollectionPage','@id'=>$canonical_url.'#collection','name'=>'Loan Products - Waqt Finance','url'=>$canonical_url,
    'description'=>$meta_description,
    'mainEntity'=>['@type'=>'ItemList','numberOfItems'=>count($loan_products),'itemListElement'=>array_map(static function(array $product,int $index):array {
        return ['@type'=>'ListItem','position'=>$index+1,'url'=>'https://waqtfinance.com/'.$product['learn'],'item'=>['@type'=>'Service','name'=>$product['name'],'description'=>$product['description'],'provider'=>['@type'=>'Organization','name'=>'Waqt Finance','url'=>'https://waqtfinance.com'],'areaServed'=>['@type'=>'Country','name'=>'India']]];
    },$loan_products,array_keys($loan_products))]
];

$extra_head = '<link rel="stylesheet" href="assets/css/products-page.css?v='.(file_exists(__DIR__.'/assets/css/products-page.css')?filemtime(__DIR__.'/assets/css/products-page.css'):time()).'">';
include 'includes/header.php';
include 'includes/main-header.php';
?>
<main class="wf-products-page" id="main-content">
    <section class="wf-products-hero" aria-labelledby="products-title">
        <div class="container">
            <nav class="wf-products-breadcrumb" aria-label="Breadcrumb"><a href="./">Home</a><span aria-hidden="true">/</span><span aria-current="page">Loan products</span></nav>
            <div class="wf-products-hero-grid">
                <div class="wf-products-hero-copy">
                    <span class="wf-products-kicker"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Transparent product discovery</span>
                    <h1 id="products-title">Find a loan journey that fits your goal</h1>
                    <p>Explore personal, business and secured finance options. Eligibility, amount, pricing and tenure are confirmed only after assessment.</p>
                    <div class="wf-products-hero-actions"><a href="#loan-products" class="wf-products-btn primary">Explore products <i class="fa-solid fa-arrow-down" aria-hidden="true"></i></a><a href="eligibility-calculator" class="wf-products-btn secondary">Check eligibility</a></div>
                </div>
                <div class="wf-products-guide" aria-label="How to choose a loan">
                    <strong>Before you choose</strong>
                    <ol><li><span>1</span><div><b>Match the purpose</b><small>Personal, business or asset-backed need</small></div></li><li><span>2</span><div><b>Check affordability</b><small>Consider EMI alongside existing obligations</small></div></li><li><span>3</span><div><b>Review the KFS</b><small>Compare APR, tenure, fees and repayment terms</small></div></li></ol>
                </div>
            </div>
        </div>
    </section>

    <section class="wf-products-catalog" id="loan-products" aria-labelledby="catalog-title">
        <div class="container">
            <div class="wf-products-section-head"><div><span>Loan catalogue</span><h2 id="catalog-title">Choose by borrowing need</h2><p>Use the filters to narrow the catalogue. No filter selection changes your eligibility.</p></div><p id="productCount" aria-live="polite"><?php echo count($loan_products); ?> products</p></div>
            <div class="wf-products-filters" role="group" aria-label="Filter loan products">
                <button type="button" class="active" data-product-filter="all" aria-pressed="true">All products</button>
                <button type="button" data-product-filter="personal" aria-pressed="false"><i class="fa-regular fa-user" aria-hidden="true"></i> Personal needs</button>
                <button type="button" data-product-filter="business" aria-pressed="false"><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Business needs</button>
                <button type="button" data-product-filter="secured" aria-pressed="false"><i class="fa-solid fa-lock" aria-hidden="true"></i> Asset-backed</button>
            </div>
            <div class="wf-products-grid" id="productGrid">
                <?php foreach($loan_products as $product): ?>
                <article class="wf-catalog-card" data-audience="<?php echo htmlspecialchars($product['audience'],ENT_QUOTES,'UTF-8'); ?>" aria-labelledby="product-<?php echo $product['key']; ?>">
                    <div class="wf-product-card-top"><span class="wf-product-icon"><i class="fa-solid <?php echo $product['icon']; ?>" aria-hidden="true"></i></span><span class="wf-product-security"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> <?php echo htmlspecialchars($product['security'],ENT_QUOTES,'UTF-8'); ?></span></div>
                    <span class="wf-product-eyebrow"><?php echo htmlspecialchars($product['eyebrow'],ENT_QUOTES,'UTF-8'); ?></span>
                    <h3 id="product-<?php echo $product['key']; ?>"><?php echo htmlspecialchars($product['name'],ENT_QUOTES,'UTF-8'); ?></h3>
                    <p><?php echo htmlspecialchars($product['description'],ENT_QUOTES,'UTF-8'); ?></p>
                    <div class="wf-product-best"><span>May suit</span><strong><?php echo htmlspecialchars($product['best'],ENT_QUOTES,'UTF-8'); ?></strong></div>
                    <div class="wf-product-actions"><a href="<?php echo $product['learn']; ?>" class="wf-product-learn" aria-label="Learn about <?php echo htmlspecialchars($product['name'],ENT_QUOTES,'UTF-8'); ?>">View details</a><a href="<?php echo $product['apply']; ?>" class="wf-product-apply" aria-label="Apply for <?php echo htmlspecialchars($product['name'],ENT_QUOTES,'UTF-8'); ?>">Start application <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="wf-products-disclosure" aria-labelledby="cost-title"><div class="container"><div class="wf-products-disclosure-inner"><span class="wf-products-disclosure-icon"><i class="fa-solid fa-file-invoice" aria-hidden="true"></i></span><div><span>Borrow with clarity</span><h2 id="cost-title">Compare the total cost—not just the headline rate</h2><p>The applicable loan amount, interest type, APR, tenure, EMI, fees and charges are disclosed in your offer and Key Facts Statement before acceptance.</p></div><div class="wf-products-policy-links"><a href="interest-rate-policy">Interest rate policy <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a><a href="faqs">Loan FAQs <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div></div></div></section>

    <section class="wf-brands-section" aria-labelledby="brands-title"><div class="container"><div class="wf-products-section-head"><div><span>Digital experiences</span><h2 id="brands-title">Explore our lending brands</h2><p>Always verify the destination and review the applicable lender, KFS and terms before continuing.</p></div></div><div class="wf-brands-grid">
        <?php foreach($digital_brands as $brand): ?><a class="wf-brand-card" href="<?php echo htmlspecialchars($brand['url'],ENT_QUOTES,'UTF-8'); ?>"<?php echo $brand['external']?' target="_blank" rel="noopener noreferrer"':''; ?> aria-label="Visit <?php echo htmlspecialchars($brand['name'],ENT_QUOTES,'UTF-8'); ?> website (opens in a new tab)"><span class="wf-brand-logo"><img src="assets/images/<?php echo htmlspecialchars($brand['image'],ENT_QUOTES,'UTF-8'); ?>" alt="<?php echo htmlspecialchars($brand['alt'],ENT_QUOTES,'UTF-8'); ?>" loading="lazy" width="220" height="72"></span><span class="wf-brand-content"><span class="wf-brand-meta"><strong><?php echo htmlspecialchars($brand['name'],ENT_QUOTES,'UTF-8'); ?></strong><small><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Official website</small></span><span class="wf-brand-visit">Visit website <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></span></span></a><?php endforeach; ?>
    </div></div></section>

    <section class="wf-products-help"><div class="container"><div class="wf-products-help-inner"><div><span>Not sure where to begin?</span><h2>Get help choosing the right journey</h2><p>Our support team can explain the application route. Final approval and terms remain subject to assessment.</p></div><div><a href="tel:9217086610" class="wf-products-btn secondary"><i class="fa-solid fa-phone" aria-hidden="true"></i> Call support</a><a href="contact-us" class="wf-products-btn primary">Contact us <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div></div></div></section>
</main>
<script>
document.addEventListener('DOMContentLoaded',function(){
 const buttons=Array.from(document.querySelectorAll('[data-product-filter]')); const cards=Array.from(document.querySelectorAll('.wf-catalog-card')); const count=document.getElementById('productCount');
 buttons.forEach(function(button){button.addEventListener('click',function(){const filter=button.dataset.productFilter;let visible=0;buttons.forEach(function(item){const active=item===button;item.classList.toggle('active',active);item.setAttribute('aria-pressed',String(active));});cards.forEach(function(card){card.hidden=filter!=='all'&&card.dataset.audience!==filter;if(!card.hidden)visible++;});count.textContent=visible+(visible===1?' product':' products');});});
});
</script>
<?php include 'includes/footer.php'; ?>
