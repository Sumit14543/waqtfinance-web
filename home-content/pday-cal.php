<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    .calculator {
        max-width: 1220px;
        margin: auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    h2 {
        margin-bottom: 30px;
    }

    h2 span {
        color: #18529c;
    }

    .pi-box-s h3 {
        font-size: 16px;
        color: rgb(33 37 41 / 75%);
    }

    .left,
    .right {
        width: 100%;
    }

    .right {
        background: #f8f9fc;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        height: 100%;
    }

    .left label {
        font-weight: bold;
        display: block;
        margin-top: 15px;
    }

    .value {
        color: grey;
        font-weight: bold;
        margin-bottom: 15px;
    }

    h1 {
        color: #2c6ed5;
        margin-top: 0;
    }

    .personal-sec {
        padding: 0px 20px;
    }

    .box,
    .pi-box-s {
        border-radius: 16px;
        padding: 25px;
        text-align: center;
        background: white;
        box-shadow: rgba(0, 0, 0, 0.05) 0px 6px 24px 0px, rgba(0, 0, 0, 0.08) 0px 0px 0px 1px;
        margin-top: 10px;
    }

    #principal,
    #totalInterest {
        font-size: 16px;
        color: #212529;
    }

    .total {
        font-weight: bold;
        background: #93c5fd;26;
        padding: 18px;
        display: flex;
        justify-content: space-between;
    }

    .right button {
        padding: 10px 12px;
        width: 100%;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
    }

    .apply-calc {
        background: #18529c !important;
        color: white !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
    }
    .apply-calc:hover {
        background: #0d3f7b !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(24, 82, 156, 0.2);
    }

    .pdf {
        background: transparent !important;
        color: #475569 !important;
        border: 1px solid #cbd5e1 !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
    }
    .pdf:hover {
        background: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        transform: translateY(-2px);
    }

    .pd-one {
        background: #198754 !important;
        color: #fff !important;
        border: 0 !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
    }
    .pd-one:hover {
        background: #157347 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.2);
    }

    .whatsapp {
        background: #e2e8f0 !important;
        color: #475569 !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
    }
    .whatsapp:hover {
        background: #cbd5e1 !important;
        transform: translateY(-2px);
    }

    canvas {
        margin-top: 30px;
        max-width: 100%;
    }

    input[type=range] {
        -webkit-appearance: none;
        width: 100%;
        height: 6px;
        border-radius: 10px;
        outline: none;
        margin: 15px 0;
        background: #d1d5db;
    }

    input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        height: 18px;
        width: 18px;
        border-radius: 50%;
        background: #18529c;
        border: 4px solid #0d3f7b;
        position: relative;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        box-shadow: 0 2px 6px rgba(24, 82, 156, 0.2);
    }

    input[type="range"]::-webkit-slider-thumb:hover {
        transform: scale(1.15);
        box-shadow: 0 0 12px rgba(24, 82, 156, 0.4);
    }

    input[type="range"]::-moz-range-thumb {
        height: 18px;
        width: 18px;
        border-radius: 50%;
        background: radial-gradient(circle at center, rgba(255, 255, 255, 0.8) 30%, #18529c 30%);
        border: 3px solid white;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(24, 82, 156, 0.2);
    }

    #loanChart {
        height: 300px !important;
        width: 300px !important;
        margin: 0 auto;
    }

    .text-muted {
        text-align: left;
    }

    .progress-custom {
        width: 100%;
        background: #e9ecef;
        height: 12px;
        border-radius: 10px;
        display: flex;
        overflow: hidden;
    }

    .progress-principal {
        background: #18529c;
        height: 100%;
        width: 0%;
        transition: width 0.3s ease;
    }

    .progress-interest {
        background: #93c5fd;
        height: 100%;
        width: 0%;
        transition: width 0.3s ease;
    }

    #emi {
        font-size: 48px;
        font-weight: bold;
        color: #000;
    }

    @media (max-width: 991px) {
        .calculator {
            padding: 20px;
        }
    }

    @media (max-width: 768px) {
        #loanChart {
            height: 240px !important;
            width: 240px !important;
        }
        #emi {
            font-size: 32px;
        }
        .calculator {
            padding: 15px;
        }
        .box, .pi-box-s {
            padding: 15px;
        }
        .total {
            padding: 15px;
        }
    }

    @media (max-width: 480px) {
        #emi {
            font-size: 28px;
        }
        .right button {
            font-size: 14px;
            padding: 10px;
        }
        .total {
            flex-direction: column;
            gap: 5px;
        }
    }

    @media (max-width: 360px) {
        .personal-sec {
            padding: 0px 10px;
        }
        .calculator {
            padding: 10px;
        }
        .right {
            padding: 15px;
        }
        .box, .pi-box-s {
            padding: 10px;
        }
        .col-6 {
            width: 100%;
            margin-top: 10px;
        }
        #loanChart {
            height: 200px !important;
            width: 200px !important;
        }
        #emi {
            font-size: 24px;
        }
        .right button {
            font-size: 13px;
            padding: 8px;
        }
    }
    .amort-btn {
        transition: all 0.3s ease !important;
    }
    .amort-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(24, 82, 156, 0.25);
    }
