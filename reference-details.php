<?php
include 'includes/header.php';
include 'includes/main-header.php';
require_once __DIR__ . '/includes/loan_flow.php';

$application = loan_flow_require_application($conn);
$nextUrl = loan_flow_next_url($conn, $application);
if (!in_array(basename($nextUrl), ['reference-details.php', 'salary-slip-upload.php', 'application-status.php'], true)) {
    header('Location: ' . $nextUrl);
    exit;
}

$savedReferences = [
    'primary' => ['full_name' => '', 'mobile' => '', 'relation' => ''],
    'secondary' => ['full_name' => '', 'mobile' => '', 'relation' => ''],
];

if (loan_flow_table_exists($conn, 'loan_application_references')) {
    $stmt = $conn->prepare('SELECT reference_type, full_name, mobile, relation FROM loan_application_references WHERE application_id = ?');
    $stmt->bind_param('s', $application['application_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (isset($savedReferences[$row['reference_type']])) {
            $savedReferences[$row['reference_type']] = $row;
        }
    }
}

$relations = [
    'Father',
    'Mother',
    'Brother',
    'Sister',
    'Spouse',
    'Son',
    'Daughter',
    'Friend',
    'Relative',
    'Cousin',
    'Colleague',
    'Guarantor',
];

function ref_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<link rel="stylesheet" href="assets/css/loan-flow.css">

