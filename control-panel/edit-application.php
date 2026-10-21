<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

start_secure_session();

// Auth check
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$conn = db();

function admin_edit_existing_payday_aadhaar(mysqli $conn, int $id): string
{
    if ($id <= 0) {
        return '';
    }

    $stmt = $conn->prepare('SELECT aadhaar_number FROM loan_applications WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return '';
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    return (string) ($row['aadhaar_number'] ?? '');
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    require_valid_csrf();
    
    $id = (int)($_POST['id'] ?? 0);
    $type = trim((string)($_POST['type'] ?? 'payday'));
    
    if (!in_array($type, ['payday', 'wizard'], true)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid application type.']);
        exit;
    }
    
    // Read state parameters to build redirect path
    $backPage = (int)($_POST['page'] ?? 1);
    $backSearch = trim((string)($_POST['search'] ?? ''));
    $backStatus = trim((string)($_POST['status_filter'] ?? ''));
    $backDateFrom = trim((string)($_POST['date_from'] ?? ''));
    $backDateTo = trim((string)($_POST['date_to'] ?? ''));
    $backAmountMin = trim((string)($_POST['amount_min'] ?? ''));
    $backAmountMax = trim((string)($_POST['amount_max'] ?? ''));
    $backLoanType = trim((string)($_POST['loan_type_filter'] ?? ''));

    $backQuery = [
        'tab' => $type,
        'page' => $backPage,
        'search' => $backSearch,
        'status' => $backStatus,
        'date_from' => $backDateFrom,
        'date_to' => $backDateTo,
        'amount_min' => $backAmountMin,
        'amount_max' => $backAmountMax,
        'loan_type' => $backLoanType,
    ];
    $backParams = http_build_query(array_filter($backQuery, static fn($value) => $value !== '' && $value !== null));
    $redirectUrl = "applications.php?{$backParams}";
    
    if ($type === 'payday') {
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $mobile = trim((string) ($_POST['mobile'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $dob = !empty($_POST['dob']) ? trim((string)$_POST['dob']) : null;
        $panNumber = strtoupper(trim((string)$_POST['pan_number'] ?? ''));
        $uanNumber = trim((string)($_POST['uan_number'] ?? ''));
        $aadhaarInput = trim((string)($_POST['aadhaar_number'] ?? ''));
        $education = trim((string)($_POST['education'] ?? ''));
        $experienceYears = !empty($_POST['experience_years']) ? (int)$_POST['experience_years'] : null;
        $employmentStatus = trim((string)($_POST['employment_status'] ?? ''));
        $companyName = trim((string)($_POST['company_name'] ?? ''));
        $designation = trim((string)($_POST['designation'] ?? ''));
        $monthlyIncome = (float) ($_POST['monthly_income'] ?? 0.0);
        $incomeReceivedIn = trim((string)($_POST['income_received_in'] ?? ''));
        $officeEmail = trim((string)($_POST['office_email'] ?? ''));
        $officeAddress = trim((string)($_POST['office_address'] ?? ''));
        $officePincode = trim((string)($_POST['office_pincode'] ?? ''));
        $salaryDay = !empty($_POST['salary_day']) ? (int)$_POST['salary_day'] : null;
        $bankName = trim((string)($_POST['bank_name'] ?? ''));
        $branchName = trim((string)($_POST['branch_name'] ?? ''));
        $accountHolder = trim((string)($_POST['account_holder'] ?? ''));
        $accountNumber = trim((string)($_POST['account_number'] ?? ''));
        $ifscCode = strtoupper(trim((string)($_POST['ifsc_code'] ?? '')));
        $loanAmount = (float) ($_POST['loan_amount'] ?? 0.0);
        $loanPurpose = trim((string) ($_POST['loan_purpose'] ?? ''));
        $hasRunningLoan = isset($_POST['has_running_loan']) ? (int)$_POST['has_running_loan'] : 0;
        $status = trim((string) ($_POST['status'] ?? 'draft'));
        $cibilReportUrl = trim((string) ($_POST['cibil_report_url'] ?? ''));
        $videoKyc = trim((string) ($_POST['video_kyc'] ?? ''));
        $city = trim((string) ($_POST['city'] ?? ''));
        $pincode = trim((string) ($_POST['pincode'] ?? ''));

        // Audited references
        $reference1Name = trim((string)($_POST['reference1_name'] ?? ''));
        $reference1Relation = trim((string)($_POST['reference1_relation'] ?? ''));
        $reference1Mobile = trim((string)($_POST['reference1_mobile'] ?? ''));
        $reference2Name = trim((string)($_POST['reference2_name'] ?? ''));
        $reference2Relation = trim((string)($_POST['reference2_relation'] ?? ''));
        $reference2Mobile = trim((string)($_POST['reference2_mobile'] ?? ''));

        // Audited document paths
        $selfieImage = trim((string)($_POST['selfie_image'] ?? ''));
        $companyIdCard = trim((string)($_POST['company_id_card'] ?? ''));
        $salarySlipCurrent = trim((string)($_POST['salary_slip_current'] ?? ''));
        $salarySlipPrevious = trim((string)($_POST['salary_slip_previous'] ?? ''));
        $salarySlipOld = trim((string)($_POST['salary_slip_old'] ?? ''));

        // Audited status, agents, metadata
        $aadhaarVerified = isset($_POST['aadhaar_verified']) ? (int)$_POST['aadhaar_verified'] : 0;
        $aadhaarUniqueId = trim((string)($_POST['aadhaar_unique_id'] ?? ''));
        $priority = trim((string)($_POST['priority'] ?? 'medium'));
        $assignedTo = trim((string)($_POST['assigned_to'] ?? ''));
        $source = trim((string)($_POST['source'] ?? 'Portal Direct'));
        $externalApplicationId = trim((string)($_POST['external_application_id'] ?? ''));
        $sourceSystem = trim((string)($_POST['source_system'] ?? ''));
        $sourceLeadId = trim((string)($_POST['source_lead_id'] ?? ''));
        $sourceApplicationId = trim((string)($_POST['source_application_id'] ?? ''));
        $sourceStatus = trim((string)($_POST['source_status'] ?? ''));

        if ($mobile === '') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Mobile number is required.']);
            exit;
        }

        $aadhaarNumber = waqt_prepare_aadhaar_for_storage(
            $aadhaarInput,
            admin_edit_existing_payday_aadhaar($conn, $id)
        );
        if ($aadhaarInput !== '' && $aadhaarNumber === '') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid Aadhaar number.']);
            exit;
        }

        $validStatuses = ['draft', 'submitted', 'review', 'approved', 'rejected', 'disbursed'];
        if (!in_array($status, $validStatuses, true)) {
            $status = 'draft';
        }

        if ($id > 0) {
            // Update 52 fields
            $stmt = $conn->prepare("UPDATE loan_applications SET 
                full_name = ?, mobile = ?, email = ?, dob = ?, pan_number = ?, 
                uan_number = ?, aadhaar_number = ?, education = ?, experience_years = ?, employment_status = ?, 
                company_name = ?, designation = ?, monthly_income = ?, income_received_in = ?, office_email = ?, 
                office_address = ?, office_pincode = ?, salary_day = ?, bank_name = ?, branch_name = ?, 
                account_holder = ?, account_number = ?, ifsc_code = ?, loan_amount = ?, loan_purpose = ?, 
                has_running_loan = ?, status = ?, cibil_report_url = ?, video_kyc = ?, city = ?, 
                pincode = ?, reference1_name = ?, reference1_relation = ?, reference1_mobile = ?, reference2_name = ?, 
                reference2_relation = ?, reference2_mobile = ?, selfie_image = ?, company_id_card = ?, salary_slip_current = ?, 
                salary_slip_previous = ?, salary_slip_old = ?, aadhaar_verified = ?, aadhaar_unique_id = ?, priority = ?, 
                assigned_to = ?, source = ?, external_application_id = ?, source_system = ?, source_lead_id = ?, 
                source_application_id = ?, source_status = ?, updated_at = NOW() 
                WHERE id = ?");
            
            $stmt->bind_param('ssssssssissssdsssissssssdsissssssssssssssisssssssi', 
                $fullName, $mobile, $email, $dob, $panNumber, 
                $uanNumber, $aadhaarNumber, $education, $experienceYears, $employmentStatus, 
                $companyName, $designation, $monthlyIncome, $incomeReceivedIn, $officeEmail, 
                $officeAddress, $officePincode, $salaryDay, $bankName, $branchName, 
                $accountHolder, $accountNumber, $ifscCode, $loanAmount, $loanPurpose, 
                $hasRunningLoan, $status, $cibilReportUrl, $videoKyc, $city, 
                $pincode, $reference1Name, $reference1Relation, $reference1Mobile, $reference2Name, 
                $reference2Relation, $reference2Mobile, $selfieImage, $companyIdCard, $salarySlipCurrent, 
                $salarySlipPrevious, $salarySlipOld, $aadhaarVerified, $aadhaarUniqueId, $priority, 
                $assignedTo, $source, $externalApplicationId, $sourceSystem, $sourceLeadId, 
                $sourceApplicationId, $sourceStatus, $id);
        } else {
            // Create
            $appId = 'WAQTFN-PD-' . mt_rand(1000000000, 9999999999);
            $stmt = $conn->prepare("INSERT INTO loan_applications (
                application_id, full_name, mobile, email, dob, pan_number, 
                uan_number, aadhaar_number, education, experience_years, employment_status, 
                company_name, designation, monthly_income, income_received_in, office_email, 
                office_address, office_pincode, salary_day, bank_name, branch_name, 
                account_holder, account_number, ifsc_code, loan_amount, loan_purpose, 
                has_running_loan, status, cibil_report_url, video_kyc, city, 
                pincode, reference1_name, reference1_relation, reference1_mobile, reference2_name, 
                reference2_relation, reference2_mobile, selfie_image, company_id_card, salary_slip_current, 
                salary_slip_previous, salary_slip_old, aadhaar_verified, aadhaar_unique_id, priority, 
                assigned_to, source, external_application_id, source_system, source_lead_id, 
                source_application_id, source_status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            
            $stmt->bind_param('ssssssssssissssdsssissssssdsissssssssssssssisssssss', 
                $appId, $fullName, $mobile, $email, $dob, $panNumber, 
                $uanNumber, $aadhaarNumber, $education, $experienceYears, $employmentStatus, 
                $companyName, $designation, $monthlyIncome, $incomeReceivedIn, $officeEmail, 
                $officeAddress, $officePincode, $salaryDay, $bankName, $branchName, 
                $accountHolder, $accountNumber, $ifscCode, $loanAmount, $loanPurpose, 
                $hasRunningLoan, $status, $cibilReportUrl, $videoKyc, $city, 
                $pincode, $reference1Name, $reference1Relation, $reference1Mobile, $reference2Name, 
                $reference2Relation, $reference2Mobile, $selfieImage, $companyIdCard, $salarySlipCurrent, 
                $salarySlipPrevious, $salarySlipOld, $aadhaarVerified, $aadhaarUniqueId, $priority, 
                $assignedTo, $source, $externalApplicationId, $sourceSystem, $sourceLeadId, 
                $sourceApplicationId, $sourceStatus);
        }

        if ($stmt->execute()) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Payday application saved successfully.', 'redirect' => $redirectUrl]);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Failed to save payday application: ' . $stmt->error]);
            exit;
        }
    } else {
        // Wizard Update
        if ($id <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'New wizard application creation is not supported.']);
            exit;
        }
        
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $mobile = trim((string) ($_POST['mobile'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $loanType = trim((string) ($_POST['loan_type'] ?? ''));
        $loanAmount = trim((string) ($_POST['loan_amount'] ?? ''));
        $loanTenure = trim((string) ($_POST['loan_tenure'] ?? ''));
        $loanPurpose = trim((string) ($_POST['loan_purpose'] ?? ''));
        $dob = trim((string) ($_POST['dob'] ?? ''));
        $gender = trim((string) ($_POST['gender'] ?? ''));
        $panNumber = trim((string) ($_POST['pan_number'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));
        $city = trim((string) ($_POST['city'] ?? ''));
        $state = trim((string) ($_POST['state'] ?? ''));
        $pincode = trim((string) ($_POST['pincode'] ?? ''));
        $employmentType = trim((string) ($_POST['employment_type'] ?? ''));
        $companyName = trim((string) ($_POST['company_name'] ?? ''));
        $designation = trim((string) ($_POST['designation'] ?? ''));
        $monthlyIncome = trim((string) ($_POST['monthly_income'] ?? ''));
        $existingEmi = trim((string) ($_POST['existing_emi'] ?? ''));
        $bankName = trim((string) ($_POST['bank_name'] ?? ''));
        $accountNumber = trim((string) ($_POST['account_number'] ?? ''));
        $ifscCode = trim((string) ($_POST['ifsc_code'] ?? ''));

        // Audited Wizard Consents
        $consentTerms = isset($_POST['consent_terms']) ? 1 : 0;
        $consentCredit = isset($_POST['consent_credit']) ? 1 : 0;
        $consentComm = isset($_POST['consent_communication']) ? 1 : 0;
        $consentsJson = json_encode(['terms' => $consentTerms, 'credit' => $consentCredit, 'communication' => $consentComm]);

        if ($mobile === '') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Mobile number is required.']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE apply_loan_applications SET 
            full_name = ?, mobile = ?, email = ?, loan_type = ?, loan_amount = ?, 
            loan_tenure = ?, loan_purpose = ?, dob = ?, gender = ?, pan_number = ?, 
            address = ?, city = ?, state = ?, pincode = ?, employment_type = ?, 
            company_name = ?, designation = ?, monthly_income = ?, existing_emi = ?, bank_name = ?, 
            account_number = ?, ifsc_code = ?, consents = ? 
            WHERE id = ?");
        
        $stmt->bind_param('sssssssssssssssssssssssi', 
            $fullName, $mobile, $email, $loanType, $loanAmount, 
            $loanTenure, $loanPurpose, $dob, $gender, $panNumber, 
            $address, $city, $state, $pincode, $employmentType, 
            $companyName, $designation, $monthlyIncome, $existingEmi, $bankName, 
            $accountNumber, $ifscCode, $consentsJson, $id
        );

        if ($stmt->execute()) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Wizard application updated successfully.', 'redirect' => $redirectUrl]);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Failed to update wizard application: ' . $stmt->error]);
            exit;
        }
    }
}

