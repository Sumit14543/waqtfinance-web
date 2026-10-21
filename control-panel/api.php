<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/leads-functions.php';

// Auth check
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    json_response(['status' => 'error', 'message' => 'Unauthorized access.'], 401);
}

require_post();

// CSRF check
require_valid_csrf();

$action = trim((string) ($_POST['action'] ?? ''));

if ($action === '') {
    json_response(['status' => 'error', 'message' => 'Action parameter is required.'], 400);
}

$conn = db();

function normalize_blog_status(string $status): ?string
{
    $status = strtolower(trim($status));
    $map = [
        'published' => 'published',
        'active' => 'published',
        'draft' => 'draft',
        'inactive' => 'draft',
    ];

    return $map[$status] ?? null;
}

function blog_status_label(string $status): string
{
    return $status === 'published' ? 'Active' : 'Inactive';
}

function admin_master_format_money(float $amount): string
{
    if ($amount >= 10000000) {
        return 'Rs. ' . number_format($amount / 10000000, 2) . ' Cr';
    }

    if ($amount >= 100000) {
        return 'Rs. ' . number_format($amount / 100000, 2) . ' L';
    }

    return 'Rs. ' . number_format($amount, 0);
}

function admin_mask_edi_sensitive_fields(array $row): array
{
    if (array_key_exists('aadhaar_number', $row)) {
        $maskedAadhaar = waqt_mask_stored_sensitive_value((string) $row['aadhaar_number'], 0, 4);
        $row['aadhaar_number'] = $maskedAadhaar !== '' ? $maskedAadhaar : 'N/A';
    }

    return $row;
}

function admin_fetch_existing_edi_aadhaar(mysqli $conn, int $id): string
{
    if ($id <= 0) {
        return '';
    }

    $stmt = $conn->prepare('SELECT aadhaar_number FROM edi_loan_applications WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return '';
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    return (string) ($row['aadhaar_number'] ?? '');
}

function admin_current_role(): string
{
    $role = strtolower(trim((string) ($_SESSION['admin_role'] ?? 'admin')));
    $role = preg_replace('/[^a-z0-9]+/', '_', $role) ?? 'admin';
    $role = trim($role, '_');

    return $role !== '' ? $role : 'admin';
}

function admin_can(string $capability): bool
{
    return admin_current_role() === 'admin';
}

function admin_require_capability(string $capability): void
{
    if (!admin_can($capability)) {
        json_response(['status' => 'error', 'message' => 'You do not have permission to perform this action.'], 403);
    }
}

function admin_master_fetch_filter_options(mysqli $conn, string $table, string $column): array
{
    $allowedColumns = [
        'loan_applied_master' => ['city', 'loan_type'],
        'edi_loan_applications' => ['city', 'business_type', 'status'],
    ];

    if (!isset($allowedColumns[$table]) || !in_array($column, $allowedColumns[$table], true)) {
        return [];
    }

    $sql = "SELECT DISTINCT `$column` AS option_value FROM `$table` WHERE `$column` IS NOT NULL AND `$column` != '' ORDER BY `$column` ASC LIMIT 80";
    $result = $conn->query($sql);
    $options = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $options[] = (string) $row['option_value'];
        }
    }

    return $options;
}

function admin_master_build_filters(string $tab, array $filters): array
{
    $conditions = [];
    $params = [];
    $types = '';
    $amountExpression = "CAST(REPLACE(COALESCE(loan_amount, '0'), ',', '') AS DECIMAL(12,2))";

    if ($filters['date_from'] !== '') {
        $conditions[] = 'created_at >= ?';
        $params[] = $filters['date_from'] . ' 00:00:00';
        $types .= 's';
    }

    if ($filters['date_to'] !== '') {
        $conditions[] = 'created_at <= ?';
        $params[] = $filters['date_to'] . ' 23:59:59';
        $types .= 's';
    }

    if ($filters['amount_min'] !== '') {
        $conditions[] = "$amountExpression >= ?";
        $params[] = (float) $filters['amount_min'];
        $types .= 'd';
    }

    if ($filters['amount_max'] !== '') {
        $conditions[] = "$amountExpression <= ?";
        $params[] = (float) $filters['amount_max'];
        $types .= 'd';
    }

    if ($filters['city'] !== '') {
        $conditions[] = 'city = ?';
        $params[] = $filters['city'];
        $types .= 's';
    }

    if ($tab === 'general') {
        if ($filters['category'] !== '') {
            $conditions[] = 'loan_type = ?';
            $params[] = $filters['category'];
            $types .= 's';
        }

        if ($filters['search'] !== '') {
            $conditions[] = '(application_id LIKE ? OR applicant_name LIKE ? OR mobile_number LIKE ? OR pancard_number LIKE ? OR city LIKE ? OR loan_type LIKE ?)';
            $searchWild = '%' . $filters['search'] . '%';
            array_push($params, $searchWild, $searchWild, $searchWild, $searchWild, $searchWild, $searchWild);
            $types .= 'ssssss';
        }
    } else {
        if ($filters['category'] !== '') {
            $conditions[] = 'business_type = ?';
            $params[] = $filters['category'];
            $types .= 's';
        }

        if ($filters['status'] !== '') {
            $conditions[] = 'status = ?';
            $params[] = $filters['status'];
            $types .= 's';
        }

        if ($filters['search'] !== '') {
            $conditions[] = '(application_id LIKE ? OR applicant_name LIKE ? OR mobile_number LIKE ? OR pan_number LIKE ? OR city LIKE ? OR shop_business_name LIKE ? OR pincode LIKE ? OR status LIKE ?)';
            $searchWild = '%' . $filters['search'] . '%';
            array_push($params, $searchWild, $searchWild, $searchWild, $searchWild, $searchWild, $searchWild, $searchWild, $searchWild);
            $types .= 'ssssssss';
        }
    }

    return [
        'where' => !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '',
        'params' => $params,
        'types' => $types,
    ];
}

