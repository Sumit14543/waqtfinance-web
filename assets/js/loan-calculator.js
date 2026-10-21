/* ============================
   LOAN CONFIG
============================ */
const loans = {
  personal: { min: 50000, max: 2500000, tenure: 60, rate: 12 },
  business: { min: 100000, max: 20000000, tenure: 60, rate: 16 },
  home: { min: 500000, max: 50000000, tenure: 360, rate: 9 },
  gold: { min: 50000, max: 10000000, tenure: 36, rate: 10 },
  vehicle: { min: 100000, max: 10000000, tenure: 84, rate: 10 }
};

let currentLoan = "personal";
let chart = null;

/* ============================
   HELPERS
============================ */
function fmt(v) {
  return "₹" + Math.round(v).toLocaleString("en-IN");
}

/* Slider fill */
function updateRangeFill(range) {
  const min = range.min ? range.min : 0;
  const max = range.max ? range.max : 100;
  const value = range.value;
  const percent = ((value - min) / (max - min)) * 100;

  range.style.background = `linear-gradient(
    to right,
    var(--primary-color) 0%,
    var(--primary-color) ${percent}%,
    #bdc9db ${percent}%,
    #c4d0ea 100%
  )`;
}


function updateAllRangeFills() {
  document.querySelectorAll(".form-range").forEach(r => updateRangeFill(r));
}

/* ============================
   CORE CALCULATION
============================ */
function calculate() {
  const P = +$("#amountRange").val();
  const n = +$("#tenureRange").val();
  const r = +$("#rateRange").val() / 12 / 100;

  const emi = P * r * Math.pow(1 + r, n) / (Math.pow(1 + r, n) - 1);
  const total = emi * n;
  const interest = total - P;

  $("#emi").text(fmt(emi));
  $("#principal").text(fmt(P));
  $("#interest").text(fmt(interest));
  $("#total").text(fmt(total));

  $("#amountText").text(fmt(P));
  $("#tenureText").text(n + " Months");
  $("#rateText").text($("#rateRange").val() + " %");

  /* Breakdown bar */
  const pPct = Math.round((P / total) * 100);
  const iPct = 100 - pPct;

  $("#pBar").css("width", pPct + "%");
  $("#iBar").css("width", iPct + "%");
  $("#pPct").text(pPct);
  $("#iPct").text(iPct);

  buildChart(P, interest);
  buildTable(P, r, n, emi);
  saveState();
}

/* ============================
   CHART
============================ */
function buildChart(p, i) {
  if (chart) {
    chart.data.datasets[0].data = [p, i];
    chart.update();
    return;
  }

  chart = new Chart(document.getElementById("chart"), {
    type: "doughnut",
    data: {
      labels: ["Principal", "Interest"],
      datasets: [{
        data: [p, i],
        backgroundColor: ["#6c757d", "#fbbd23"],
        borderWidth: 0
      }]
    },
    options: {
      cutout: "70%",
      plugins: { legend: { display: false } }
    }
  });
}

/* ============================
   AMORTIZATION TABLE
============================ */
function buildTable(P, r, n, emi) {
  let balance = P;
  let html = "";

  for (let i = 1; i <= n; i++) {
    const interest = balance * r;
    const principal = emi - interest;
    balance -= principal;

    html += `
      <tr>
        <td>${i}</td>
        <td>${fmt(emi)}</td>
        <td>${fmt(principal)}</td>
        <td>${fmt(interest)}</td>
        <td>${fmt(Math.max(0, balance))}</td>
      </tr>
    `;
  }

  $("#amortization").html(html);
}

/* ============================
   LOAN LOADER
============================ */
function loadLoan(type, preserve = false) {
  const l = loans[type];

  $("#amountRange").attr({ min: l.min, max: l.max });
  $("#tenureRange").attr({ min: 6, max: l.tenure });
  $("#rateRange").attr({ min: 7, max: 26 });

  if (!preserve) {
    $("#amountRange").val(l.min);
    $("#tenureRange").val(Math.floor(l.tenure / 2));
    $("#rateRange").val(l.rate);
  }

  syncInputs();
  updateAllRangeFills();
  calculate();
}

/* ============================
   INPUT SYNC
============================ */
function syncInputs() {
  $("#amountInput").val($("#amountRange").val());
  $("#tenureInput").val($("#tenureRange").val());
  $("#rateInput").val($("#rateRange").val());
}

/* ============================
   STATE SAVE / LOAD
============================ */
function saveState() {
  const state = {
    loan: currentLoan,
    amount: $("#amountRange").val(),
    tenure: $("#tenureRange").val(),
    rate: $("#rateRange").val()
  };
  localStorage.setItem("emiState", JSON.stringify(state));
}

/* 🔥 MAIN FIX HERE */
function loadState() {
  const saved = localStorage.getItem("emiState");
  if (!saved) {
    loadLoan(currentLoan);
    return;
  }

  const s = JSON.parse(saved);
  currentLoan = s.loan;

  /* Activate correct tab */
  $("#loanTabs button").removeClass("active");
  $(`#loanTabs button[data-loan="${currentLoan}"]`).addClass("active");

  /* Load loan WITHOUT resetting values */
  loadLoan(currentLoan, true);

  $("#amountRange").val(s.amount);
  $("#tenureRange").val(s.tenure);
  $("#rateRange").val(s.rate);

  syncInputs();
  updateAllRangeFills();
  calculate();
}

/* ============================
   EVENTS
============================ */
$("#loanTabs button").on("click", function () {
  $("#loanTabs button").removeClass("active");
  $(this).addClass("active");
  currentLoan = $(this).data("loan");
  loadLoan(currentLoan);
});

/* Manual inputs */
$("#amountInput, #tenureInput, #rateInput").on("input", function () {
  const rangeId = this.id.replace("Input", "Range");
  $("#" + rangeId).val(this.value);
  updateAllRangeFills();
  calculate();
});

/* Slider move */
$("#amountRange, #tenureRange, #rateRange").on("input", function () {
  syncInputs();
  updateRangeFill(this);
  calculate();
});

/* Download chart */
$("#downloadChart").on("click", function () {
  if (!chart) return;
  const a = document.createElement("a");
  a.href = chart.toBase64Image();
  a.download = "emi-chart.png";
  a.click();
});

/* ============================
   INIT
============================ */
document.addEventListener("DOMContentLoaded", function () {
  loadState();
});