$id = (int)($_GET['id'] ?? 0);
$type = trim((string)($_GET['type'] ?? 'payday'));

if (!in_array($type, ['payday', 'wizard'], true)) {
    $type = 'payday';
}

$backPage = (int)($_GET['page'] ?? 1);
$backSearch = trim((string)($_GET['search'] ?? ''));
$backStatus = trim((string)($_GET['status'] ?? ''));
$backDateFrom = trim((string)($_GET['date_from'] ?? ''));
$backDateTo = trim((string)($_GET['date_to'] ?? ''));
$backAmountMin = trim((string)($_GET['amount_min'] ?? ''));
$backAmountMax = trim((string)($_GET['amount_max'] ?? ''));
$backLoanType = trim((string)($_GET['loan_type'] ?? ''));

$backQuery = [
    'tab' => $type,
    'page' => $backPage,
    'search' => $backSearch,
    'status' => $backStatus,
    'date_from' => $backDateFrom,
    'date_to' => $backDateTo,
    'amount_min' => $backAmountMin,
    'amount_max' => $backAmountMax,
    'loan_type' => $backLoanType,
];
$backParams = http_build_query(array_filter($backQuery, static fn($value) => $value !== '' && $value !== null));

$app = null;
if ($id > 0) {
    if ($type === 'payday') {
        $stmt = $conn->prepare("SELECT * FROM loan_applications WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $app = $stmt->get_result()->fetch_assoc();
    } else {
        $stmt = $conn->prepare("SELECT * FROM apply_loan_applications WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $app = $stmt->get_result()->fetch_assoc();
    }
    if (!$app) {
        $pageTitle = 'Application Not Found';
        include 'header.php';
        echo '<div style="padding: 30px;"><div class="badge-status badge-rejected" style="font-size:16px; padding:15px; display:block; text-align:center;">Application not found.</div></div>';
        include 'footer.php';
        exit;
    }
}

$displayId = '';
if ($id > 0 && $app) {
    $displayId = $type === 'payday' ? ($app['application_id'] ?? 'N/A') : ($app['application_ref'] ?? 'N/A');
} else {
    $displayId = 'NEW';
}

$aadhaarDisplayValue = '';
if ($type === 'payday' && $app) {
    $aadhaarDisplayValue = waqt_mask_stored_sensitive_value((string)($app['aadhaar_number'] ?? ''), 0, 4);
}

$pageTitle = ($id > 0) ? 'Edit Application Details' : 'Create Application';
$pageSubtitle = ($id > 0) ? 'Modify active fields of the application' : 'Fill details to add a new payday application';

include 'header.php';
?>

<style>
    .edit-container {
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding-bottom: 90px; /* Space for sticky footer */
    }
    .action-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    .btn-outline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border: 1px solid var(--border-color);
        background: #ffffff;
        color: var(--text-muted);
        border-radius: 8px;
        font-weight: 600;
        font-size: 13.5px;
        text-decoration: none;
        transition: all 0.25s ease;
        cursor: pointer;
    }
    .btn-outline:hover {
        background: #f8fafc;
        color: var(--text-main);
        border-color: #cbd5e1;
    }

    /* Split double column layout */
    .edit-layout-wrapper {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 30px;
        align-items: start;
    }
    @media (max-width: 992px) {
        .edit-layout-wrapper {
            grid-template-columns: 1fr;
        }
    }

    /* Left Sidebar Panel */
    .edit-sidebar {
        position: sticky;
        top: 100px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        z-index: 10;
    }
    .sidebar-profile-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        text-align: center;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .sidebar-avatar-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid var(--primary);
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px auto;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .sidebar-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .sidebar-profile-name {
        font-family: 'Outfit', sans-serif;
        font-size: 16.5px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 4px;
    }
    .sidebar-profile-id {
        font-size: 12px;
        color: var(--text-muted);
        font-family: monospace;
        background: #f1f5f9;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-block;
    }

    /* Tabs Menu */
    .sidebar-tabs {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 8px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .tab-menu-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: transparent;
        border: none;
        color: var(--text-muted);
        font-size: 13.5px;
        font-weight: 700;
        border-radius: 10px;
        cursor: pointer;
        text-align: left;
        transition: all 0.2s ease;
        width: 100%;
    }
    .tab-menu-btn:hover {
        background: rgba(24, 82, 156, 0.04);
        color: var(--primary);
    }
    .tab-menu-btn.active {
        background: var(--primary);
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(24, 82, 156, 0.18);
    }
    .tab-menu-btn i {
        font-size: 15px;
        width: 20px;
        text-align: center;
    }

    /* Right content panels */
    .edit-content-area {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        min-height: 480px;
    }
    .form-panel {
        display: none;
        animation: fadeIn 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .form-panel.active {
        display: block;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .panel-title {
        font-family: 'Outfit', sans-serif;
        font-size: 16px;
        font-weight: 800;
        color: var(--primary);
        margin-bottom: 22px;
        border-bottom: 2px solid rgba(24, 82, 156, 0.08);
        padding-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-group.full-width {
        grid-column: span 2;
    }
    @media (max-width: 768px) {
        .form-group.full-width {
            grid-column: span 1;
        }
    }
    .form-label {
        font-size: 12px;
        font-weight: 800;
        color: var(--text-main);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .input-icon-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }
    .input-icon {
        position: absolute;
        left: 14px;
        color: var(--text-muted);
        font-size: 13.5px;
        pointer-events: none;
        transition: color 0.25s ease;
    }
    .form-input {
        width: 100%;
        height: 42px;
        padding: 0 14px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        outline: none;
        transition: all 0.25s ease;
        background: #f8fafc;
    }
    .form-input-iconified {
        padding-left: 38px !important;
    }
    .form-input:focus {
        border-color: var(--primary);
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(24, 82, 156, 0.1);
    }
    .form-input:focus + .input-icon {
        color: var(--primary);
    }
    .form-select {
        width: 100%;
        height: 42px;
        padding: 0 14px 0 38px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        outline: none;
        transition: all 0.25s ease;
        background: #f8fafc;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 16px;
    }
    .form-select:focus {
        border-color: var(--primary);
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(24, 82, 156, 0.1);
    }
    .form-select:focus + .input-icon {
        color: var(--primary);
    }
    .checkbox-label {
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text-main);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }
    .form-checkbox {
        width: 18px;
        height: 18px;
        accent-color: var(--primary);
        cursor: pointer;
    }

    /* Sticky Action Bar at the bottom */
    .sticky-action-bar {
        position: sticky;
        bottom: 0;
        margin: 35px -40px -40px -40px;
        padding: 16px 40px;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-top: 1px solid var(--border-color);
        z-index: 99;
    }
    .sticky-action-inner {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .sticky-action-status {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .sticky-action-buttons {
        display: flex;
        gap: 12px;
    }
</style>

<div class="edit-container">
    <div class="action-header">
        <a href="applications.php?<?php echo $backParams; ?>" class="btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Cancel & Back
        </a>
    </div>

    <div class="edit-layout-wrapper">
        <!-- Left Sidebar Sticky Summary & Navigation -->
        <div class="edit-sidebar">
            <div class="sidebar-profile-card">
                <div class="sidebar-avatar-wrapper">
                    <?php
                    $hasSelfie = false;
                    $selfieUrl = '';
                    if ($type === 'payday' && !empty($app['selfie_image'])) {
                        $selfiePath = $app['selfie_image'];
                        $selfieUrl = waqt_document_url((string) $selfiePath, '../');
                        $hasSelfie = true;
                    }
                    
                    if ($hasSelfie):
                    ?>
                        <img src="<?php echo htmlspecialchars($selfieUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Photo" class="sidebar-avatar-img">
                    <?php else:
                        $initials = '';
                        if (!empty($app['full_name'])) {
                            $parts = explode(' ', trim($app['full_name']));
                            $initials .= strtoupper(substr($parts[0], 0, 1));
                            if (count($parts) > 1) {
                                $initials .= strtoupper(substr($parts[count($parts) - 1], 0, 1));
                            }
                        }
                        if ($initials === '') $initials = 'U';
                    ?>
                        <span style="font-family: 'Outfit', sans-serif; font-size: 28px; font-weight: 800; color: var(--primary);"><?php echo $initials; ?></span>
                    <?php endif; ?>
                </div>
                <div class="sidebar-profile-name"><?php echo htmlspecialchars($app['full_name'] ?? 'New Application', ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="sidebar-profile-id"><?php echo htmlspecialchars($displayId, ENT_QUOTES, 'UTF-8'); ?></div>
            </div>

            <!-- Tab Buttons -->
            <div class="sidebar-tabs">
                <button type="button" class="tab-menu-btn active" id="btn-tab-profile" onclick="switchFormTab('profile')">
                    <i class="fa-solid fa-user"></i> Profile & Identity
                </button>
                <button type="button" class="tab-menu-btn" id="btn-tab-loan" onclick="switchFormTab('loan')">
                    <i class="fa-solid fa-indian-rupee-sign"></i> Loan Requirements
                </button>
                <button type="button" class="tab-menu-btn" id="btn-tab-employment" onclick="switchFormTab('employment')">
                    <i class="fa-solid fa-briefcase"></i> Employment Info
                </button>
                <button type="button" class="tab-menu-btn" id="btn-tab-bank" onclick="switchFormTab('bank')">
                    <i class="fa-solid fa-building-columns"></i> Bank & References
                </button>
                <button type="button" class="tab-menu-btn" id="btn-tab-docs" onclick="switchFormTab('docs')">
                    <i class="fa-solid fa-folder-open"></i> Docs & Sync
                </button>
            </div>
        </div>

        <!-- Right Content Area containing the Form -->
        <div class="edit-content-area">
            <form id="editAppForm">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="type" value="<?php echo $type; ?>">
                <input type="hidden" name="page" value="<?php echo $backPage; ?>">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($backSearch, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($backStatus, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="date_from" value="<?php echo htmlspecialchars($backDateFrom, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="date_to" value="<?php echo htmlspecialchars($backDateTo, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="amount_min" value="<?php echo htmlspecialchars($backAmountMin, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="amount_max" value="<?php echo htmlspecialchars($backAmountMax, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="loan_type_filter" value="<?php echo htmlspecialchars($backLoanType, ENT_QUOTES, 'UTF-8'); ?>">

                <?php if ($type === 'payday'): ?>
                    <!-- ==================== PAYDAY TAB PANELS ==================== -->
                    
                    <!-- Panel 1: Profile & Identity -->
                    <div id="panel-profile" class="form-panel active">
                        <h3 class="panel-title"><i class="fa-solid fa-user"></i> Profile & Identity</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Full Name *</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="full_name" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['full_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <i class="fa-solid fa-user input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Mobile Number *</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="mobile" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['mobile'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <i class="fa-solid fa-phone input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <div class="input-icon-wrapper">
                                    <input type="email" name="email" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-envelope input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Date of Birth</label>
                                <div class="input-icon-wrapper">
                                    <input type="date" name="dob" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['dob'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-calendar input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">PAN Card Number</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="pan_number" id="panNumberField" class="form-input form-input-iconified" style="text-transform: uppercase;" placeholder="ABCDE1234F" value="<?php echo htmlspecialchars((string)($app['pan_number'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-id-card input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Aadhaar Number</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="aadhaar_number" class="form-input form-input-iconified" placeholder="12-digit Aadhaar" value="<?php echo htmlspecialchars($aadhaarDisplayValue, ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-fingerprint input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Education Qualification</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="education" class="form-input form-input-iconified" placeholder="e.g. Graduate" value="<?php echo htmlspecialchars((string)($app['education'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-graduation-cap input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Experience (Years)</label>
                                <div class="input-icon-wrapper">
                                    <input type="number" name="experience_years" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['experience_years'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-briefcase input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="city" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['city'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-city input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pincode</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="pincode" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['pincode'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-map-pin input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Aadhaar Verified?</label>
                                <div class="input-icon-wrapper">
                                    <select name="aadhaar_verified" class="form-select">
                                        <option value="0" <?php echo ($app['aadhaar_verified'] ?? 0) == 0 ? 'selected' : ''; ?>>No</option>
                                        <option value="1" <?php echo ($app['aadhaar_verified'] ?? 0) == 1 ? 'selected' : ''; ?>>Yes</option>
                                    </select>
                                    <i class="fa-solid fa-shield-halved input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Aadhaar Unique ID</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="aadhaar_unique_id" class="form-input form-input-iconified" placeholder="Aadhaar Auth UUID" value="<?php echo htmlspecialchars((string)($app['aadhaar_unique_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-hashtag input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">CIBIL Report URL</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="cibil_report_url" class="form-input form-input-iconified" placeholder="HTTP Link to Cibil PDF" value="<?php echo htmlspecialchars((string)($app['cibil_report_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-chart-line input-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 2: Loan Requirements -->
                    <div id="panel-loan" class="form-panel">
                        <h3 class="panel-title"><i class="fa-solid fa-indian-rupee-sign"></i> Loan Requirements</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Requested Loan Amount (INR) *</label>
                                <div class="input-icon-wrapper">
                                    <input type="number" step="0.01" name="loan_amount" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['loan_amount'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <i class="fa-solid fa-wallet input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Loan Purpose</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="loan_purpose" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['loan_purpose'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-circle-info input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Existing Loan Running? *</label>
                                <div class="input-icon-wrapper">
                                    <select name="has_running_loan" class="form-select" required>
                                        <option value="0" <?php echo ($app['has_running_loan'] ?? 0) == 0 ? 'selected' : ''; ?>>No</option>
                                        <option value="1" <?php echo ($app['has_running_loan'] ?? 0) == 1 ? 'selected' : ''; ?>>Yes</option>
                                    </select>
                                    <i class="fa-solid fa-arrows-spin input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Application Stage Status *</label>
                                <div class="input-icon-wrapper">
                                    <select name="status" class="form-select" required>
                                        <option value="draft" <?php echo ($app['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                        <option value="submitted" <?php echo ($app['status'] ?? '') === 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                                        <option value="review" <?php echo ($app['status'] ?? '') === 'review' ? 'selected' : ''; ?>>Review</option>
                                        <option value="approved" <?php echo ($app['status'] ?? '') === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="rejected" <?php echo ($app['status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        <option value="disbursed" <?php echo ($app['status'] ?? '') === 'disbursed' ? 'selected' : ''; ?>>Disbursed</option>
                                    </select>
                                    <i class="fa-solid fa-clipboard-check input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Case Priority</label>
                                <div class="input-icon-wrapper">
                                    <select name="priority" class="form-select">
                                        <option value="low" <?php echo ($app['priority'] ?? '') === 'low' ? 'selected' : ''; ?>>Low</option>
                                        <option value="medium" <?php echo ($app['priority'] ?? '') === 'medium' || empty($app['priority']) ? 'selected' : ''; ?>>Medium</option>
                                        <option value="high" <?php echo ($app['priority'] ?? '') === 'high' ? 'selected' : ''; ?>>High</option>
                                    </select>
                                    <i class="fa-solid fa-triangle-exclamation input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Assigned Case Agent</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="assigned_to" class="form-input form-input-iconified" placeholder="Agent Name/Email" value="<?php echo htmlspecialchars((string)($app['assigned_to'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-user-tie input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Video KYC Link/URL</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="video_kyc" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['video_kyc'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-video input-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 3: Employment Info -->
                    <div id="panel-employment" class="form-panel">
                        <h3 class="panel-title"><i class="fa-solid fa-briefcase"></i> Employment Info</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Employment Type *</label>
                                <div class="input-icon-wrapper">
                                    <select name="employment_status" class="form-select" required>
                                        <option value="salaried" <?php echo ($app['employment_status'] ?? '') === 'salaried' ? 'selected' : ''; ?>>Salaried</option>
                                        <option value="self" <?php echo ($app['employment_status'] ?? '') === 'self' ? 'selected' : ''; ?>>Self Employed</option>
                                    </select>
                                    <i class="fa-solid fa-user-gear input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Company Name</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="company_name" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['company_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-building input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Designation</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="designation" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['designation'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-user-ninja input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Monthly Net Income (INR) *</label>
                                <div class="input-icon-wrapper">
                                    <input type="number" step="0.01" name="monthly_income" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['monthly_income'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <i class="fa-solid fa-money-check-dollar input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Income Received Mode</label>
                                <div class="input-icon-wrapper">
                                    <select name="income_received_in" class="form-select">
                                        <option value="account" <?php echo ($app['income_received_in'] ?? '') === 'account' ? 'selected' : ''; ?>>Bank Account</option>
                                        <option value="cash" <?php echo ($app['income_received_in'] ?? '') === 'cash' ? 'selected' : ''; ?>>Cash</option>
                                        <option value="cheque" <?php echo ($app['income_received_in'] ?? '') === 'cheque' ? 'selected' : ''; ?>>Cheque</option>
                                    </select>
                                    <i class="fa-solid fa-wallet input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">UAN Number (PF/EPF)</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="uan_number" class="form-input form-input-iconified" placeholder="12-digit UAN" value="<?php echo htmlspecialchars((string)($app['uan_number'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-hashtag input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Office Email Address</label>
                                <div class="input-icon-wrapper">
                                    <input type="email" name="office_email" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['office_email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-envelope-open-text input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Office Pincode</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="office_pincode" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['office_pincode'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-map-pin input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Salary Day of Month</label>
                                <div class="input-icon-wrapper">
                                    <input type="number" min="1" max="31" name="salary_day" class="form-input form-input-iconified" placeholder="e.g. 5" value="<?php echo htmlspecialchars((string)($app['salary_day'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-calendar-day input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Office Address</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="office_address" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['office_address'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-map-location input-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 4: Bank & References -->
                    <div id="panel-bank" class="form-panel">
                        <h3 class="panel-title"><i class="fa-solid fa-building-columns"></i> Bank & References</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Bank Name</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="bank_name" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['bank_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-bank input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Branch Name</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="branch_name" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['branch_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-code-branch input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Account Holder Name</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="account_holder" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['account_holder'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-user-tag input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Account Number</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="account_number" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['account_number'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-hashtag input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">IFSC Code</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="ifsc_code" id="ifscCodeField" class="form-input form-input-iconified" style="text-transform: uppercase;" value="<?php echo htmlspecialchars((string)($app['ifsc_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-building input-icon"></i>
                                </div>
                            </div>
                            
                            <div style="grid-column: span 2; border-top: 1px dashed var(--border-color); margin: 10px 0;"></div>
                            
                            <div class="form-group">
                                <label class="form-label">Primary Reference Name</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="reference1_name" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['reference1_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-user input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Primary Reference Relation</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="reference1_relation" class="form-input form-input-iconified" placeholder="Father, Friend..." value="<?php echo htmlspecialchars((string)($app['reference1_relation'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-user-group input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Primary Reference Mobile</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="reference1_mobile" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['reference1_mobile'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-phone input-icon"></i>
                                </div>
                            </div>
                            
                            <div style="grid-column: span 2; border-top: 1px dashed var(--border-color); margin: 10px 0;"></div>
                            
                            <div class="form-group">
                                <label class="form-label">Secondary Reference Name</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="reference2_name" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['reference2_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-user input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Secondary Reference Relation</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="reference2_relation" class="form-input form-input-iconified" placeholder="Spouse, Colleague..." value="<?php echo htmlspecialchars((string)($app['reference2_relation'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-user-group input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Secondary Reference Mobile</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="reference2_mobile" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['reference2_mobile'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-phone input-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 5: Docs & Sync -->
                    <div id="panel-docs" class="form-panel">
                        <h3 class="panel-title"><i class="fa-solid fa-folder-open"></i> Docs & Sync</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Selfie Image Path</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="selfie_image" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['selfie_image'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-camera input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Company ID Card Path</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="company_id_card" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['company_id_card'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-id-badge input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Salary Slip (Current) Path</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="salary_slip_current" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['salary_slip_current'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-file-invoice-dollar input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Salary Slip (Previous) Path</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="salary_slip_previous" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['salary_slip_previous'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-file-invoice-dollar input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Salary Slip (Old) Path</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="salary_slip_old" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['salary_slip_old'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-file-invoice-dollar input-icon"></i>
                                </div>
                            </div>
                            
                            <div style="grid-column: span 2; border-top: 1px dashed var(--border-color); margin: 10px 0;"></div>
                            
                            <div class="form-group">
                                <label class="form-label">Ingestion Lead Source</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="source" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['source'] ?? 'Portal Direct'), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-globe input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">External System App ID</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="external_application_id" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['external_application_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-hashtag input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Origin Ingestion System</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="source_system" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['source_system'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-server input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Origin Lead ID</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="source_lead_id" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['source_lead_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-tag input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Origin Application ID</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="source_application_id" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['source_application_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-file-invoice input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Origin Status / State</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="source_status" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['source_status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-info-circle input-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- ==================== WIZARD TAB PANELS ==================== -->
                    
                    <!-- Panel 1: Profile & Identity -->
                    <div id="panel-profile" class="form-panel active">
                        <h3 class="panel-title"><i class="fa-solid fa-user"></i> Profile & Identity</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Full Name *</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="full_name" class="form-input form-input-iconified" value="<?php echo htmlspecialchars($app['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <i class="fa-solid fa-user input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Mobile Number *</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="mobile" class="form-input form-input-iconified" value="<?php echo htmlspecialchars($app['mobile'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <i class="fa-solid fa-phone input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <div class="input-icon-wrapper">
                                    <input type="email" name="email" class="form-input form-input-iconified" value="<?php echo htmlspecialchars($app['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-envelope input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Date of Birth</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="dob" class="form-input form-input-iconified" placeholder="YYYY-MM-DD" value="<?php echo htmlspecialchars($app['dob'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-calendar input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Gender</label>
                                <div class="input-icon-wrapper">
                                    <select name="gender" class="form-select">
                                        <option value="male" <?php echo ($app['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="female" <?php echo ($app['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="other" <?php echo ($app['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                    <i class="fa-solid fa-venus-mars input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">PAN Card Number</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="pan_number" id="panNumberField" class="form-input form-input-iconified" style="text-transform: uppercase;" value="<?php echo htmlspecialchars($app['pan_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-id-card input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Residential Address</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="address" class="form-input form-input-iconified" value="<?php echo htmlspecialchars($app['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-map-location input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="city" class="form-input form-input-iconified" value="<?php echo htmlspecialchars($app['city'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-city input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">State</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="state" class="form-input form-input-iconified" value="<?php echo htmlspecialchars($app['state'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-location-dot input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pincode</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="pincode" class="form-input form-input-iconified" value="<?php echo htmlspecialchars($app['pincode'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-map-pin input-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 2: Loan Requirements -->
                    <div id="panel-loan" class="form-panel">
                        <h3 class="panel-title"><i class="fa-solid fa-indian-rupee-sign"></i> Loan Requirements</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Loan Type Category</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="loan_type" class="form-input form-input-iconified" value="<?php echo htmlspecialchars($app['loan_type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-tags input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Requested Loan Amount (INR) *</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="loan_amount" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['loan_amount'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <i class="fa-solid fa-wallet input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Requested Loan Tenure</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="loan_tenure" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['loan_tenure'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-calendar-days input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Loan Purpose</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="loan_purpose" class="form-input form-input-iconified" value="<?php echo htmlspecialchars($app['loan_purpose'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-circle-info input-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 3: Employment Info -->
                    <div id="panel-employment" class="form-panel">
                        <h3 class="panel-title"><i class="fa-solid fa-briefcase"></i> Employment & Bank Details</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Employment Type</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="employment_type" class="form-input form-input-iconified" value="<?php echo htmlspecialchars($app['employment_type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-user-tie input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Company Name</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="company_name" class="form-input form-input-iconified" value="<?php echo htmlspecialchars($app['company_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-building input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Designation</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="designation" class="form-input form-input-iconified" value="<?php echo htmlspecialchars($app['designation'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-user-ninja input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Monthly Income</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="monthly_income" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['monthly_income'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-money-check-dollar input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Existing EMIs</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="existing_emi" class="form-input form-input-iconified" value="<?php echo htmlspecialchars((string)($app['existing_emi'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-percent input-icon"></i>
                                </div>
                            </div>
                            
                            <div style="grid-column: span 2; border-top: 1px dashed var(--border-color); margin: 10px 0;"></div>
                            
                            <div class="form-group">
                                <label class="form-label">Bank Name</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="bank_name" class="form-input form-input-iconified" value="<?php echo htmlspecialchars($app['bank_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-bank input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Account Number</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="account_number" class="form-input form-input-iconified" value="<?php echo htmlspecialchars($app['account_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-hashtag input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">IFSC Code</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" name="ifsc_code" id="ifscCodeField" class="form-input form-input-iconified" style="text-transform: uppercase;" value="<?php echo htmlspecialchars($app['ifsc_code'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-building input-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 4: Consents & Checklist -->
                    <div id="panel-docs" class="form-panel">
                        <h3 class="panel-title"><i class="fa-solid fa-signature"></i> Consents & Legal Agreements</h3>
                        <div class="form-grid" style="grid-template-columns: 1fr; gap: 20px;">
                            <?php
                            $consentObj = ['terms' => true, 'credit' => true, 'communication' => true];
                            try {
                                if (!empty($app['consents'])) {
                                    $consentObj = json_decode($app['consents'], true) ?? $consentObj;
                                }
                            } catch(\Exception $e){}
                            ?>
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="consent_terms" class="form-checkbox" value="1" <?php echo ($consentObj['terms'] ?? false) ? 'checked' : ''; ?>>
                                    Applicant Accepted Terms & Conditions (Legal Agreement)
                                </label>
                            </div>
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="consent_credit" class="form-checkbox" value="1" <?php echo ($consentObj['credit'] ?? false) ? 'checked' : ''; ?>>
                                    Credit Bureau Inquiry Authorization Consent
                                </label>
                            </div>
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="consent_communication" class="form-checkbox" value="1" <?php echo ($consentObj['communication'] ?? false) ? 'checked' : ''; ?>>
                                    Opt-in for Automated WhatsApp/Email Communications
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<!-- Sticky action footer bar -->
<div class="sticky-action-bar">
    <div class="sticky-action-inner">
        <span class="sticky-action-status">
            <i class="fa-solid fa-circle-notch fa-spin" style="display:none; color:var(--primary);" id="savingSpinner"></i>
            <span id="statusBarText"><i class="fa-solid fa-pen-to-square"></i> Editing <?php echo $type === 'payday' ? 'Payday Loan' : 'Wizard Application'; ?> details</span>
        </span>
        <div class="sticky-action-buttons">
            <a href="applications.php?<?php echo $backParams; ?>" class="btn-outline">Cancel</a>
            <button type="submit" form="editAppForm" class="btn-primary-adm" style="height: 42px; padding: 0 30px; box-shadow: none;">Save Changes</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Tab Switching Logic
    function switchFormTab(tabId) {
        // Hide all form panels
        document.querySelectorAll('.form-panel').forEach(panel => {
            panel.classList.remove('active');
        });
        
        // Remove active class from all tab menu buttons
        document.querySelectorAll('.tab-menu-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Show selected panel
        const targetPanel = document.getElementById('panel-' + tabId);
        if (targetPanel) {
            targetPanel.classList.add('active');
        }
        
        // Add active class to clicked button
        const targetBtn = document.getElementById('btn-tab-' + tabId);
        if (targetBtn) {
            targetBtn.classList.add('active');
        }
    }

    // Auto capitalization for PAN & IFSC inputs
    const panField = document.getElementById('panNumberField');
    if (panField) {
        panField.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    const ifscField = document.getElementById('ifscCodeField');
    if (ifscField) {
        ifscField.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    // AJAX Form Submit
    document.getElementById('editAppForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const submitBtn = form.querySelector('button[type="submit"]') || document.querySelector('button[form="editAppForm"]');
        const spinner = document.getElementById('savingSpinner');
        const statusText = document.getElementById('statusBarText');
        
        if (submitBtn) submitBtn.disabled = true;
        if (spinner) spinner.style.display = 'inline-block';
        if (statusText) statusText.innerText = 'Saving changes to database...';

        const formData = new FormData(form);
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');
        
        Swal.fire({
            title: 'Saving details...',
            text: 'Please wait while we record the updates in the database.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('edit-application.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved Successfully!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = data.redirect;
                });
            } else {
                throw new Error(data.message || 'Verification update failed.');
            }
        })
        .catch(err => {
            if (submitBtn) submitBtn.disabled = false;
            if (spinner) spinner.style.display = 'none';
            if (statusText) statusText.innerHTML = '<span style="color:var(--danger);"><i class="fa-solid fa-circle-exclamation"></i> Save failed. Check details.</span>';
            
            Swal.fire({
                icon: 'error',
                title: 'Save Failed',
                text: err.message || 'A network error occurred.'
            });
        });
    });
</script>

<?php include 'footer.php'; ?>
