<?php
$page_title = "Official Policies & Regulatory Documents | Waqt Finance";
$meta_description = "Download official regulatory policies of Waqt Finance including Fair Practice Code, KYC norms, interest rate policy & grievance redressal guidelines.";
$meta_keywords = "Waqt Finance documents, loan policy PDF, KYC documents list, fair practice code, NBFC policies India, loan guidelines PDF";
$canonical_url = "https://waqtfinance.com/policies";

include 'includes/header.php';
include 'includes/main-header.php';
?>

<div class="wf-apply-body" style="padding-top: 110px !important; background: #f8fafc !important; min-height: 70vh;">
    
    <!-- Page Header -->
    <div class="text-center" style="margin-bottom: 40px; padding: 0 20px;">
        <h1 style="font-size: 32px; font-weight: 800; color: #0c1a30; font-family: 'Inter', sans-serif !important; letter-spacing: -0.5px; margin: 0 0 10px 0; border: none; padding: 0;">Official Policies & Regulatory Documents</h1>
        <p style="font-size: 15px; color: #64748b; font-family: 'Inter', sans-serif !important; max-width: 600px; margin: 0 auto;">Waqt Finance is committed to transparency and regulatory compliance. Access and download our official policy papers below.</p>
    </div>

    <!-- Documents Grid Container -->
    <div class="container" style="max-width: 1200px;">
        <div class="pdfcard-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; padding: 0 15px 50px;">
            
            <!-- Card 1 -->
            <div class="pdfcard-card" onclick="openPDF('pdfs/document-on-loans-and-advances.pdf')">
                <div class="pdf-icon-wrap">
                    <i class="fa-solid fa-file-pdf"></i>
                </div>
                <div class="pdf-meta">
                    <h2 class="pdf-title">Document On Loans And Advances</h2>
                    <p class="pdf-desc">Official loan disbursement terms and advances guidelines.</p>
                </div>
                <div class="pdf-btn">
                    <i class="fa-solid fa-arrow-down-to-line"></i> Download PDF
                </div>
            </div>

            <!-- Card 2 -->
            <div class="pdfcard-card" onclick="openPDF('pdfs/policy-for-determining-interest-rates.pdf')">
                <div class="pdf-icon-wrap">
                    <i class="fa-solid fa-file-pdf"></i>
                </div>
                <div class="pdf-meta">
                    <h3 class="pdf-title">Policy For Determining Interest Rates</h3>
                    <p class="pdf-desc">Interest rate model, risk grading, and pricing policy.</p>
                </div>
                <div class="pdf-btn">
                    <i class="fa-solid fa-arrow-down-to-line"></i> Download PDF
                </div>
            </div>

            <!-- Card 3 -->
            <div class="pdfcard-card" onclick="openPDF('pdfs/FPC.pdf')">
                <div class="pdf-icon-wrap">
                    <i class="fa-solid fa-file-pdf"></i>
                </div>
                <div class="pdf-meta">
                    <h3 class="pdf-title">FPC (Fair Practice Code)</h3>
                    <p class="pdf-desc">Customer grievance, transparency, and lending practices code.</p>
                </div>
                <div class="pdf-btn">
                    <i class="fa-solid fa-arrow-down-to-line"></i> Download PDF
                </div>
            </div>

            <!-- Card 4 -->
            <div class="pdfcard-card" onclick="openPDF('pdfs/List_of_KYC_Documents_Waqt Finance.pdf')">
                <div class="pdf-icon-wrap">
                    <i class="fa-solid fa-file-pdf"></i>
                </div>
                <div class="pdf-meta">
                    <h3 class="pdf-title">List Of KYC Documents</h3>
                    <p class="pdf-desc">Required verification documents for individuals and businesses.</p>
                </div>
                <div class="pdf-btn">
                    <i class="fa-solid fa-arrow-down-to-line"></i> Download PDF
                </div>
            </div>

            <!-- Card 5 -->
            <div class="pdfcard-card" onclick="openPDF('pdfs/fair-practice-code.pdf')">
                <div class="pdf-icon-wrap">
                    <i class="fa-solid fa-file-pdf"></i>
                </div>
                <div class="pdf-meta">
                    <h3 class="pdf-title">Fair Practice Code Guidelines</h3>
                    <p class="pdf-desc">Detailed code guidelines for fair dealing with borrowers.</p>
                </div>
                <div class="pdf-btn">
                    <i class="fa-solid fa-arrow-down-to-line"></i> Download PDF
                </div>
            </div>

        </div>
    </div>
</div>

<style>
/* CSS overrides for modern regulatory document index */
.pdfcard-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    padding: 30px 24px !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    text-align: center !important;
    cursor: pointer !important;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.01) !important;
    position: relative !important;
    box-sizing: border-box !important;
    min-height: 260px !important;
    justify-content: space-between !important;
}

.pdfcard-card:hover {
    border-color: #18529c !important;
    transform: translateY(-4px) !important;
    box-shadow: 0 12px 30px -5px rgba(15, 23, 42, 0.04), 0 4px 10px -5px rgba(15, 23, 42, 0.04) !important;
}

.pdf-icon-wrap {
    width: 60px !important;
    height: 60px !important;
    border-radius: 12px !important;
    background: #fef2f2 !important; /* Soft red gradient background */
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin-bottom: 18px !important;
    transition: all 0.2s ease !important;
}

.pdf-icon-wrap i {
    font-size: 32px !important;
    color: #ef4444 !important; /* Red PDF icon */
}

.pdfcard-card:hover .pdf-icon-wrap {
    background: #ef4444 !important;
}

.pdfcard-card:hover .pdf-icon-wrap i {
    color: #ffffff !important;
}

.pdf-meta {
    flex-grow: 1 !important;
    margin-bottom: 20px !important;
}

.pdf-title {
    font-size: 16px !important;
    font-weight: 700 !important;
    color: #0c1a30 !important;
    margin: 0 0 6px 0 !important;
    line-height: 1.4 !important;
    font-family: 'Inter', sans-serif !important;
}

.pdf-desc {
    font-size: 13px !important;
    color: #64748b !important;
    margin: 0 !important;
    line-height: 1.5 !important;
    font-family: 'Inter', sans-serif !important;
}

.pdf-btn {
    width: 100% !important;
    height: 38px !important;
    border-radius: 8px !important;
    border: 1.5px solid #cbd5e1 !important;
    background: #ffffff !important;
    color: #475569 !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    transition: all 0.2s ease !important;
}

.pdfcard-card:hover .pdf-btn {
    background: #18529c !important;
    border-color: #18529c !important;
    color: #ffffff !important;
}

@media (max-width: 768px) {
    .pdfcard-card {
        padding: 24px 20px !important;
        min-height: 240px !important;
    }
}
</style>

<script>
function openPDF(file) {
    window.open(file, '_blank');
}
</script>

<?php include 'includes/footer.php' ?>