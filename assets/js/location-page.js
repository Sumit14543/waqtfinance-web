(function () {
    'use strict';
    function formatInr(value) { return new Intl.NumberFormat('en-IN', { maximumFractionDigits: 0 }).format(Math.round(value || 0)); }
    function calculate() {
        const amountStr = document.getElementById('locAmount')?.value || '0';
        const amount = Number(amountStr.replace(/\D/g, '') || 0);
        const annual = Number(document.getElementById('locRate')?.value || 0);
        const months = Number(document.getElementById('locTenure')?.value || 0);
        const result = document.getElementById('locEmiResult');
        if (!result) return;
        let emi = 0;
        if (amount > 0 && months > 0) {
            const rate = annual / 1200;
            emi = rate > 0 ? amount * rate * Math.pow(1 + rate, months) / (Math.pow(1 + rate, months) - 1) : amount / months;
        }
        result.querySelector('strong').textContent = '₹' + formatInr(emi);
        result.querySelector('small').textContent = 'Illustrative estimate only; review the applicable KFS and final terms.';
    }
    const locAmount = document.getElementById('locAmount');
    if (locAmount) {
        locAmount.addEventListener('input', function () {
            let digits = locAmount.value.replace(/\D/g, "").slice(0, 9);
            locAmount.value = digits ? Number(digits).toLocaleString("en-IN") : "";
        });
    }
    document.addEventListener('input', function (event) { if (event.target.matches('#locAmount,#locRate,#locTenure')) calculate(); });
    document.addEventListener('click', function (event) {
        const button = event.target.closest('.wf-location-faq-list button[aria-expanded]');
        if (!button) return;
        const list = button.closest('.wf-location-faq-list');
        const open = button.getAttribute('aria-expanded') === 'true';
        
        // Collapse all other panels in this FAQ section
        const buttons = list.querySelectorAll('button[aria-expanded]');
        buttons.forEach(btn => {
            if (btn !== button) {
                btn.setAttribute('aria-expanded', 'false');
                const pnl = document.getElementById(btn.getAttribute('aria-controls'));
                if (pnl) pnl.hidden = true;
                const art = btn.closest('article');
                if (art) art.classList.remove('open');
            }
        });
        
        // Toggle the clicked panel state
        button.setAttribute('aria-expanded', String(!open));
        const panel = document.getElementById(button.getAttribute('aria-controls'));
        if (panel) panel.hidden = open;
        button.closest('article').classList.toggle('open', !open);
    });
    calculate();
})();