</style>

<section class="personal-sec">
    <div class="calculator">
        <div class="container-fluid px-0">
            <div class="row g-4">
                <div class="col-lg-6 col-12 text-start">
                    <div class="left">

                        <div class="d-flex justify-content-between mb-2">
                            <span class="slider-label">Loan Amount</span>
                            <span class="value-text" id="loanAmountText">₹50,000</span>
                        </div>

                        <input type="range" id="loanAmount" min="1000" max="50000" step="500" value="5000">
                        <div class="value" id="amount">₹5,000</div>

                        <label>Loan Tenure</label>
                        <input type="range" id="tenure" min="7" max="30" value="15">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="value" id="days">15 Days</span>
                            <span>30 Days</span>
                        </div>

                        <label>Interest Rate (per day)</label>
                        <input type="range" id="interestRate" min="1" max="3" step="0.1" value="1">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="value" id="rate">1% Per Day</span>
                            <span>3%</span>
                        </div>

                        <canvas id="loanChart"></canvas>

                    </div>
                </div>

                <div class="col-lg-6 col-12 text-center text-lg-end">
                    <div class="right">

                        <div class="pi-box-s mt-0">
                            <h3>Total Payable Amount</h3>
                            <h2 id="emi">₹0</h2>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-6">
                                <div class="box h-100 d-flex flex-column justify-content-center m-0">
                                    <span class="d-block mb-1">Principal</span>
                                    <span id="principal" class="fw-bold">₹0</span>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="box h-100 d-flex flex-column justify-content-center m-0">
                                    <span class="d-block mb-1">Total Interest</span>
                                    <span id="totalInterest" class="fw-bold">₹0</span>
                                </div>
                            </div>
                        </div>

                        <div class="box total mt-3">
                            <span>Total Payment</span>
                            <span id="totalPayment">₹0</span>
                        </div>

                        <div class="mb-2 small text-muted mt-4 text-start">Payment Breakdown</div>

                        <div class="progress-custom mb-3">
                            <div id="principalBar" class="progress-principal"></div>
                            <div id="interestBar" class="progress-interest"></div>
                        </div>

                        <div class="d-flex justify-content-between small mb-4">
                            <span>Principal</span>
                            <span>Interest</span>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-12 col-sm-6">
                                <button class="apply-calc w-100" onclick="window.location.href='payday-apply'">Apply Now</button>
                            </div>

                            <div class="col-12 col-sm-6">
                                <button class="pdf w-100" onclick="window.location.href='emi-calculator'">Full Calculator</button>
                            </div>

                            <div class="col-12 col-sm-6">
                                <button class="pdf pd-one w-100" onclick="downloadPDF()">
                                    <i class="fas fa-download me-1"></i> Download PDF
                                </button>
                            </div>

                            <div class="col-12 col-sm-6">
                                <button class="whatsapp w-100" onclick="shareWhatsApp()">
                                    <i class="fab fa-whatsapp me-1"></i> Share WhatsApp
                                </button>
                            </div>

                            <div class="col-12 mt-3">
                                <button type="button" class="amort-btn w-100" id="amortBtn" style="background:linear-gradient(135deg, #18529c, #0d3f7b);color:#fff; padding: 12px 18px !important; border:none; border-radius:6px; font-weight:500;">
                                    <i class="fas fa-chart-line me-2"></i> View Amortization Schedule
                                </button>
                            </div>
                        </div>

                    </div>
                       </div>
            <div class="collapse mt-4" id="amortization">
                <div class="card card-body border-0 shadow-sm p-4" style="border-radius: 12px; background: #f8fafc; text-align: left;">
                    <h5 class="fw-bold mb-3" style="color: #18529c;">Daily Repayment Schedule</h5>
                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                            <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th class="text-start">Period</th>
                                    <th class="text-end">Repayment Amount</th>
                                    <th class="text-end">Principal</th>
                                    <th class="text-end">Interest</th>
                                    <th class="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody id="amortizationBody">
                                <!-- Dynamic Rows go here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
