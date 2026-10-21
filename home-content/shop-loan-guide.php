
<head>


<style>
 
  .slg-container {
    max-width: 1000px;
    margin: auto;
  }

  .slg-header {
    text-align: center;
    margin-bottom: 35px;
  }

  .slg-header-title {
    font-size: 30px;
    font-weight: 700;
  }

  .slg-header-subtitle {
    margin-top: 6px;
    color: #6b7280;
    font-size: 14px;
  }

  .slg-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 18px;
  }

  .slg-card {
    background: #fff;
    border-radius: 16px;
    padding: 18px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    border: 1px solid #eef2ff;
    transition: 0.2s ease;
  }

  .slg-card:hover {
    transform: translateY(-4px);
  }

  .slg-card-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 12px;
  }

  .slg-card-title i {
    color: #4f46e5;
  }

  .slg-list {
    list-style: none;
  }

  .slg-list li {
    padding: 7px 0;
    font-size: 14px;
    color: #374151;
    display: flex;
    gap: 8px;
  }

  .slg-list li i {
    color: #22c55e;
    font-size: 12px;
    margin-top: 3px;
  }

  .slg-table {
    width: 100%;
    font-size: 14px;
  }

  .slg-table td {
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
  }

  .slg-table td:first-child {
    font-weight: 600;
    color: #111827;
  }

  .slg-badge {
    display: inline-block;
    background: #eef2ff;
    color: #4338ca;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 999px;
    margin-bottom: 10px;
  }

  .slg-note {
    margin-top: 25px;
    background: #fff7ed;
    border-left: 4px solid #f59e0b;
    padding: 14px;
    border-radius: 12px;
    font-size: 13px;
    color: #7c2d12;
  }
</style>
</head>

<body>

<div class="slg-container">

  <div class="slg-header">
    <div class="slg-header-title">Shop Loan Guide</div>
    <div class="slg-header-subtitle">
      Complete information about fees, eligibility & application process
    </div>
  </div>

  <div class="slg-grid">

    <!-- FEES -->
    <div class="slg-card">
      <div class="slg-card-title">
        <i class="fa-solid fa-coins"></i> Fees & Interest Rates
      </div>

      <table class="slg-table">
        <tr><td>Processing Fee</td><td>1% – 5%</td></tr>
        <tr><td>Interest Rate</td><td>5% – 30%</td></tr>
        <tr><td>Closing Charges</td><td>2% – 5%</td></tr>
        <tr><td>Monthly Fee</td><td>0.025% – 0.075%</td></tr>
      </table>
    </div>

    <!-- APPLY -->
    <div class="slg-card">
      <div class="slg-card-title">
        <i class="fa-solid fa-file-pen"></i> How to Apply
      </div>

      <ul class="slg-list">
        <li><i class="fa-solid fa-check"></i> Compare lenders</li>
        <li><i class="fa-solid fa-check"></i> Choose loan type</li>
        <li><i class="fa-solid fa-check"></i> Fill online form</li>
        <li><i class="fa-solid fa-check"></i> Upload documents</li>
        <li><i class="fa-solid fa-check"></i> Get approval</li>
      </ul>
    </div>

    <!-- ELIGIBILITY -->
    <div class="slg-card">
      <div class="slg-card-title">
        <i class="fa-solid fa-user-check"></i> Eligibility
      </div>

      <ul class="slg-list">
        <li><i class="fa-solid fa-check"></i> Age: 21–65 years</li>
        <li><i class="fa-solid fa-check"></i> Indian citizen</li>
        <li><i class="fa-solid fa-check"></i> Credit score 650+</li>
        <li><i class="fa-solid fa-check"></i> 1+ year business</li>
      </ul>
    </div>

    <!-- DOCUMENTS -->
    <div class="slg-card">
      <div class="slg-card-title">
        <i class="fa-solid fa-folder-open"></i> Documents
      </div>

      <span class="slg-badge">Required</span>

      <ul class="slg-list">
        <li><i class="fa-solid fa-check"></i> PAN & Aadhaar</li>
        <li><i class="fa-solid fa-check"></i> Address proof</li>
        <li><i class="fa-solid fa-check"></i> Bank statements</li>
        <li><i class="fa-solid fa-check"></i> GST / Shop Act</li>
      </ul>
    </div>

    <!-- PROVIDER -->
    <div class="slg-card">
      <div class="slg-card-title">
        <i class="fa-solid fa-building-columns"></i> Loan Provider
      </div>

      <p style="font-size:14px; color:#374151; line-height:1.5;">
        <b>FlexiLoans</b> offers collateral-free shop loans up to ₹50 Lakh with fast approval within 72 hours.
      </p>
    </div>

  </div>


</div>

</body>
</html>