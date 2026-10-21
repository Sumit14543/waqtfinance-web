<?php
declare(strict_types=1);

/**
 * Aadhaar DigiLocker e-KYC Verification Portal
 * Route: /aadhaar-verification.php
 * Powered by Bifrost / Unifers AI & UIDAI DigiLocker
 */

require_once __DIR__ . '/config/db.php';

$pageTitle = 'Aadhaar DigiLocker e-KYC Verification | Waqt Finance';
$aadhaarError = $_SESSION['aadhaar_error'] ?? '';
unset($_SESSION['aadhaar_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --brand-primary: #004c8f;
            --brand-secondary: #008080;
            --brand-gradient: linear-gradient(135deg, #004c8f 0%, #008080 100%);
            --brand-accent: #f59e0b;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-500: #64748b;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;
            --success: #16a34a;
            --error: #dc2626;
            --radius-xl: 20px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 50%, #f8fafc 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 14px;
            color: var(--slate-900);
        }

        .kyc-wrapper {
            width: 100%;
            max-width: 480px;
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 76, 143, 0.2), 0 0 0 1px rgba(0, 76, 143, 0.06);
            overflow: hidden;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .kyc-top-bar {
            background: var(--brand-gradient);
            padding: 28px 22px 22px;
            text-align: center;
            color: #ffffff;
            position: relative;
        }

        .brand-logo-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: #ffffff;
            padding: 3px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.22);
            margin-bottom: 12px;
        }

        .brand-logo-pill img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
        }

        .trust-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(8px);
            font-size: 11px;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .kyc-title {
            font-size: 21px;
            font-weight: 900;
            letter-spacing: -0.4px;
        }

        .kyc-desc {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.88);
            margin-top: 4px;
        }

        /* 3-Step Progress Tracker */
        .step-tracker {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
            padding: 12px 20px;
            background: #f8fafc;
            border-bottom: 1px solid var(--slate-200);
            font-size: 11px;
            text-align: center;
        }

        .step-item {
            color: var(--slate-500);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .step-item.active {
            color: var(--brand-primary);
            font-weight: 800;
        }

        .step-num {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--slate-200);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 800;
        }

        .step-item.active .step-num {
            background: var(--brand-primary);
            color: #ffffff;
        }

        .kyc-body {
            padding: 24px 22px;
        }

        .alert-error {
            background: #fef2f2;
            border: 1.5px solid #fca5a5;
            color: #991b1b;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 800;
            color: var(--slate-700);
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--brand-primary);
            font-size: 16px;
        }

        .aadhaar-input {
            width: 100%;
            height: 52px;
            padding: 0 14px 0 44px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 17px;
            font-weight: 700;
            letter-spacing: 2px;
            border: 2px solid var(--slate-200);
            border-radius: 14px;
            background: var(--slate-50);
            color: var(--slate-900);
            transition: all 0.2s ease;
        }

        .aadhaar-input:focus {
            outline: none;
            border-color: var(--brand-primary);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(0, 76, 143, 0.12);
        }

        .aadhaar-input.valid {
            border-color: var(--success);
            background: #f0fdf4;
        }

        .aadhaar-input.invalid {
            border-color: var(--error);
            background: #fef2f2;
        }

        .input-hint {
            font-size: 11px;
            color: var(--slate-500);
            margin-top: 6px;
            display: flex;
            justify-content: space-between;
        }

        .btn-submit {
            width: 100%;
            height: 50px;
            background: var(--brand-gradient);
            color: #ffffff;
            border: none;
            border-radius: 14px;
            font-size: 14.5px;
            font-weight: 800;
            letter-spacing: 0.2px;
            cursor: pointer;
            box-shadow: 0 10px 20px -3px rgba(0, 76, 143, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 14px 26px -3px rgba(0, 76, 143, 0.45);
        }

        .btn-submit:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        .digilocker-info-card {
            background: #f8fafc;
            border: 1px solid var(--slate-200);
            border-radius: 14px;
            padding: 12px 14px;
            margin-top: 18px;
            font-size: 11.5px;
            color: var(--slate-700);
            line-height: 1.45;
        }

        .digilocker-info-title {
            font-weight: 800;
            color: var(--slate-900);
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .trust-footer {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 18px;
            padding-top: 14px;
            border-top: 1px dashed var(--slate-200);
            font-size: 11px;
            color: var(--slate-500);
        }

        /* Loading Overlay */
        .loading-overlay {
            display: none;
            text-align: center;
            padding: 24px 10px;
        }
        .spinner {
            font-size: 34px;
            color: var(--brand-primary);
            animation: spin 0.8s linear infinite;
            margin-bottom: 12px;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<div class="kyc-wrapper">
    <div class="kyc-top-bar">
        <div class="brand-logo-pill">
            <img src="assets/images/logo1.webp" alt="Waqt Finance">
        </div>
        <div class="trust-pill">
            <i class="fa-solid fa-building-columns"></i> Government DigiLocker e-KYC
        </div>
        <h1 class="kyc-title">Verify Aadhaar Online</h1>
        <p class="kyc-desc">Instant paperless verification via UIDAI &amp; DigiLocker Gateway</p>
    </div>

    <div class="step-tracker">
        <div class="step-item active">
            <span class="step-num">1</span> Aadhaar
        </div>
        <div class="step-item">
            <span class="step-num">2</span> DigiLocker
        </div>
        <div class="step-item">
            <span class="step-num">3</span> Verified
        </div>
    </div>

    <div class="kyc-body">
        <?php if (!empty($aadhaarError)): ?>
            <div class="alert-error" id="serverErrorAlert">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?= htmlspecialchars($aadhaarError, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <div id="formStateWrap">
            <form id="aadhaarForm" action="aadhaar_initiate.php" method="POST">
                <div class="form-group">
                    <label class="form-label" for="aadhaar">
                        <span>Aadhaar Card Number <span style="color:var(--error);">*</span></span>
                        <span id="aadhaarStatusPill" style="font-weight:700; color:var(--slate-500);">12 Digits</span>
                    </label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-fingerprint input-icon"></i>
                        <input
                            type="tel"
                            id="aadhaar"
                            name="aadhaar"
                            class="aadhaar-input"
                            placeholder="XXXX XXXX XXXX"
                            maxlength="14"
                            inputmode="numeric"
                            autocomplete="off"
                            required
                            autofocus
                        >
                    </div>
                    <div class="input-hint">
                        <span id="validationMsg">Enter 12 digits as on your Aadhaar card</span>
                        <span id="digitCount">0/12</span>
                    </div>
                </div>

                <button type="submit" id="btnSubmit" class="btn-submit" disabled>
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Authorize via DigiLocker
                </button>
            </form>

            <div class="digilocker-info-card">
                <div class="digilocker-info-title">
                    <i class="fa-solid fa-shield-halved" style="color:var(--brand-secondary);"></i> Official DigiLocker Consent Flow
                </div>
                You will be securely redirected to the Government DigiLocker portal to enter OTP and grant consent. Upon success, verified name, address, and e-KYC will be synchronized instantly.
            </div>
        </div>

        <!-- Loading State during Initiation -->
        <div id="loadingStateWrap" class="loading-overlay">
            <div class="spinner">
                <i class="fa-solid fa-circle-notch"></i>
            </div>
            <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin-bottom:4px;">Connecting to DigiLocker</h3>
            <p style="font-size:12px; color:var(--slate-500);">Generating secure UIDAI session reference... Please wait.</p>
        </div>

        <div class="trust-footer">
            <span><i class="fa-solid fa-lock"></i> 256-Bit SSL Encrypted</span>
            <span><i class="fa-solid fa-check"></i> UIDAI Compliant</span>
            <span><i class="fa-solid fa-bolt"></i> Instant 10-Sec e-KYC</span>
        </div>
    </div>
</div>

<script>
    const input = document.getElementById('aadhaar');
    const submitBtn = document.getElementById('btnSubmit');
    const digitCount = document.getElementById('digitCount');
    const validationMsg = document.getElementById('validationMsg');
    const statusPill = document.getElementById('aadhaarStatusPill');
    const form = document.getElementById('aadhaarForm');
    const formWrap = document.getElementById('formStateWrap');
    const loadingWrap = document.getElementById('loadingStateWrap');

    // UIDAI Verhoeff Checksum Validation
    function isValidVerhoeff(num) {
        const d = [
            [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
            [1, 2, 3, 4, 0, 6, 7, 8, 9, 5],
            [2, 3, 4, 0, 1, 7, 8, 9, 5, 6],
            [3, 4, 0, 1, 2, 8, 9, 5, 6, 7],
            [4, 0, 1, 2, 3, 9, 5, 6, 7, 8],
            [5, 9, 8, 7, 6, 0, 4, 3, 2, 1],
            [6, 5, 9, 8, 7, 1, 0, 4, 3, 2],
            [7, 6, 5, 9, 8, 2, 1, 0, 4, 3],
            [8, 7, 6, 5, 9, 3, 2, 1, 0, 4],
            [9, 8, 7, 6, 5, 4, 3, 2, 1, 0]
        ];
        const p = [
            [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
            [1, 5, 7, 6, 2, 8, 3, 0, 9, 4],
            [5, 8, 0, 3, 7, 9, 6, 1, 4, 2],
            [8, 9, 1, 6, 0, 4, 3, 5, 2, 7],
            [9, 4, 5, 3, 1, 2, 6, 8, 7, 0],
            [4, 2, 8, 6, 5, 7, 3, 9, 0, 1],
            [2, 7, 9, 3, 8, 0, 6, 4, 1, 5],
            [7, 0, 4, 6, 9, 1, 3, 2, 5, 8]
        ];
        let c = 0;
        const digits = num.split('').reverse().map(Number);
        for (let i = 0; i < digits.length; i++) {
            c = d[c][p[i % 8][digits[i]]];
        }
        return c === 0;
    }

    // Auto-Format with spaces: XXXX XXXX XXXX
    input.addEventListener('input', function() {
        let raw = this.value.replace(/\D/g, '').slice(0, 12);
        this.value = raw.replace(/(\d{4})(?=\d)/g, '$1 ');

        digitCount.innerText = `${raw.length}/12`;

        if (raw.length === 12) {
            if (isValidVerhoeff(raw)) {
                this.classList.remove('invalid');
                this.classList.add('valid');
                submitBtn.disabled = false;
                statusPill.innerText = 'Valid Format ✓';
                statusPill.style.color = 'var(--success)';
                validationMsg.innerText = 'Valid Aadhaar checksum. Ready to verify.';
                validationMsg.style.color = 'var(--success)';
            } else {
                this.classList.remove('valid');
                this.classList.add('invalid');
                submitBtn.disabled = true;
                statusPill.innerText = 'Invalid Number ✕';
                statusPill.style.color = 'var(--error)';
                validationMsg.innerText = 'Invalid Aadhaar checksum. Please re-check numbers.';
                validationMsg.style.color = 'var(--error)';
            }
        } else {
            this.classList.remove('valid', 'invalid');
            submitBtn.disabled = true;
            statusPill.innerText = `${raw.length}/12 Digits`;
            statusPill.style.color = 'var(--slate-500)';
            validationMsg.innerText = 'Enter 12 digits as on your Aadhaar card';
            validationMsg.style.color = 'var(--slate-500)';
        }
    });

    // AJAX Form Submission for seamless animated transition
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const raw = input.value.replace(/\D/g, '');
        if (raw.length !== 12 || !isValidVerhoeff(raw)) return;

        formWrap.style.display = 'none';
        loadingWrap.style.display = 'block';

        fetch('aadhaar_initiate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ aadhaar: raw })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.authorizationUrl) {
                // Redirect user to DigiLocker authorization URL
                window.location.href = data.authorizationUrl;
            } else {
                alert(data.message || 'Verification initiation failed. Please try again.');
                loadingWrap.style.display = 'none';
                formWrap.style.display = 'block';
            }
        })
        .catch(err => {
            console.error(err);
            // Fallback to normal form submit
            form.submit();
        });
    });
</script>

</body>
</html>