const loanAmount = document.getElementById("loanAmount");
const tenure = document.getElementById("tenure");
const interestRate = document.getElementById("interestRate");

const emi = document.getElementById("emi");
const principal = document.getElementById("principal");
const totalInterest = document.getElementById("totalInterest");
const totalPayment = document.getElementById("totalPayment");

const amountText = document.getElementById("amount");
const daysText = document.getElementById("days");
const rateText = document.getElementById("rate");

const principalBar = document.getElementById("principalBar");
const interestBar = document.getElementById("interestBar");

const ctx = document.getElementById("loanChart");

let chart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Principal', 'Interest'],
        datasets: [{
            data: [0, 0],
            backgroundColor: ['#18529c', '#93c5fd']
        }]
    },
    options: {
        cutout: '75%',
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

function calculateLoan() {
    let P = parseFloat(loanAmount.value);
    let T = parseFloat(tenure.value);
    let R = parseFloat(interestRate.value) / 100;

    let interest = P * R * T;
    let total = P + interest;

    amountText.innerText = "₹" + P.toLocaleString("en-IN");
    daysText.innerText = T + " Days";
    rateText.innerText = (R * 100).toFixed(1) + "% Per Day";

    emi.innerText = "₹" + Math.round(total).toLocaleString("en-IN");
    principal.innerText = "₹" + P.toLocaleString("en-IN");
    totalInterest.innerText = "₹" + Math.round(interest).toLocaleString("en-IN");
    totalPayment.innerText = "₹" + Math.round(total).toLocaleString("en-IN");

    chart.data.datasets[0].data = [P, interest];
    chart.update();

    let principalPercent = (P / total) * 100;
    let interestPercent = (interest / total) * 100;

    principalBar.style.width = principalPercent + "%";
    interestBar.style.width = interestPercent + "%";

    // Generate daily repayment schedule
    let amortHtml = "";
    let accumInterest = 0;
    let dailyInterest = P * R;
    for (let i = 1; i <= T; i++) {
        accumInterest += dailyInterest;
        let isLast = (i === T);
        let repayment = isLast ? (P + interest) : 0;
        let principalPaid = isLast ? P : 0;
        let balance = isLast ? 0 : (P + accumInterest);
        
        amortHtml += `<tr>
            <td class="text-start">Day ${i} ${isLast ? ' <strong>(Maturity)</strong>' : ''}</td>
            <td class="text-end">₹${Math.round(repayment).toLocaleString("en-IN")}</td>
            <td class="text-end">₹${Math.round(principalPaid).toLocaleString("en-IN")}</td>
            <td class="text-end">₹${Math.round(dailyInterest).toLocaleString("en-IN")}</td>
            <td class="text-end">₹${Math.round(balance).toLocaleString("en-IN")}</td>
        </tr>`;
    }
    const amortBody = document.getElementById("amortizationBody");
    if (amortBody) amortBody.innerHTML = amortHtml;
}

function updateSliderBackground(slider) {
    const min = slider.min;
    const max = slider.max;
    const val = slider.value;
    const percent = ((val - min) / (max - min)) * 100;

    slider.style.background = `linear-gradient(to right, #2a6fb5 0%, #2a6fb5 ${percent}%, #d1d5db ${percent}%, #d1d5db 100%)`;
}

loanAmount.oninput = () => {
    calculateLoan();
    updateSliderBackground(loanAmount);
};

tenure.oninput = () => {
    calculateLoan();
    updateSliderBackground(tenure);
};

interestRate.oninput = () => {
    calculateLoan();
    updateSliderBackground(interestRate);
};

    calculateLoan();
    updateSliderBackground(loanAmount);
    updateSliderBackground(tenure);
    updateSliderBackground(interestRate);

    // Toggle amortization programmatically to bypass double-toggle bugs
    window.addEventListener('load', function () {
        const myCollapseEl = document.getElementById("amortization");
        const btn = document.getElementById("amortBtn");
        if (myCollapseEl && btn) {
            const bsCollapse = new bootstrap.Collapse(myCollapseEl, {
                toggle: false
            });
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const isShown = myCollapseEl.classList.contains('show');
                if (isShown) {
                    bsCollapse.hide();
                    btn.innerHTML = '<i class="fas fa-chart-line me-2"></i> View Amortization Schedule';
                } else {
                    bsCollapse.show();
                    btn.innerHTML = '<i class="fas fa-chart-line me-2"></i> Hide Amortization Schedule';
                }
            });
        }
    });