switch ($action) {
    case 'list_website_leads':
        $tab = admin_website_sanitize_tab(trim((string) ($_POST['tab'] ?? 'landing')));
        $limit = (int) ($_POST['limit'] ?? 10);
        if ($limit < 5 || $limit > 100) {
            $limit = 10;
        }

        $page = (int) ($_POST['page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }

        $filters = admin_website_clean_filters($tab, $_POST);
        $pageData = admin_website_fetch_page($conn, $tab, $filters, $page, $limit);
        $records = admin_website_public_records($tab, $pageData['records']);

        json_response([
            'status' => 'success',
            'tab' => $tab,
            'tab_meta' => admin_website_lead_tabs()[$tab],
            'filters' => $filters,
            'filters_active' => admin_website_filters_active($filters),
            'records' => $records,
            'page' => $pageData['page'],
            'limit' => $pageData['limit'],
            'total_records' => $pageData['total_records'],
            'total_pages' => $pageData['total_pages'],
            'kpi_cards' => admin_website_kpi_cards($conn),
            'category_options' => admin_website_fetch_filter_options($conn, $tab, 'category'),
            'status_options' => admin_website_fetch_filter_options($conn, $tab, 'status'),
        ]);
        break;

    case 'list_master_leads':
        $tab = trim((string) ($_POST['tab'] ?? 'general'));
        if (!in_array($tab, ['general', 'edi'], true)) {
            $tab = 'general';
        }

        $limit = (int) ($_POST['limit'] ?? 10);
        if ($limit < 5 || $limit > 100) {
            $limit = 10;
        }

        $page = (int) ($_POST['page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }

        $validEdiStatuses = ['pending', 'submitted', 'approved', 'rejected'];
        $filters = [
            'search' => trim((string) ($_POST['search'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'category' => trim((string) ($_POST['category'] ?? '')),
            'status' => trim((string) ($_POST['status'] ?? '')),
            'date_from' => trim((string) ($_POST['date_from'] ?? '')),
            'date_to' => trim((string) ($_POST['date_to'] ?? '')),
            'amount_min' => trim((string) ($_POST['amount_min'] ?? '')),
            'amount_max' => trim((string) ($_POST['amount_max'] ?? '')),
        ];

        if ($tab !== 'edi' || !in_array($filters['status'], $validEdiStatuses, true)) {
            $filters['status'] = '';
        }

        foreach (['date_from', 'date_to'] as $dateKey) {
            if ($filters[$dateKey] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters[$dateKey])) {
                $filters[$dateKey] = '';
            }
        }

        foreach (['amount_min', 'amount_max'] as $amountKey) {
            if ($filters[$amountKey] !== '' && !is_numeric($filters[$amountKey])) {
                $filters[$amountKey] = '';
            }
        }

        $tableName = $tab === 'general' ? 'loan_applied_master' : 'edi_loan_applications';
        $categoryColumn = $tab === 'general' ? 'loan_type' : 'business_type';
        $cityOptions = admin_master_fetch_filter_options($conn, $tableName, 'city');
        $categoryOptions = admin_master_fetch_filter_options($conn, $tableName, $categoryColumn);
        $statusOptions = $tab === 'edi'
            ? array_values(array_unique(array_merge($validEdiStatuses, admin_master_fetch_filter_options($conn, $tableName, 'status'))))
            : [];

        $statsRes = $conn->query("
            SELECT
                COUNT(*) AS total_records,
                SUM(CASE WHEN created_at >= CURDATE() AND created_at < DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS today_records,
                SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND created_at < CURDATE() THEN 1 ELSE 0 END) AS yesterday_records,
                SUM(CASE WHEN created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 1 ELSE 0 END) AS month_records,
                COALESCE(SUM(CAST(REPLACE(COALESCE(loan_amount, '0'), ',', '') AS DECIMAL(12,2))), 0) AS total_loan_value
            FROM `$tableName`
        ");
        $stats = $statsRes ? ($statsRes->fetch_assoc() ?: []) : [];
        $kpiCards = [
            [
                'label' => $tab === 'general' ? 'Total General Leads' : 'Total EDI Leads',
                'value' => number_format((int) ($stats['total_records'] ?? 0)),
                'sub' => $tab === 'general' ? 'Quick loan enquiries' : 'Shopkeeper applications',
                'icon' => $tab === 'general' ? 'fa-layer-group' : 'fa-store',
                'tone' => 'blue',
            ],
            [
                'label' => "Today's Leads",
                'value' => number_format((int) ($stats['today_records'] ?? 0)),
                'sub' => 'Yesterday: ' . number_format((int) ($stats['yesterday_records'] ?? 0)),
                'icon' => 'fa-calendar-day',
                'tone' => 'green',
            ],
            [
                'label' => 'This Month',
                'value' => number_format((int) ($stats['month_records'] ?? 0)),
                'sub' => date('M Y') . ' intake',
                'icon' => 'fa-chart-line',
                'tone' => 'yellow',
            ],
            [
                'label' => 'Loan Value',
                'value' => admin_master_format_money((float) ($stats['total_loan_value'] ?? 0)),
                'sub' => 'Requested amount',
                'icon' => 'fa-indian-rupee-sign',
                'tone' => 'red',
            ],
        ];

        $filterParts = admin_master_build_filters($tab, $filters);
        $whereClause = $filterParts['where'];
        $params = $filterParts['params'];
        $types = $filterParts['types'];

        $countStmt = $conn->prepare("SELECT COUNT(*) FROM `$tableName` $whereClause");
        if ($types !== '') {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $totalRecords = (int) ($countStmt->get_result()->fetch_row()[0] ?? 0);
        $totalPages = max(1, (int) ceil($totalRecords / $limit));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $limit;

        $fetchSql = "SELECT * FROM `$tableName` $whereClause ORDER BY id DESC LIMIT ? OFFSET ?";
        $fetchStmt = $conn->prepare($fetchSql);
        $fetchParams = $params;
        $fetchTypes = $types . 'ii';
        $fetchParams[] = $limit;
        $fetchParams[] = $offset;
        $fetchStmt->bind_param($fetchTypes, ...$fetchParams);
        $fetchStmt->execute();
        $records = $fetchStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        if ($tab === 'edi') {
            $records = array_map('admin_mask_edi_sensitive_fields', $records);
        }

        $filtersActive = false;
        foreach ($filters as $filterValue) {
            if ($filterValue !== '') {
                $filtersActive = true;
                break;
            }
        }

        json_response([
            'status' => 'success',
            'tab' => $tab,
            'page' => $page,
            'limit' => $limit,
            'total_records' => $totalRecords,
            'total_pages' => $totalPages,
            'filters_active' => $filtersActive,
            'filters' => $filters,
            'kpi_cards' => $kpiCards,
            'records' => $records,
            'city_options' => $cityOptions,
            'category_options' => $categoryOptions,
            'status_options' => $statusOptions,
        ]);
        break;

    case 'delete_application':
        admin_require_capability('lead.delete');
        $id = (int) ($_POST['id'] ?? 0);
        $type = trim((string) ($_POST['type'] ?? 'payday'));
        if ($id <= 0) {
            json_response(['status' => 'error', 'message' => 'Invalid application ID.'], 400);
        }
        
        if ($type === 'wizard') {
            $table = 'apply_loan_applications';
        } elseif ($type === 'general') {
            $table = 'loan_applied_master';
        } elseif ($type === 'edi') {
            $table = 'edi_loan_applications';
        } elseif ($type === 'landing') {
            $table = 'loan_leads';
        } elseif ($type === 'contact') {
            $table = 'contact_messages';
        } elseif ($type === 'cibil') {
            $table = 'cibil_reports';
        } elseif ($type === 'partners') {
            $table = 'partner_registrations';
        } else {
            $table = 'loan_applications';
        }
        
        $stmt = $conn->prepare("DELETE FROM `$table` WHERE id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            json_response(['status' => 'success', 'message' => 'Application deleted successfully.']);
        } else {
            json_response(['status' => 'error', 'message' => 'Failed to delete application.'], 500);
        }
        break;

    case 'update_partner_status':
        admin_require_capability('partner.status');
        $id = (int) ($_POST['id'] ?? 0);
        $status = trim((string) ($_POST['status'] ?? ''));
        
        $validStatuses = ['pending', 'approved', 'rejected'];
        if ($id <= 0 || !in_array($status, $validStatuses, true)) {
            json_response(['status' => 'error', 'message' => 'Invalid parameters.'], 400);
        }
        
        $stmt = $conn->prepare("UPDATE partner_registrations SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);
        
        if ($stmt->execute()) {
            json_response(['status' => 'success', 'message' => 'DSA partner status updated successfully.']);
        } else {
            json_response(['status' => 'error', 'message' => 'Failed to update partner status.'], 500);
        }
        break;

    case 'save_lead_details':
        admin_require_capability('lead.edit');
        $id = (int) ($_POST['id'] ?? 0);
        $type = trim((string) ($_POST['type'] ?? 'general'));
        
        if ($id <= 0 || !in_array($type, ['general', 'edi'], true)) {
            json_response(['status' => 'error', 'message' => 'Invalid parameters.'], 400);
        }
        
        if ($type === 'general') {
            $applicantName = trim((string)($_POST['applicant_name'] ?? ''));
            $mobileNumber = trim((string)($_POST['mobile_number'] ?? ''));
            $pancardNumber = strtoupper(trim((string)($_POST['pancard_number'] ?? '')));
            $city = trim((string)($_POST['city'] ?? ''));
            $loanAmount = (float)($_POST['loan_amount'] ?? 0);
            $loanType = trim((string)($_POST['loan_type'] ?? 'personal'));
            
            if ($applicantName === '' || $mobileNumber === '' || $pancardNumber === '') {
                json_response(['status' => 'error', 'message' => 'Name, Mobile and PAN are required.'], 400);
            }
            
            $stmt = $conn->prepare("UPDATE loan_applied_master SET applicant_name = ?, mobile_number = ?, pancard_number = ?, city = ?, loan_amount = ?, loan_type = ? WHERE id = ?");
            $stmt->bind_param('ssssdsi', $applicantName, $mobileNumber, $pancardNumber, $city, $loanAmount, $loanType, $id);
        } else {
            $applicantName = trim((string)($_POST['applicant_name'] ?? ''));
            $mobileNumber = trim((string)($_POST['mobile_number'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $currentAddress = trim((string)($_POST['current_address'] ?? ''));
            $pincode = trim((string)($_POST['pincode'] ?? ''));
            $city = trim((string)($_POST['city'] ?? ''));
            $panNumber = strtoupper(trim((string)($_POST['pan_number'] ?? '')));
            $aadhaarInput = trim((string)($_POST['aadhaar_number'] ?? ''));
            $shopBusinessName = trim((string)($_POST['shop_business_name'] ?? ''));
            $registrationType = trim((string)($_POST['registration_type'] ?? ''));
            $businessAge = trim((string)($_POST['business_age'] ?? ''));
            $dailySales = (float)($_POST['daily_sales'] ?? 0);
            $monthlyIncome = (float)($_POST['monthly_income'] ?? 0);
            $loanAmount = (float)($_POST['loan_amount'] ?? 0);
            $loanPurpose = trim((string)($_POST['loan_purpose'] ?? ''));
            $ifscCode = strtoupper(trim((string)($_POST['ifsc_code'] ?? '')));
            $bankName = trim((string)($_POST['bank_name'] ?? ''));
            $accountHolder = trim((string)($_POST['account_holder'] ?? ''));
            $accountNumber = trim((string)($_POST['account_number'] ?? ''));
            
            $aadhaarNumber = waqt_prepare_aadhaar_for_storage(
                $aadhaarInput,
                admin_fetch_existing_edi_aadhaar($conn, $id)
            );

            if ($applicantName === '' || $mobileNumber === '' || $panNumber === '' || $aadhaarNumber === '') {
                json_response(['status' => 'error', 'message' => 'Name, Mobile, PAN, and Aadhaar are required.'], 400);
            }
            
            $stmt = $conn->prepare("UPDATE edi_loan_applications SET applicant_name = ?, mobile_number = ?, email = ?, current_address = ?, pincode = ?, city = ?, pan_number = ?, aadhaar_number = ?, shop_business_name = ?, registration_type = ?, business_age = ?, daily_sales = ?, monthly_income = ?, loan_amount = ?, loan_purpose = ?, ifsc_code = ?, bank_name = ?, account_holder = ?, account_number = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('sssssssssssddddsssssi', $applicantName, $mobileNumber, $email, $currentAddress, $pincode, $city, $panNumber, $aadhaarNumber, $shopBusinessName, $registrationType, $businessAge, $dailySales, $monthlyIncome, $loanAmount, $loanPurpose, $ifscCode, $bankName, $accountHolder, $accountNumber, $id);
        }
        
        if ($stmt->execute()) {
            json_response(['status' => 'success', 'message' => 'Lead details updated successfully.']);
        } else {
            json_response(['status' => 'error', 'message' => 'Failed to update lead details.'], 500);
        }
        break;

    case 'save_application':
        admin_require_capability('lead.edit');
        $id = (int) ($_POST['id'] ?? 0);
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $mobile = trim((string) ($_POST['mobile'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $monthlyIncome = (float) ($_POST['monthly_income'] ?? 0.0);
        $loanAmount = (float) ($_POST['loan_amount'] ?? 0.0);
        $status = trim((string) ($_POST['status'] ?? 'draft'));
        $loanPurpose = trim((string) ($_POST['loan_purpose'] ?? ''));
        $city = trim((string) ($_POST['city'] ?? ''));
        $pincode = trim((string) ($_POST['pincode'] ?? ''));

        if ($mobile === '') {
            json_response(['status' => 'error', 'message' => 'Mobile number is required.'], 400);
        }

        $validStatuses = ['draft', 'submitted', 'review', 'approved', 'rejected', 'disbursed'];
        if (!in_array($status, $validStatuses, true)) {
            $status = 'draft';
        }

        if ($id > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE loan_applications SET full_name = ?, mobile = ?, email = ?, monthly_income = ?, loan_amount = ?, status = ?, loan_purpose = ?, city = ?, pincode = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('sssddssssi', $fullName, $mobile, $email, $monthlyIncome, $loanAmount, $status, $loanPurpose, $city, $pincode, $id);
        } else {
            // Create
            $appId = 'WAQTFN-PD-' . mt_rand(1000000000, 9999999999);
            $stmt = $conn->prepare("INSERT INTO loan_applications (application_id, full_name, mobile, email, monthly_income, loan_amount, status, loan_purpose, city, pincode, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param('ssssddssss', $appId, $fullName, $mobile, $email, $monthlyIncome, $loanAmount, $status, $loanPurpose, $city, $pincode);
        }

        if ($stmt->execute()) {
            json_response(['status' => 'success', 'message' => 'Application saved successfully.']);
        } else {
            json_response(['status' => 'error', 'message' => 'Failed to save application.'], 500);
        }
        break;

    case 'update_app_status':
        admin_require_capability('lead.status');
        $id = (int) ($_POST['id'] ?? 0);
        $status = trim((string) ($_POST['status'] ?? ''));
        
        $validStatuses = ['draft', 'submitted', 'review', 'approved', 'rejected', 'disbursed'];
        if ($id <= 0 || !in_array($status, $validStatuses, true)) {
            json_response(['status' => 'error', 'message' => 'Invalid parameters.'], 400);
        }
        
        $stmt = $conn->prepare("UPDATE loan_applications SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);
        
        if ($stmt->execute()) {
            json_response(['status' => 'success', 'message' => 'Application status updated.']);
        } else {
            json_response(['status' => 'error', 'message' => 'Failed to update application status.'], 500);
        }
        break;

    case 'update_case_assignment':
        admin_require_capability('lead.assignment');
        $id = (int) ($_POST['id'] ?? 0);
        $agent = trim((string) ($_POST['assigned_to'] ?? ''));
        $priority = trim((string) ($_POST['priority'] ?? 'medium'));
        
        if ($id <= 0) {
            json_response(['status' => 'error', 'message' => 'Invalid parameters.'], 400);
        }
        
        $stmt = $conn->prepare("UPDATE loan_applications SET assigned_to = ?, priority = ? WHERE id = ?");
        $stmt->bind_param('ssi', $agent, $priority, $id);
        
        if ($stmt->execute()) {
            json_response(['status' => 'success', 'message' => 'Case assignment updated successfully.']);
        } else {
            json_response(['status' => 'error', 'message' => 'Failed to update case assignment.'], 500);
        }
        break;

    case 'update_edi_status':
        admin_require_capability('lead.status');
        $id = (int) ($_POST['id'] ?? 0);
        $status = trim((string) ($_POST['status'] ?? ''));
        
        // Match the database enum values
        $validStatuses = ['pending', 'submitted', 'approved', 'rejected'];
        
        // Map UI status request to closest database enum
        if ($status === 'review' || $status === 'pending') {
            $dbStatus = 'pending';
        } elseif ($status === 'disbursed' || $status === 'approved') {
            $dbStatus = 'approved';
        } else {
            $dbStatus = $status;
        }

        if ($id <= 0 || !in_array($dbStatus, $validStatuses, true)) {
            json_response(['status' => 'error', 'message' => 'Invalid status parameter.'], 400);
        }
        
        $stmt = $conn->prepare("UPDATE edi_loan_applications SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('si', $dbStatus, $id);
        
        if ($stmt->execute()) {
            json_response(['status' => 'success', 'message' => 'EDI Application status updated.', 'mapped_status' => $dbStatus]);
        } else {
            json_response(['status' => 'error', 'message' => 'Failed to update EDI status.'], 500);
        }
        break;

    case 'toggle_blog_status':
        admin_require_capability('blog.manage');
        $id = (int) ($_POST['id'] ?? 0);
        $blogStatus = normalize_blog_status((string) ($_POST['status'] ?? ''));
        if ($id <= 0 || $blogStatus === null) {
            json_response(['status' => 'error', 'message' => 'Invalid blog status request.'], 400);
        }

        $stmtBlog = $conn->prepare("SELECT id, author_name FROM blogs WHERE id = ?");
        $stmtBlog->bind_param('i', $id);
        $stmtBlog->execute();
        $blogRow = $stmtBlog->get_result()->fetch_assoc();

        if (!$blogRow) {
            json_response(['status' => 'error', 'message' => 'Blog post not found.'], 404);
        }

        if ($blogStatus === 'published' && trim((string) ($blogRow['author_name'] ?? '')) === '') {
            json_response(['status' => 'error', 'message' => 'Author Name is required before activating this article.'], 400);
        }

        $stmt = $conn->prepare("UPDATE blogs SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('si', $blogStatus, $id);

        if ($stmt->execute()) {
            waqt_generate_sitemap_file();
            json_response([
                'status' => 'success',
                'message' => 'Blog marked ' . strtolower(blog_status_label($blogStatus)) . '.',
                'blog_status' => $blogStatus,
                'label' => blog_status_label($blogStatus),
            ]);
        }

        json_response(['status' => 'error', 'message' => 'Failed to update blog status.'], 500);
        break;

    case 'delete_blog':
        admin_require_capability('blog.delete');
        $id = (int) ($_POST['id'] ?? 0);
        $defaultBlogImage = 'assets/images/default-blog.jpg';
        if ($id <= 0) {
            json_response(['status' => 'error', 'message' => 'Invalid blog ID.'], 400);
        }

        // Optional: delete image from folder
        $stmtImg = $conn->prepare("SELECT image_url FROM blogs WHERE id = ?");
        $stmtImg->bind_param('i', $id);
        $stmtImg->execute();
        $resImg = $stmtImg->get_result()->fetch_assoc();
        if ($resImg && !empty($resImg['image_url']) && $resImg['image_url'] !== $defaultBlogImage) {
            $imgPath = __DIR__ . '/../' . ltrim($resImg['image_url'], '/');
            if (file_exists($imgPath) && is_file($imgPath)) {
                @unlink($imgPath);
            }
        }

        $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            waqt_generate_sitemap_file();
            json_response(['status' => 'success', 'message' => 'Blog post deleted successfully.']);
        } else {
            json_response(['status' => 'error', 'message' => 'Failed to delete blog post.'], 500);
        }
        break;

    case 'save_blog':
        admin_require_capability('blog.manage');
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = trim((string) ($_POST['slug'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));
        $readTime = trim((string) ($_POST['read_time'] ?? '5 Min Read'));
        $excerpt = trim((string) ($_POST['excerpt'] ?? ''));
        $content = waqt_sanitize_rich_html(trim((string) ($_POST['content'] ?? '')));
        $metaTitle = trim((string) ($_POST['meta_title'] ?? ''));
        $metaDescription = trim((string) ($_POST['meta_description'] ?? ''));
        $metaKeywords = trim((string) ($_POST['meta_keywords'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? 'draft'));
        $authorName = trim((string) ($_POST['author_name'] ?? ''));
        $authorDesignation = trim((string) ($_POST['author_designation'] ?? ''));
        $reviewedBy = trim((string) ($_POST['reviewed_by'] ?? ''));
        $ctaText = trim((string) ($_POST['cta_text'] ?? ''));
        $ctaLink = trim((string) ($_POST['cta_link'] ?? ''));
        $faqsRaw = trim((string) ($_POST['faqs'] ?? '[]'));
        $removeImage = (string) ($_POST['remove_image'] ?? '0') === '1';
        $blogStatus = normalize_blog_status($status);
        $defaultBlogImage = 'assets/images/default-blog.jpg';

        $faqsArr = json_decode($faqsRaw, true);
        if (!is_array($faqsArr)) {
            $faqsArr = [];
        }
        $faqsClean = json_encode($faqsArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($title === '' || $slug === '' || $category === '' || $content === '') {
            json_response(['status' => 'error', 'message' => 'Title, Slug, Category, and Content are required.'], 400);
        }

        if ($blogStatus === null) {
            json_response(['status' => 'error', 'message' => 'Invalid blog status selected.'], 400);
        }
        $status = $blogStatus;

        if ($status === 'published' && $authorName === '') {
            json_response(['status' => 'error', 'message' => 'Author Name is required before activating this article.'], 400);
        }

        // Clean slug: lowercase, replace spaces with hyphens, remove special characters
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9\-]/', '', str_replace(' ', '-', $slug));
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        // Check if slug is unique
        if ($id > 0) {
            $stmtSlug = $conn->prepare("SELECT id FROM blogs WHERE slug = ? AND id != ?");
            $stmtSlug->bind_param('si', $slug, $id);
        } else {
            $stmtSlug = $conn->prepare("SELECT id FROM blogs WHERE slug = ?");
            $stmtSlug->bind_param('s', $slug);
        }
        $stmtSlug->execute();
        if ($stmtSlug->get_result()->num_rows > 0) {
            json_response(['status' => 'error', 'message' => 'A blog post with this slug already exists.'], 409);
        }

        // Handle Image Upload
        $imageUrl = null;
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $imageUrl = waqt_save_uploaded_file($_FILES['featured_image'], 'blogs', 'blog', [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ], 5 * 1024 * 1024);
            $removeImage = false;
        }

        // If updating, delete the old image only when replacing/removing it.
        if ($id > 0 && ($imageUrl !== null || $removeImage)) {
            $stmtOld = $conn->prepare("SELECT image_url FROM blogs WHERE id = ?");
            $stmtOld->bind_param('i', $id);
            $stmtOld->execute();
            $resOld = $stmtOld->get_result()->fetch_assoc();
            if ($resOld && !empty($resOld['image_url']) && $resOld['image_url'] !== $defaultBlogImage) {
                $oldImgPath = __DIR__ . '/../' . ltrim($resOld['image_url'], '/');
                if (file_exists($oldImgPath) && is_file($oldImgPath)) {
                    @unlink($oldImgPath);
                }
            }
        }

        if ($id > 0) {
            // Update
            if ($imageUrl !== null || $removeImage) {
                $finalImage = $imageUrl ?? $defaultBlogImage;
                $stmt = $conn->prepare("UPDATE blogs SET title = ?, slug = ?, category = ?, read_time = ?, excerpt = ?, content = ?, image_url = ?, meta_title = ?, meta_description = ?, meta_keywords = ?, status = ?, author_name = ?, author_designation = ?, reviewed_by = ?, cta_text = ?, cta_link = ?, faqs = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param('sssssssssssssssssi', $title, $slug, $category, $readTime, $excerpt, $content, $finalImage, $metaTitle, $metaDescription, $metaKeywords, $status, $authorName, $authorDesignation, $reviewedBy, $ctaText, $ctaLink, $faqsClean, $id);
            } else {
                $stmt = $conn->prepare("UPDATE blogs SET title = ?, slug = ?, category = ?, read_time = ?, excerpt = ?, content = ?, meta_title = ?, meta_description = ?, meta_keywords = ?, status = ?, author_name = ?, author_designation = ?, reviewed_by = ?, cta_text = ?, cta_link = ?, faqs = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param('ssssssssssssssssi', $title, $slug, $category, $readTime, $excerpt, $content, $metaTitle, $metaDescription, $metaKeywords, $status, $authorName, $authorDesignation, $reviewedBy, $ctaText, $ctaLink, $faqsClean, $id);
            }
        } else {
            // Create
            $finalImage = $imageUrl ?? $defaultBlogImage;
            $stmt = $conn->prepare("INSERT INTO blogs (title, slug, category, read_time, excerpt, content, image_url, meta_title, meta_description, meta_keywords, status, author_name, author_designation, reviewed_by, cta_text, cta_link, faqs, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param('sssssssssssssssss', $title, $slug, $category, $readTime, $excerpt, $content, $finalImage, $metaTitle, $metaDescription, $metaKeywords, $status, $authorName, $authorDesignation, $reviewedBy, $ctaText, $ctaLink, $faqsClean);
        }

        if ($stmt->execute()) {
            waqt_generate_sitemap_file();
            json_response(['status' => 'success', 'message' => 'Blog post saved successfully.', 'slug' => $slug]);
        } else {
            json_response(['status' => 'error', 'message' => 'Failed to save blog post to database.'], 500);
        }
        break;

    case 'update_location_status':
        admin_require_capability('location.manage');
        $id = (int) ($_POST['id'] ?? 0);
        $status = trim((string) ($_POST['status'] ?? ''));
        if ($id <= 0 || !in_array($status, ['draft', 'published'], true)) {
            json_response(['status' => 'error', 'message' => 'Invalid location status request.'], 400);
        }

        $stmt = $conn->prepare("UPDATE location_pages SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('si', $status, $id);

        if ($stmt->execute()) {
            waqt_generate_sitemap_file();
            json_response([
                'status' => 'success',
                'message' => 'Location marked ' . ($status === 'published' ? 'Active' : 'Draft') . '.',
                'location_status' => $status,
                'label' => $status === 'published' ? 'Active' : 'Draft',
            ]);
        }

        json_response(['status' => 'error', 'message' => 'Failed to update location status.'], 500);
        break;

    case 'delete_location':
        admin_require_capability('location.manage');
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            json_response(['status' => 'error', 'message' => 'Invalid location ID.'], 400);
        }

        $stmt = $conn->prepare("DELETE FROM location_pages WHERE id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            waqt_generate_sitemap_file();
            json_response(['status' => 'success', 'message' => 'Location page deleted successfully.']);
        } else {
            json_response(['status' => 'error', 'message' => 'Failed to delete location page.'], 500);
        }
        break;

    case 'get_blogs':
        admin_require_capability('blog.manage');
        
        $page = (int) ($_POST['page'] ?? 1);
        if ($page < 1) $page = 1;
        $limit = 10;
        
        $search = trim((string) ($_POST['search'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? ''));
        
        $where = [];
        $params = [];
        $types = "";
        
        if ($search !== '') {
            $where[] = "(title LIKE ? OR slug LIKE ? OR author_name LIKE ?)";
            $searchWildcard = '%' . $search . '%';
            $params[] = $searchWildcard;
            $params[] = $searchWildcard;
            $params[] = $searchWildcard;
            $types .= "sss";
        }
        
        if ($category !== '') {
            $where[] = "category = ?";
            $params[] = $category;
            $types .= "s";
        }
        
        if ($status !== '') {
            $where[] = "status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        $whereSql = "";
        if (!empty($where)) {
            $whereSql = "WHERE " . implode(" AND ", $where);
        }
        
        $countQuery = "SELECT COUNT(*) FROM blogs $whereSql";
        $stmt = $conn->prepare($countQuery);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $totalRecords = (int) ($stmt->get_result()->fetch_row()[0] ?? 0);
        $totalPages = (int) ceil($totalRecords / $limit);
        if ($totalPages < 1) $totalPages = 1;
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $limit;
        
        $fetchQuery = "SELECT * FROM blogs $whereSql ORDER BY id DESC LIMIT ? OFFSET ?";
        $fetchParams = $params;
        $fetchParams[] = $limit;
        $fetchParams[] = $offset;
        $fetchTypes = $types . "ii";
        
        $stmt = $conn->prepare($fetchQuery);
        $stmt->bind_param($fetchTypes, ...$fetchParams);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $rowsHtml = "";
        if ($res->num_rows === 0) {
            $rowsHtml = '<tr><td colspan="9" class="text-center" style="padding: 40px; color: var(--text-muted);"><i class="fa-solid fa-folder-open" style="font-size: 28px; margin-bottom: 10px; display: block; opacity: 0.5;"></i> No matching blog posts found.</td></tr>';
        } else {
            while ($b = $res->fetch_assoc()) {
                $blogStatus = (($b['status'] ?? 'draft') === 'published') ? 'published' : 'draft';
                $isActive = $blogStatus === 'published';
                $statusLabel = $isActive ? 'Active' : 'Inactive';
                $nextStatus = $isActive ? 'draft' : 'published';
                $toggleTitle = $isActive ? 'Set Inactive' : 'Set Active';
                $badgeClass = $isActive ? 'badge-approved' : 'badge-draft';
                
                $rowsHtml .= '<tr id="blog-row-' . (int) $b['id'] . '" data-id="' . (int) $b['id'] . '" data-category="' . htmlspecialchars($b['category']) . '" data-author="' . htmlspecialchars($b['author_name']) . '" data-status="' . htmlspecialchars($blogStatus) . '" data-status-label="' . strtolower($statusLabel) . '">';
                $rowsHtml .= '<td><img src="../' . htmlspecialchars($b['image_url'] ?? 'assets/images/default-blog.jpg') . '" class="blog-thumb" alt="cover"></td>';
                $rowsHtml .= '<td><strong style="color: var(--text-main); font-weight: 700;">' . htmlspecialchars($b['title']) . '</strong></td>';
                $rowsHtml .= '<td><span style="text-transform: capitalize; color: var(--text-main);">' . htmlspecialchars($b['category']) . '</span></td>';
                $rowsHtml .= '<td style="font-family: monospace; font-size:12px; color: var(--info);">/blog/' . htmlspecialchars($b['slug']) . '</td>';
                $rowsHtml .= '<td>' . htmlspecialchars($b['read_time'] ?: '5 Min Read') . '</td>';
                $rowsHtml .= '<td><strong style="color: var(--text-main);">' . number_format((int)($b['views'] ?? 0)) . '</strong></td>';
                $rowsHtml .= '<td><span class="badge-status ' . $badgeClass . ' status-badge">' . $statusLabel . '</span></td>';
                $rowsHtml .= '<td>' . date('d M Y', strtotime($b['created_at'])) . '</td>';
                $rowsHtml .= '<td>';
                $rowsHtml .= '<div class="row-actions">';
                $rowsHtml .= '<button type="button" class="btn-action edit-btn" data-blog=\'' . htmlspecialchars(json_encode($b, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') . '\' title="Edit Article"><i class="fa-solid fa-pen-to-square"></i></button>';
                $rowsHtml .= '<button type="button" class="btn-action status-toggle-btn ' . ($isActive ? 'is-active' : 'is-inactive') . '" data-id="' . (int) $b['id'] . '" data-next-status="' . $nextStatus . '" title="' . $toggleTitle . '" aria-label="' . $toggleTitle . '"><i class="fa-solid ' . ($isActive ? 'fa-toggle-on' : 'fa-toggle-off') . '"></i></button>';
                $rowsHtml .= '<button type="button" class="btn-action delete-btn" data-id="' . (int) $b['id'] . '" title="Delete Article"><i class="fa-solid fa-trash" style="color:#dc2626;"></i></button>';
                $rowsHtml .= '</div>';
                $rowsHtml .= '</td>';
                $rowsHtml .= '</tr>';
            }
        }
        
        $paginationHtml = "";
        if ($totalPages > 1) {
            $paginationHtml .= '<ul class="pagination">';
            $paginationHtml .= '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '">';
            if ($page <= 1) {
                $paginationHtml .= '<span style="opacity:0.5; cursor:not-allowed;">&laquo; Prev</span>';
            } else {
                $paginationHtml .= '<a href="#" data-page="' . ($page - 1) . '">&laquo; Prev</a>';
            }
            $paginationHtml .= '</li>';
            
            for ($p = 1; $p <= $totalPages; $p++) {
                $paginationHtml .= '<li class="page-item ' . ($p === $page ? 'active' : '') . '">';
                if ($p === $page) {
                    $paginationHtml .= '<span>' . $p . '</span>';
                } else {
                    $paginationHtml .= '<a href="#" data-page="' . $p . '">' . $p . '</a>';
                }
                $paginationHtml .= '</li>';
            }
            
            $paginationHtml .= '<li class="page-item ' . ($page >= $totalPages ? 'disabled' : '') . '">';
            if ($page >= $totalPages) {
                $paginationHtml .= '<span style="opacity:0.5; cursor:not-allowed;">Next &raquo;</span>';
            } else {
                $paginationHtml .= '<a href="#" data-page="' . ($page + 1) . '">Next &raquo;</a>';
            }
            $paginationHtml .= '</li>';
            $paginationHtml .= '</ul>';
        }
        
        json_response([
            'status' => 'success',
            'html' => $rowsHtml,
            'pagination' => $paginationHtml,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
        break;

    case 'upload_editor_image':
        admin_require_capability('blog.manage');

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            json_response(['status' => 'error', 'message' => 'No file uploaded.'], 400);
        }

        $file = $_FILES['file'];
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/jpg'  => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif'
        ];
        $maxBytes = 5 * 1024 * 1024; // 5MB limit

        try {
            $fileKey = waqt_save_uploaded_file($file, 'blogs', 'blog_body', $allowedMimes, $maxBytes);
            $baseUrl = waqt_base_url();
            $absoluteUrl = $baseUrl . $fileKey;

            json_response([
                'location' => $absoluteUrl
            ]);
        } catch (Throwable $e) {
            json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
        break;

    case 'save_location':
        admin_require_capability('location.manage');
        $id = (int) ($_POST['id'] ?? 0);
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $name = trim((string) ($_POST['name'] ?? ''));
        $title = trim((string) ($_POST['title'] ?? ''));
        $metaTitle = trim((string) ($_POST['meta_title'] ?? ''));
        $metaDescription = trim((string) ($_POST['meta_description'] ?? ''));
        $type = trim((string) ($_POST['type'] ?? 'service-area'));
        $intro = trim((string) ($_POST['intro'] ?? ''));
        $localNote = trim((string) ($_POST['local_note'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? 'draft'));

        // JSON fields parsing and validation
        $areasRaw = trim((string) ($_POST['areas'] ?? '[]'));
        $scenariosRaw = trim((string) ($_POST['scenarios'] ?? '[]'));
        $nearbyRaw = trim((string) ($_POST['nearby'] ?? '[]'));
        $faqsRaw = trim((string) ($_POST['faqs'] ?? '[]'));
        $productContentRaw = trim((string) ($_POST['product_content'] ?? '[]'));

        // Basic validations
        if ($slug === '' || $name === '' || $title === '') {
            json_response(['status' => 'error', 'message' => 'Slug, Name, and Title are required.'], 400);
        }
        if (!in_array($type, ['region', 'office', 'service-area'], true)) {
            json_response(['status' => 'error', 'message' => 'Invalid location type.'], 400);
        }
        if (!in_array($status, ['draft', 'published'], true)) {
            json_response(['status' => 'error', 'message' => 'Invalid status.'], 400);
        }

        // Validate unique slug
        if ($id > 0) {
            $stmtSlug = $conn->prepare("SELECT id FROM location_pages WHERE slug = ? AND id != ?");
            $stmtSlug->bind_param('si', $slug, $id);
        } else {
            $stmtSlug = $conn->prepare("SELECT id FROM location_pages WHERE slug = ?");
            $stmtSlug->bind_param('s', $slug);
        }
        $stmtSlug->execute();
        if ($stmtSlug->get_result()->fetch_assoc()) {
            json_response(['status' => 'error', 'message' => 'A location with this URL slug already exists.'], 422);
        }

        // Clean arrays
        $areasArr = json_decode($areasRaw, true);
        if (!is_array($areasArr)) {
            // Try parsing comma-separated if simple text was passed
            $areasArr = array_filter(array_map('trim', explode(',', $areasRaw)));
        }
        $areasClean = json_encode(array_values($areasArr), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $scenariosArr = json_decode($scenariosRaw, true);
        if (!is_array($scenariosArr)) {
            $scenariosArr = [];
        }
        $scenariosClean = json_encode($scenariosArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $nearbyArr = json_decode($nearbyRaw, true);
        if (!is_array($nearbyArr)) {
            $nearbyArr = [];
        }
        $nearbyClean = json_encode($nearbyArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $faqsArr = json_decode($faqsRaw, true);
        if (!is_array($faqsArr)) {
            $faqsArr = [];
        }
        $faqsClean = json_encode($faqsArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $productContentArr = json_decode($productContentRaw, true);
        if (!is_array($productContentArr)) {
            $productContentArr = [];
        }
        $productContentClean = json_encode($productContentArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $finalAddress = ($type === 'office') ? $address : null;

        if ($id > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE location_pages SET slug = ?, name = ?, title = ?, meta_title = ?, meta_description = ?, type = ?, intro = ?, local_note = ?, address = ?, areas = ?, scenarios = ?, nearby = ?, faqs = ?, product_content = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('sssssssssssssssi', $slug, $name, $title, $metaTitle, $metaDescription, $type, $intro, $localNote, $finalAddress, $areasClean, $scenariosClean, $nearbyClean, $faqsClean, $productContentClean, $status, $id);
        } else {
            // Create
            $stmt = $conn->prepare("INSERT INTO location_pages (slug, name, title, meta_title, meta_description, type, intro, local_note, address, areas, scenarios, nearby, faqs, product_content, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param('sssssssssssssss', $slug, $name, $title, $metaTitle, $metaDescription, $type, $intro, $localNote, $finalAddress, $areasClean, $scenariosClean, $nearbyClean, $faqsClean, $productContentClean, $status);
        }

        if ($stmt->execute()) {
            waqt_generate_sitemap_file();
            json_response(['status' => 'success', 'message' => 'Location page saved successfully.', 'slug' => $slug]);
        } else {
            json_response(['status' => 'error', 'message' => 'Failed to save location page to database.'], 500);
        }
        break;


    case 'verify_lead_document':
        admin_require_capability('lead.document');
        $id = (int)($_POST['id'] ?? 0);
        $appId = trim((string)($_POST['application_id'] ?? ''));
        $docKey = trim((string)($_POST['document_key'] ?? ''));
        $label = trim((string)($_POST['label'] ?? ''));
        $status = trim((string)($_POST['status'] ?? 'pending'));
        $remark = trim((string)($_POST['remark'] ?? ''));
        
        $validStatuses = ['pending', 'verified', 'rejected'];
        if ($appId === '' || $docKey === '' || !in_array($status, $validStatuses, true)) {
            json_response(['status' => 'error', 'message' => 'Invalid document verification parameters.'], 400);
        }
        
        $verifiedBy = $_SESSION['admin_name'] ?? 'Admin';
        
        // Check if document check record exists
        $stmtCheck = $conn->prepare("SELECT id FROM lead_document_checks WHERE application_id = ? AND document_key = ?");
        $stmtCheck->bind_param('ss', $appId, $docKey);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result()->fetch_assoc();
        
        if ($resCheck) {
            $stmt = $conn->prepare("UPDATE lead_document_checks SET status = ?, remark = ?, verified_by = ?, verified_at = NOW(), updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('sssi', $status, $remark, $verifiedBy, $resCheck['id']);
        } else {
            $leadIdStr = $id > 0 ? (string)$id : NULL;
            $stmt = $conn->prepare("INSERT INTO lead_document_checks (lead_id, application_id, document_key, label, status, remark, verified_by, verified_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())");
            $stmt->bind_param('sssssss', $leadIdStr, $appId, $docKey, $label, $status, $remark, $verifiedBy);
        }
        
        if ($stmt->execute()) {
            $desc = "Document '{$label}' status updated to '" . strtoupper($status) . "'";
            if ($remark !== '') $desc .= " (Remark: {$remark})";
            $typeAct = "document";
            $actorAct = $_SESSION['admin_name'] ?? 'Admin';
            $leadIdStr = $id > 0 ? (string)$id : NULL;
            
            $stmtAct = $conn->prepare("INSERT INTO lead_activities (lead_id, application_id, type, description, actor, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmtAct->bind_param('sssss', $leadIdStr, $appId, $typeAct, $desc, $actorAct);
            $stmtAct->execute();
            
            json_response(['status' => 'success', 'message' => 'Document verification status updated successfully.']);
        } else {
            json_response(['status' => 'error', 'message' => 'Failed to save document verification.'], 500);
        }
        break;

    case 'add_lead_activity':
        admin_require_capability('lead.activity');
        $id = (int)($_POST['id'] ?? 0);
        $appId = trim((string)($_POST['application_id'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $type = trim((string)($_POST['type'] ?? 'comment'));
        
        if ($appId === '' || $description === '') {
            json_response(['status' => 'error', 'message' => 'Application ID and comment text are required.'], 400);
        }
        
        $actor = $_SESSION['admin_name'] ?? 'Admin';
        $leadIdStr = $id > 0 ? (string)$id : NULL;
        
        $stmt = $conn->prepare("INSERT INTO lead_activities (lead_id, application_id, type, description, actor, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('sssss', $leadIdStr, $appId, $type, $description, $actor);
        
        if ($stmt->execute()) {
            json_response(['status' => 'success', 'message' => 'Comment added successfully.']);
        } else {
            json_response(['status' => 'error', 'message' => 'Failed to save comment.'], 500);
        }
        break;

    case 'schedule_lead_followup':
        admin_require_capability('lead.followup');
        $id = (int)($_POST['id'] ?? 0);
        $appId = trim((string)($_POST['application_id'] ?? ''));
        $dueAt = trim((string)($_POST['due_at'] ?? ''));
        $reason = trim((string)($_POST['reason'] ?? ''));
        $notes = trim((string)($_POST['notes'] ?? ''));
        
        if ($appId === '' || $dueAt === '' || $reason === '') {
            json_response(['status' => 'error', 'message' => 'Application ID, Date/Time, and Reason are required.'], 400);
        }
        
        $dueAtFormatted = date('Y-m-d H:i:s', strtotime($dueAt));
        $status = 'pending';
        $actor = $_SESSION['admin_name'] ?? 'Admin';
        $leadIdStr = $id > 0 ? (string)$id : NULL;
        
        $stmt = $conn->prepare("INSERT INTO lead_followups (lead_id, application_id, due_at, reason, status, notes, actor, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param('sssssss', $leadIdStr, $appId, $dueAtFormatted, $reason, $status, $notes, $actor);
        
        if ($stmt->execute()) {
            $desc = "Scheduled follow-up callback for " . date('d M Y, h:i A', strtotime($dueAt)) . " | Reason: " . $reason;
            if ($notes !== '') $desc .= " (Notes: {$notes})";
            $typeAct = "followup";
            $stmtAct = $conn->prepare("INSERT INTO lead_activities (lead_id, application_id, type, description, actor, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmtAct->bind_param('sssss', $leadIdStr, $appId, $typeAct, $desc, $actor);
            $stmtAct->execute();
            
            json_response(['status' => 'success', 'message' => 'Follow-up scheduled successfully.']);
        } else {
            json_response(['status' => 'error', 'message' => 'Failed to schedule follow-up.'], 500);
        }
        break;

    case 'save_lead_cam_sheet':
        admin_require_capability('lead.cam');
        $id = (int)($_POST['id'] ?? 0);
        $appId = trim((string)($_POST['application_id'] ?? ''));
        
        $requestedAmount = (float)($_POST['requested_amount'] ?? 0);
        $recommendedAmount = (float)($_POST['recommended_amount'] ?? 0);
        $approvedAmount = (float)($_POST['approved_amount'] ?? 0);
        $loanTermDays = (int)($_POST['loan_term_days'] ?? 30);
        $interestRate = (float)($_POST['interest_rate'] ?? 1.0);
        $processingFeeRate = (float)($_POST['processing_fee_rate'] ?? 2.0);
        $totalRepayment = (float)($_POST['total_repayment'] ?? 0);
        $monthlyIncome = (float)($_POST['monthly_income'] ?? 0);
        $monthlyExpenses = (float)($_POST['monthly_expenses'] ?? 0);
        
        $netIncome = $monthlyIncome - $monthlyExpenses;
        $dtiRatio = $monthlyIncome > 0 ? ($monthlyExpenses / $monthlyIncome) * 100 : 0.0;
        
        $repaymentBuffer = (float)($_POST['repayment_buffer'] ?? 0.0);
        $policyScore = (int)($_POST['policy_score'] ?? 100);
        $verificationScore = (int)($_POST['verification_score'] ?? 100);
        
        $riskGrade = trim((string)($_POST['risk_grade'] ?? 'C'));
        $creditRisk = trim((string)($_POST['credit_risk'] ?? 'Medium'));
        $repaymentCapacity = trim((string)($_POST['repayment_capacity'] ?? 'Excellent'));
        $recommendation = trim((string)($_POST['recommendation'] ?? 'Approve'));
        $deviationLevel = trim((string)($_POST['deviation_level'] ?? 'None'));
        
        $decisionReason = trim((string)($_POST['decision_reason'] ?? ''));
        $conditions = trim((string)($_POST['conditions'] ?? ''));
        $notes = trim((string)($_POST['notes'] ?? ''));
        
        if ($appId === '') {
            json_response(['status' => 'error', 'message' => 'Application ID is required.'], 400);
        }
        
        $createdBy = $_SESSION['admin_name'] ?? 'Waqt Credit Manager';
        $leadIdStr = $id > 0 ? (string)$id : NULL;
        
        $stmtVersion = $conn->prepare("SELECT MAX(version) as max_v FROM lead_cam_sheets WHERE application_id = ?");
        $stmtVersion->bind_param('s', $appId);
        $stmtVersion->execute();
        $resVersion = $stmtVersion->get_result()->fetch_assoc();
        $nextVersion = $resVersion && $resVersion['max_v'] !== null ? ((int)$resVersion['max_v'] + 1) : 1;
        
        $snapshotObj = [
            'leadId' => $appId,
            'policyChecks' => [
                ['label' => 'CIBIL report available', 'passed' => true],
                ['label' => 'Aadhaar / KYC verified', 'passed' => true],
                ['label' => 'PAN captured', 'passed' => true],
                ['label' => 'Bank details captured', 'passed' => true]
            ],
            'summary' => "CAM sheet version {$nextVersion} saved by {$createdBy}."
        ];
        $snapshotJson = json_encode($snapshotObj);
        
        $status = strtolower($recommendation) === 'approve' ? 'approved' : (strtolower($recommendation) === 'reject' ? 'rejected' : 'review');
        
        $stmt = $conn->prepare("INSERT INTO lead_cam_sheets (lead_id, application_id, version, status, requested_amount, recommended_amount, approved_amount, loan_term_days, interest_rate, processing_fee_rate, total_repayment, monthly_income, monthly_expenses, net_income, dti_ratio, repayment_buffer, policy_score, verification_score, risk_grade, credit_risk, repayment_capacity, recommendation, deviation_level, decision_reason, conditions, notes, snapshot, created_by, decided_by, decided_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())");
        
        $stmt->bind_param(
            'ssisdddidddddddiiissssssssss',
            $leadIdStr,
            $appId,
            $nextVersion,
            $status,
            $requestedAmount,
            $recommendedAmount,
            $approvedAmount,
            $loanTermDays,
            $interestRate,
            $processingFeeRate,
            $totalRepayment,
            $monthlyIncome,
            $monthlyExpenses,
            $netIncome,
            $dtiRatio,
            $repaymentBuffer,
            $policyScore,
            $verificationScore,
            $riskGrade,
            $creditRisk,
            $repaymentCapacity,
            $recommendation,
            $deviationLevel,
            $decisionReason,
            $conditions,
            $notes,
            $snapshotJson,
            $createdBy,
            $createdBy
        );
        
        if ($stmt->execute()) {
            if ($status === 'approved' || $status === 'rejected') {
                $appStatus = $status === 'approved' ? 'approved' : 'rejected';
                $stmtUpdate = $conn->prepare("UPDATE loan_applications SET status = ? WHERE application_id = ?");
                $stmtUpdate->bind_param('ss', $appStatus, $appId);
                $stmtUpdate->execute();
            }
            
            $desc = "Credit Assessment Memo (CAM) version {$nextVersion} saved. Recommendation: {$recommendation}, Approved Amt: ₹" . number_format($approvedAmount, 2);
            $typeAct = "underwriting";
            $stmtAct = $conn->prepare("INSERT INTO lead_activities (lead_id, application_id, type, description, actor, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmtAct->bind_param('sssss', $leadIdStr, $appId, $typeAct, $desc, $createdBy);
            $stmtAct->execute();
            
            json_response(['status' => 'success', 'message' => 'CAM Sheet saved successfully.', 'version' => $nextVersion]);
        } else {
            json_response(['status' => 'error', 'message' => 'Failed to save CAM sheet.'], 500);
        }
        break;

    case 'get_lead_details':
        $id = (int)($_POST['id'] ?? 0);
        $type = trim((string)($_POST['type'] ?? 'general'));
        
        if ($id <= 0 || !in_array($type, ['general', 'edi', 'payday', 'wizard'], true)) {
            json_response(['status' => 'error', 'message' => 'Invalid parameters.'], 400);
        }
        
        $app = null;
        if ($type === 'general') {
            $stmt = $conn->prepare("SELECT * FROM loan_applied_master WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $app = $stmt->get_result()->fetch_assoc();
        } elseif ($type === 'edi') {
            $stmt = $conn->prepare("SELECT * FROM edi_loan_applications WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $app = $stmt->get_result()->fetch_assoc();
        } elseif ($type === 'payday') {
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
            json_response(['status' => 'error', 'message' => 'Application not found.'], 404);
        }
        if ($type === 'edi') {
            $app = admin_mask_edi_sensitive_fields($app);
        }
        
        $appId = $type === 'edi' || $type === 'payday' || $type === 'general' ? ($app['application_id'] ?? '') : ($app['application_ref'] ?? '');
        $pan = trim((string)($app['pan_number'] ?? $app['pancard_number'] ?? ''));
        $mobile = trim((string)($app['mobile'] ?? $app['mobile_number'] ?? ''));
        
        $cibilReport = null;
        if ($pan !== '' || $mobile !== '' || $appId !== '') {
            $cStmt = $conn->prepare("SELECT * FROM cibil_reports WHERE application_id = ? OR (pan = ? AND pan != '') OR (mobile = ? AND mobile != '') ORDER BY id DESC LIMIT 1");
            $cStmt->bind_param('sss', $appId, $pan, $mobile);
            $cStmt->execute();
            $cibilReport = $cStmt->get_result()->fetch_assoc();
        }
        
        $docChecks = [];
        $dcStmt = $conn->prepare("SELECT * FROM lead_document_checks WHERE application_id = ?");
        $dcStmt->bind_param('s', $appId);
        $dcStmt->execute();
        $dcRes = $dcStmt->get_result();
        while ($row = $dcRes->fetch_assoc()) {
            $docChecks[$row['document_key']] = $row;
        }
        
        $activities = [];
        $actStmt = $conn->prepare("SELECT * FROM lead_activities WHERE application_id = ? ORDER BY id DESC");
        $actStmt->bind_param('s', $appId);
        $actStmt->execute();
        $activities = $actStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $followups = [];
        $fupStmt = $conn->prepare("SELECT * FROM lead_followups WHERE application_id = ? ORDER BY due_at ASC");
        $fupStmt->bind_param('s', $appId);
        $fupStmt->execute();
        $followups = $fupStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        json_response([
            'status' => 'success',
            'application' => $app,
            'cibil' => $cibilReport,
            'docChecks' => (object)$docChecks,
            'activities' => $activities,
            'followups' => $followups
        ]);
        break;

    case 'export_applications':
        admin_require_capability('export.leads');
        $tab = trim((string)($_POST['tab'] ?? 'payday'));
        $search = trim((string)($_POST['search'] ?? ''));
        $status = trim((string)($_POST['status'] ?? ''));
        $dateFrom = trim((string)($_POST['date_from'] ?? ''));
        $dateTo = trim((string)($_POST['date_to'] ?? ''));
        $amountMin = trim((string)($_POST['amount_min'] ?? ''));
        $amountMax = trim((string)($_POST['amount_max'] ?? ''));
        $loanType = trim((string)($_POST['loan_type'] ?? ''));

        if (!in_array($tab, ['payday', 'wizard'], true)) {
            json_response(['status' => 'error', 'message' => 'Invalid applications tab parameter.'], 400);
        }

        $validPaydayStatuses = ['draft', 'submitted', 'review', 'approved', 'rejected', 'disbursed'];
        if ($tab !== 'payday' || !in_array($status, $validPaydayStatuses, true)) {
            $status = '';
        }

        foreach (['dateFrom', 'dateTo'] as $dateVar) {
            if ($$dateVar !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $$dateVar)) {
                $$dateVar = '';
            }
        }

        foreach (['amountMin', 'amountMax'] as $amountVar) {
            if ($$amountVar !== '' && !is_numeric($$amountVar)) {
                $$amountVar = '';
            }
        }

        if ($tab !== 'wizard') {
            $loanType = '';
        }

        $conditions = [];
        $params = [];
        $types = '';
        $amountExpression = $tab === 'payday' ? 'loan_amount' : "CAST(REPLACE(loan_amount, ',', '') AS DECIMAL(12,2))";

        if ($dateFrom !== '') {
            $conditions[] = 'created_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
            $types .= 's';
        }

        if ($dateTo !== '') {
            $conditions[] = 'created_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
            $types .= 's';
        }

        if ($amountMin !== '') {
            $conditions[] = "$amountExpression >= ?";
            $params[] = (float) $amountMin;
            $types .= 'd';
        }

        if ($amountMax !== '') {
            $conditions[] = "$amountExpression <= ?";
            $params[] = (float) $amountMax;
            $types .= 'd';
        }

        if ($tab === 'payday') {
            if ($search !== '') {
                $conditions[] = "(application_id LIKE ? OR full_name LIKE ? OR mobile LIKE ? OR email LIKE ? OR pan_number LIKE ?)";
                $searchWild = "%$search%";
                $params[] = $searchWild; $params[] = $searchWild; $params[] = $searchWild; $params[] = $searchWild; $params[] = $searchWild;
                $types .= 'sssss';
            }

            if ($status !== '') {
                $conditions[] = 'status = ?';
                $params[] = $status;
                $types .= 's';
            }

            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $sql = "SELECT application_id, full_name, mobile, email, pan_number, loan_amount, monthly_income, loan_purpose, city, pincode, status, created_at FROM loan_applications $whereClause ORDER BY id DESC";
            $filename = 'Waqt_Finance_Payday_Applications_' . date('Ymd_His') . '.csv';
            $headers = ['Application ID', 'Full Name', 'Mobile', 'Email', 'PAN', 'Loan Amount', 'Monthly Income', 'Loan Purpose', 'City', 'Pincode', 'Status', 'Applied Date'];
        } else {
            if ($search !== '') {
                $conditions[] = "(application_ref LIKE ? OR full_name LIKE ? OR mobile LIKE ? OR email LIKE ? OR pan_number LIKE ? OR loan_type LIKE ?)";
                $searchWild = "%$search%";
                $params[] = $searchWild; $params[] = $searchWild; $params[] = $searchWild; $params[] = $searchWild; $params[] = $searchWild; $params[] = $searchWild;
                $types .= 'ssssss';
            }

            if ($loanType !== '') {
                $conditions[] = 'loan_type = ?';
                $params[] = $loanType;
                $types .= 's';
            }

            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $sql = "SELECT application_ref, loan_type, loan_amount, loan_tenure, loan_purpose, full_name, mobile, email, pan_number, city, state, pincode, employment_type, monthly_income, existing_emi, created_at FROM apply_loan_applications $whereClause ORDER BY id DESC";
            $filename = 'Waqt_Finance_General_Applications_' . date('Ymd_His') . '.csv';
            $headers = ['Application Ref', 'Loan Type', 'Loan Amount', 'Loan Tenure', 'Loan Purpose', 'Full Name', 'Mobile', 'Email', 'PAN', 'City', 'State', 'Pincode', 'Employment Type', 'Monthly Income', 'Existing EMI', 'Applied Date'];
        }

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers);

        while ($row = $result->fetch_assoc()) {
            if (!empty($row['created_at'])) {
                $row['created_at'] = date('d M Y, h:i A', strtotime((string)$row['created_at']));
            }
            fputcsv($output, array_values($row));
        }

        fclose($output);
        exit;

    case 'export_website_leads':
        admin_require_capability('export.website');
        $tab = admin_website_sanitize_tab(trim((string) ($_POST['tab'] ?? 'landing')));
        $filters = admin_website_clean_filters($tab, $_POST);
        $records = admin_website_fetch_all($conn, $tab, $filters);
        $exportConfig = admin_website_export_config($tab);
        $filename = $exportConfig['filename'] . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $exportConfig['headers']);

        if (empty($records)) {
            fputcsv($output, ['No records found']);
        } else {
            foreach ($records as $record) {
                $row = [];
                foreach ($exportConfig['columns'] as $column) {
                    $row[] = admin_website_export_value($column, $record[$column] ?? '');
                }
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;

    case 'export_leads':
        admin_require_capability('export.leads');
        $tab = trim((string)($_POST['tab'] ?? 'general'));
        $search = trim((string)($_POST['search'] ?? ''));
        $city = trim((string)($_POST['city'] ?? ''));
        $category = trim((string)($_POST['category'] ?? ''));
        $status = trim((string)($_POST['status'] ?? ''));
        $dateFrom = trim((string)($_POST['date_from'] ?? ''));
        $dateTo = trim((string)($_POST['date_to'] ?? ''));
        $amountMin = trim((string)($_POST['amount_min'] ?? ''));
        $amountMax = trim((string)($_POST['amount_max'] ?? ''));
        
        if (!in_array($tab, ['general', 'edi'], true)) {
            json_response(['status' => 'error', 'message' => 'Invalid tab parameter.'], 400);
        }

        if ($tab !== 'edi' || !in_array($status, ['pending', 'submitted', 'approved', 'rejected'], true)) {
            $status = '';
        }

        foreach (['dateFrom', 'dateTo'] as $dateVar) {
            if ($$dateVar !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $$dateVar)) {
                $$dateVar = '';
            }
        }

        foreach (['amountMin', 'amountMax'] as $amountVar) {
            if ($$amountVar !== '' && !is_numeric($$amountVar)) {
                $$amountVar = '';
            }
        }
        
        $conditions = [];
        $params = [];
        $types = '';
        $amountExpression = "CAST(REPLACE(COALESCE(loan_amount, '0'), ',', '') AS DECIMAL(12,2))";

        if ($dateFrom !== '') {
            $conditions[] = 'created_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
            $types .= 's';
        }

        if ($dateTo !== '') {
            $conditions[] = 'created_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
            $types .= 's';
        }

        if ($amountMin !== '') {
            $conditions[] = "$amountExpression >= ?";
            $params[] = (float) $amountMin;
            $types .= 'd';
        }

        if ($amountMax !== '') {
            $conditions[] = "$amountExpression <= ?";
            $params[] = (float) $amountMax;
            $types .= 'd';
        }

        if ($city !== '') {
            $conditions[] = 'city = ?';
            $params[] = $city;
            $types .= 's';
        }
        
        if ($tab === 'general') {
            if ($category !== '') {
                $conditions[] = 'loan_type = ?';
                $params[] = $category;
                $types .= 's';
            }

            if ($search !== '') {
                $conditions[] = "(application_id LIKE ? OR applicant_name LIKE ? OR mobile_number LIKE ? OR pancard_number LIKE ? OR city LIKE ? OR loan_type LIKE ?)";
                $searchWild = "%$search%";
                $params[] = $searchWild; $params[] = $searchWild; $params[] = $searchWild;
                $params[] = $searchWild; $params[] = $searchWild; $params[] = $searchWild;
                $types .= 'ssssss';
            }
            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $headers = ['Application ID', 'Applicant Name', 'Mobile', 'PAN', 'City', 'Loan Amount', 'Category', 'Created At'];
            $sql = "SELECT application_id, applicant_name, mobile_number, pancard_number, city, loan_amount, loan_type, created_at FROM loan_applied_master $whereClause ORDER BY id DESC";
        } else {
            if ($category !== '') {
                $conditions[] = 'business_type = ?';
                $params[] = $category;
                $types .= 's';
            }

            if ($status !== '') {
                $conditions[] = 'status = ?';
                $params[] = $status;
                $types .= 's';
            }

            if ($search !== '') {
                $conditions[] = "(application_id LIKE ? OR applicant_name LIKE ? OR mobile_number LIKE ? OR pan_number LIKE ? OR city LIKE ? OR shop_business_name LIKE ? OR pincode LIKE ? OR status LIKE ?)";
                $searchWild = "%$search%";
                $params[] = $searchWild; $params[] = $searchWild; $params[] = $searchWild;
                $params[] = $searchWild; $params[] = $searchWild; $params[] = $searchWild;
                $params[] = $searchWild; $params[] = $searchWild;
                $types .= 'ssssssss';
            }
            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $headers = ['Application ID', 'Applicant Name', 'Mobile', 'Email', 'PAN', 'Aadhaar', 'City', 'Pincode', 'Shop Name', 'Registration Type', 'Business Age', 'Business Type', 'Daily Sales', 'Monthly Income', 'Loan Amount', 'Loan Purpose', 'Status', 'Created At'];
            $sql = "SELECT application_id, applicant_name, mobile_number, email, pan_number, aadhaar_number, city, pincode, shop_business_name, registration_type, business_age, business_type, daily_sales, monthly_income, loan_amount, loan_purpose, status, created_at FROM edi_loan_applications $whereClause ORDER BY id DESC";
        }
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $filename = "Waqt_Finance_" . ucfirst($tab) . "_Leads_" . date('Ymd_His') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        
        fputcsv($output, $headers);
        if (empty($res)) {
            fputcsv($output, ['No records found']);
        } else {
            foreach ($res as $row) {
                if ($tab === 'edi') {
                    $row = admin_mask_edi_sensitive_fields($row);
                }
                if (!empty($row['created_at'])) {
                    $row['created_at'] = date('d M Y, h:i A', strtotime((string)$row['created_at']));
                }
                fputcsv($output, array_values($row));
            }
        }
        fclose($output);
        exit;

    default:
        json_response(['status' => 'error', 'message' => 'Unknown action requested.'], 400);
}