<div class="lf-page">
    <div class="lf-container">
        <div class="lf-progress">
            <div class="lf-progress-track">
                <div class="lf-step done">
                    <div class="lf-step-dot"><i class="fa-solid fa-check"></i></div>
                    <div class="lf-step-label">Basic<br>Details</div>
                </div>
                <div class="lf-step done">
                    <div class="lf-step-dot"><i class="fa-solid fa-check"></i></div>
                    <div class="lf-step-label">PAN<br>Verify</div>
                </div>
                <div class="lf-step done">
                    <div class="lf-step-dot"><i class="fa-solid fa-check"></i></div>
                    <div class="lf-step-label">Aadhaar<br>Verify</div>
                </div>
                <div class="lf-step done">
                    <div class="lf-step-dot"><i class="fa-solid fa-check"></i></div>
                    <div class="lf-step-label">Work<br>Details</div>
                </div>
                <div class="lf-step done">
                    <div class="lf-step-dot"><i class="fa-solid fa-check"></i></div>
                    <div class="lf-step-label">Bank<br>Details</div>
                </div>
                <div class="lf-step active">
                    <div class="lf-step-dot">6</div>
                    <div class="lf-step-label">References</div>
                </div>
                <div class="lf-step">
                    <div class="lf-step-dot">7</div>
                    <div class="lf-step-label">Upload<br>Docs</div>
                </div>
            </div>
        </div>

        <div class="lf-card">
            <div class="lf-card-header">
                <div class="lf-card-icon">
                    <i class="fa-solid fa-user-group"></i>
                </div>
                <h1 class="lf-card-title">Reference Details</h1>
                <p class="lf-card-sub">Add two contacts our verification team can reach if required.</p>
            </div>

            <div class="lf-card-body">
                <form id="referenceForm">
                    <?php foreach (['primary' => 'Reference 1', 'secondary' => 'Reference 2'] as $key => $title): ?>
                        <?php $reference = $savedReferences[$key]; ?>
                        <div class="lf-reference-block">
                            <div class="lf-reference-head">
                                <div class="lf-reference-number"><?= $key === 'primary' ? '1' : '2' ?></div>
                                <div>
                                    <h2><?= ref_e($title) ?></h2>
                                    <p><?= $key === 'primary' ? 'Primary reference contact' : 'Secondary reference contact' ?></p>
                                </div>
                            </div>

                            <div class="lf-form-group">
                                <label class="lf-label" for="<?= ref_e($key) ?>_name">Full Name <span class="lf-req">*</span></label>
                                <input type="text" id="<?= ref_e($key) ?>_name" class="lf-input" value="<?= ref_e($reference['full_name']) ?>" placeholder="Enter full name" autocomplete="name">
                                <small class="lf-error-text" id="<?= ref_e($key) ?>NameError"></small>
                            </div>

                            <div class="lf-form-group">
                                <label class="lf-label" for="<?= ref_e($key) ?>_mobile">Mobile Number <span class="lf-req">*</span></label>
                                <input type="tel" id="<?= ref_e($key) ?>_mobile" class="lf-input" value="<?= ref_e($reference['mobile']) ?>" placeholder="Enter 10-digit mobile number" maxlength="10" inputmode="numeric">
                                <small class="lf-error-text" id="<?= ref_e($key) ?>MobileError"></small>
                            </div>

                            <div class="lf-form-group">
                                <label class="lf-label" for="<?= ref_e($key) ?>_relation">Relation <span class="lf-req">*</span></label>
                                <div class="lf-select-wrap">
                                    <select id="<?= ref_e($key) ?>_relation" class="lf-select">
                                        <option value="">Select relation</option>
                                        <?php foreach ($relations as $relation): ?>
                                            <?php $label = $relation === 'Spouse' ? 'Spouse (Husband/Wife)' : $relation; ?>
                                            <option value="<?= ref_e($relation) ?>" <?= $reference['relation'] === $relation ? 'selected' : '' ?>>
                                                <?= ref_e($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <small class="lf-error-text" id="<?= ref_e($key) ?>RelationError"></small>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" class="lf-btn lf-btn-primary" id="referenceSubmitBtn">
                        Continue <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            </div>

            <div class="lf-trust">
                <div class="lf-trust-item"><i class="fa-solid fa-shield-halved"></i> Consent Based</div>
                <div class="lf-trust-item"><i class="fa-solid fa-lock"></i> Secure Storage</div>
            </div>
        </div>
    </div>
</div>

<script>
    const referenceForm = document.getElementById("referenceForm");
    const referenceSubmitBtn = document.getElementById("referenceSubmitBtn");
    const applicantMobile = "<?= ref_e(preg_replace('/\D/', '', (string) ($application['mobile'] ?? '')) ?? '') ?>";

    document.querySelectorAll("#referenceForm input[type='tel']").forEach((input) => {
        input.addEventListener("input", () => {
            input.value = input.value.replace(/\D/g, "").slice(0, 10);
        });
    });

    function clearReferenceErrors() {
        document.querySelectorAll(".lf-error-text").forEach((el) => el.innerText = "");
        document.querySelectorAll(".lf-input, .lf-select").forEach((el) => el.classList.remove("lf-error-border"));
    }

    function validateReference(prefix, label) {
        const name = document.getElementById(prefix + "_name");
        const mobile = document.getElementById(prefix + "_mobile");
        const relation = document.getElementById(prefix + "_relation");
        let valid = true;

        if (name.value.trim().length < 3) {
            document.getElementById(prefix + "NameError").innerText = label + " name is required";
            name.classList.add("lf-error-border");
            valid = false;
        }

        if (!/^[6-9]\d{9}$/.test(mobile.value.trim())) {
            document.getElementById(prefix + "MobileError").innerText = "Enter valid 10-digit mobile number";
            mobile.classList.add("lf-error-border");
            valid = false;
        }

        if (!relation.value) {
            document.getElementById(prefix + "RelationError").innerText = "Select relation";
            relation.classList.add("lf-error-border");
            valid = false;
        }

        return valid;
    }

    referenceForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        clearReferenceErrors();

        const primaryMobile = document.getElementById("primary_mobile").value.trim();
        const secondaryMobile = document.getElementById("secondary_mobile").value.trim();
        let valid = validateReference("primary", "Reference 1") && validateReference("secondary", "Reference 2");

        if (primaryMobile && secondaryMobile && primaryMobile === secondaryMobile) {
            document.getElementById("secondaryMobileError").innerText = "Reference numbers must be different";
            document.getElementById("secondary_mobile").classList.add("lf-error-border");
            valid = false;
        }

        if (applicantMobile && [primaryMobile, secondaryMobile].includes(applicantMobile)) {
            document.getElementById("primaryMobileError").innerText = "Reference number cannot be same as applicant";
            document.getElementById("secondaryMobileError").innerText = "Reference number cannot be same as applicant";
            valid = false;
        }

        if (!valid || referenceSubmitBtn.disabled) return;

        referenceSubmitBtn.disabled = true;
        referenceSubmitBtn.innerHTML = "<div class='lf-spinner'></div> Saving...";

        const formData = new FormData();
        ["primary", "secondary"].forEach((prefix) => {
            formData.append(prefix + "_name", document.getElementById(prefix + "_name").value.trim());
            formData.append(prefix + "_mobile", document.getElementById(prefix + "_mobile").value.trim());
            formData.append(prefix + "_relation", document.getElementById(prefix + "_relation").value);
        });

        try {
            const res = await fetch("api/apply/save_references.php", {
                method: "POST",
                headers: {
                    "X-CSRF-Token": window.WAQT_CSRF_TOKEN || ""
                },
                body: formData
            });
            const data = await res.json();

            if (data.status === "success") {
                window.location.href = "salary-slip-upload.php";
            } else {
                throw new Error(data.message || "Failed to save references");
            }
        } catch (error) {
            referenceSubmitBtn.disabled = false;
            referenceSubmitBtn.innerHTML = "Continue <i class='fa-solid fa-arrow-right'></i>";
            alert(error.message || "Network error");
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