function downloadPDF() {
    if (!window.jspdf) {
        alert("PDF generator not loaded yet.");
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF("p", "mm", "a4");

    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();

    const primaryColor = [5, 83, 170];
    const darkText = [35, 45, 65];
    const blueText = [5, 83, 170];

    const logoImg = new Image();
    logoImg.crossOrigin = "anonymous";
    logoImg.src = "assets/images/waqt-pdf-logo1.png";

    function cleanText(text) {
        return String(text)
            .replace(/₹/g, "Rs.")
            .replace(/¹/g, "")
            .replace(/\s+/g, " ")
            .trim();
    }

    function addLogoImage(doc, img, x, y, w, h) {
        try {
            doc.addImage(img, "PNG", x, y, w, h);
        } catch(e) {
            try {
                doc.addImage(img, "JPEG", x, y, w, h);
            } catch(err) {}
        }
    }

    function drawPDF() {
        doc.setFillColor(primaryColor[0], primaryColor[1], primaryColor[2]);
        doc.rect(0, 0, pageWidth, 30, "F");

        doc.setTextColor(255, 255, 255);
        doc.setFont("helvetica", "bold");
        doc.setFontSize(19);
        doc.text("WAQT FINANCE", 12, 14);

        doc.setFont("helvetica", "normal");
        doc.setFontSize(9);
        doc.text("PAY DAY LOAN REPORT", 12, 24);

        doc.setDrawColor(145, 198, 255);
        doc.setLineWidth(1);
        doc.line(12, 18, 32, 18);

        try {
            doc.saveGraphicsState();
            doc.setGState(new doc.GState({ opacity: 0.055 }));
            addLogoImage(doc, logoImg, 33, 62, 145, 145);
            doc.restoreGraphicsState();
        } catch(e) {}

        let y = 48;

        doc.setFont("helvetica", "bold");
        doc.setFontSize(14);
        doc.setTextColor(darkText[0], darkText[1], darkText[2]);
        doc.text("Loan Summary Report", 12, y);

        doc.setDrawColor(primaryColor[0], primaryColor[1], primaryColor[2]);
        doc.setLineWidth(1);
        doc.line(12, y + 4, 34, y + 4);

        doc.setDrawColor(200, 210, 225);
        doc.setLineWidth(0.3);
        doc.line(12, y + 7, pageWidth - 12, y + 7);

        y += 18;

        function drawCard(x, y, w, h, label, value) {
            value = cleanText(value);

            doc.setFillColor(255, 255, 255);
            doc.setDrawColor(214, 220, 228);
            doc.setLineWidth(0.35);
            doc.roundedRect(x, y, w, h, 2.5, 2.5, "FD");

            doc.setTextColor(62, 65, 75);
            doc.setFont("helvetica", "normal");
            doc.setFontSize(8);
            doc.text(label, x + w / 2, y + 8, { align: "center" });

            doc.setTextColor(blueText[0], blueText[1], blueText[2]);
            doc.setFont("helvetica", "bold");
            doc.setFontSize(13);
            doc.text(value, x + w / 2, y + 18, { align: "center" });
        }

        const cardW = 86;
        const cardH = 24;
        const leftX = 12;
        const rightX = 112;
        const gapY = 34;

        drawCard(leftX, y, cardW, cardH, "Total Payable", emi.innerText);
        drawCard(rightX, y, cardW, cardH, "Principal Amount", principal.innerText);

        y += gapY;
        drawCard(leftX, y, cardW, cardH, "Total Interest", totalInterest.innerText);
        drawCard(rightX, y, cardW, cardH, "Total Payment", totalPayment.innerText);

        y += gapY;
        drawCard(leftX, y, cardW, cardH, "Loan Tenure", daysText.innerText);
        drawCard(rightX, y, cardW, cardH, "Interest Rate", rateText.innerText);

        y += 40;

        doc.setFillColor(235, 245, 255);
        doc.roundedRect(12, y, pageWidth - 24, 22, 2, 2, "F");

        doc.setFont("helvetica", "normal");
        doc.setFontSize(8);
        doc.setTextColor(45, 50, 60);
        doc.text("This document is a computer-generated financial summary for estimation purposes.", pageWidth / 2, y + 9, { align: "center" });
        doc.text("Please contact Waqt Finance for official loan approval and final terms.", pageWidth / 2, y + 16, { align: "center" });

        const footerY = pageHeight - 22;

        doc.setDrawColor(primaryColor[0], primaryColor[1], primaryColor[2]);
        doc.setLineWidth(0.8);
        doc.line(12, footerY - 12, pageWidth - 12, footerY - 12);

        const now = new Date().toLocaleString("en-IN", {
            day:"2-digit",
            month:"short",
            year:"numeric",
            hour:"2-digit",
            minute:"2-digit",
            hour12:true
        });

        doc.setFont("helvetica", "normal");
        doc.setFontSize(7);
        doc.setTextColor(65, 65, 65);

        doc.text("Generated on", 12, footerY);
        doc.text(now, 12, footerY + 5);
        doc.text("waqtfinance.com", pageWidth / 2, footerY + 3, { align: "center" });
        doc.text("Waqt Finance Private Limited", pageWidth - 12, footerY + 3, { align: "right" });

        doc.save("Waqt-Finance-Pay-Day-Loan-Report.pdf");
    }

    logoImg.onload = function () {
        drawPDF();
    };

    logoImg.onerror = function () {
        drawPDF();
    };
}

function shareWhatsApp() {
    let message = `Pay Day Loan Details

Loan Amount: ${principal.innerText}
Interest: ${totalInterest.innerText}
Total Payment: ${totalPayment.innerText}
Tenure: ${daysText.innerText}
Interest Rate: ${rateText.innerText}`;

    let url = "https://wa.me/?text=" + encodeURIComponent(message);
    window.open(url, "_blank");
}
</script>
