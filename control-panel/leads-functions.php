<?php
declare(strict_types=1);

function admin_website_lead_tabs(): array
{
    return [
        'landing' => [
            'table' => 'loan_leads',
            'label' => 'Quick Quotes',
            'title' => 'Landing Page Leads (Quick Forms)',
            'empty' => 'No landing leads recorded yet.',
            'icon' => 'fa-list-check',
            'search_columns' => ['full_name', 'mobile', 'loan_type', 'pincode'],
            'category_column' => 'loan_type',
        ],
        'contact' => [
            'table' => 'contact_messages',
            'label' => 'Contact Inquiries',
            'title' => 'Contact Support Inbox',
            'empty' => 'No support inquiries received yet.',
            'icon' => 'fa-envelope-open-text',
            'search_columns' => ['name', 'email', 'phone', 'subject', 'message'],
        ],
        'cibil' => [
            'table' => 'cibil_reports',
            'label' => 'CIBIL Checks',
            'title' => 'CIBIL Report Inquiries',
            'empty' => 'No credit report inquiries checked yet.',
            'icon' => 'fa-gauge-high',
            'search_columns' => ['full_name', 'email', 'mobile', 'pan', 'score', 'ref_id'],
        ],
        'partners' => [
            'table' => 'partner_registrations',
            'label' => 'DSA Partners',
            'title' => 'DSA Partner Registrations',
            'empty' => 'No DSA partners registered yet.',
            'icon' => 'fa-users',
            'search_columns' => ['full_name', 'email_or_mobile', 'status'],
            'status_column' => 'status',
        ],
    ];
}

function admin_website_sanitize_tab(string $tab): string
{
    return array_key_exists($tab, admin_website_lead_tabs()) ? $tab : 'landing';
}

function admin_website_clean_filters(string $tab, array $input): array
{
    $tab = admin_website_sanitize_tab($tab);
    $filters = [
        'search' => trim((string) ($input['search'] ?? '')),
        'date_from' => trim((string) ($input['date_from'] ?? '')),
        'date_to' => trim((string) ($input['date_to'] ?? '')),
        'category' => trim((string) ($input['category'] ?? '')),
        'status' => trim((string) ($input['status'] ?? '')),
        'score_min' => trim((string) ($input['score_min'] ?? '')),
        'score_max' => trim((string) ($input['score_max'] ?? '')),
    ];

    foreach (['date_from', 'date_to'] as $dateKey) {
        if ($filters[$dateKey] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters[$dateKey])) {
            $filters[$dateKey] = '';
        }
    }

    if ($tab !== 'landing') {
        $filters['category'] = '';
    }

    if ($tab !== 'partners' || !in_array($filters['status'], ['pending', 'approved', 'rejected'], true)) {
        $filters['status'] = '';
    }

    foreach (['score_min', 'score_max'] as $scoreKey) {
        if ($tab !== 'cibil' || $filters[$scoreKey] === '' || !is_numeric($filters[$scoreKey])) {
            $filters[$scoreKey] = '';
        }
    }

    return $filters;
}

function admin_website_filters_active(array $filters): bool
{
    foreach ($filters as $value) {
        if ($value !== '' && $value !== null) {
            return true;
        }
    }

    return false;
}

function admin_website_build_filters(string $tab, array $filters): array
{
    $tabs = admin_website_lead_tabs();
    $tab = admin_website_sanitize_tab($tab);
    $config = $tabs[$tab];
    $conditions = [];
    $params = [];
    $types = '';

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

    if ($tab === 'landing' && $filters['category'] !== '') {
        $conditions[] = 'loan_type = ?';
        $params[] = $filters['category'];
        $types .= 's';
    }

    if ($tab === 'partners' && $filters['status'] !== '') {
        $conditions[] = 'status = ?';
        $params[] = $filters['status'];
        $types .= 's';
    }

    if ($tab === 'cibil') {
        if ($filters['score_min'] !== '') {
            $conditions[] = 'score >= ?';
            $params[] = (int) $filters['score_min'];
            $types .= 'i';
        }

        if ($filters['score_max'] !== '') {
            $conditions[] = 'score <= ?';
            $params[] = (int) $filters['score_max'];
            $types .= 'i';
        }
    }

    if ($filters['search'] !== '') {
        $searchParts = [];
        $searchWild = '%' . $filters['search'] . '%';
        foreach ($config['search_columns'] as $column) {
            $searchParts[] = "`$column` LIKE ?";
            $params[] = $searchWild;
            $types .= 's';
        }
        $conditions[] = '(' . implode(' OR ', $searchParts) . ')';
    }

    return [
        'where' => !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '',
        'params' => $params,
        'types' => $types,
    ];
}

function admin_website_fetch_page(mysqli $conn, string $tab, array $filters, int $page, int $limit): array
{
    $tabs = admin_website_lead_tabs();
    $tab = admin_website_sanitize_tab($tab);
    $table = $tabs[$tab]['table'];
    $filterParts = admin_website_build_filters($tab, $filters);
    $whereClause = $filterParts['where'];
    $params = $filterParts['params'];
    $types = $filterParts['types'];

    $countStmt = $conn->prepare("SELECT COUNT(*) FROM `$table` $whereClause");
    if ($types !== '') {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $totalRecords = (int) ($countStmt->get_result()->fetch_row()[0] ?? 0);
    $totalPages = max(1, (int) ceil($totalRecords / $limit));
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $limit;

    $fetchStmt = $conn->prepare("SELECT * FROM `$table` $whereClause ORDER BY id DESC LIMIT ? OFFSET ?");
    $fetchParams = $params;
    $fetchTypes = $types . 'ii';
    $fetchParams[] = $limit;
    $fetchParams[] = $offset;
    $fetchStmt->bind_param($fetchTypes, ...$fetchParams);
    $fetchStmt->execute();

    return [
        'records' => $fetchStmt->get_result()->fetch_all(MYSQLI_ASSOC),
        'total_records' => $totalRecords,
        'total_pages' => $totalPages,
        'page' => $page,
        'limit' => $limit,
    ];
}

function admin_website_fetch_all(mysqli $conn, string $tab, array $filters): array
{
    $tabs = admin_website_lead_tabs();
    $tab = admin_website_sanitize_tab($tab);
    $table = $tabs[$tab]['table'];
    $filterParts = admin_website_build_filters($tab, $filters);
    $whereClause = $filterParts['where'];
    $params = $filterParts['params'];
    $types = $filterParts['types'];

    $stmt = $conn->prepare("SELECT * FROM `$table` $whereClause ORDER BY id DESC");
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function admin_website_fetch_filter_options(mysqli $conn, string $tab, string $kind): array
{
    $tab = admin_website_sanitize_tab($tab);
    $options = [];

    if ($kind === 'category' && $tab === 'landing') {
        $result = $conn->query("SELECT DISTINCT loan_type AS option_value FROM loan_leads WHERE loan_type IS NOT NULL AND loan_type != '' ORDER BY loan_type ASC LIMIT 80");
        while ($row = $result->fetch_assoc()) {
            $options[] = (string) $row['option_value'];
        }
    }

    if ($kind === 'status' && $tab === 'partners') {
        $options = ['pending', 'approved', 'rejected'];
        $result = $conn->query("SELECT DISTINCT status AS option_value FROM partner_registrations WHERE status IS NOT NULL AND status != '' ORDER BY status ASC LIMIT 20");
        while ($row = $result->fetch_assoc()) {
            $options[] = (string) $row['option_value'];
        }
        $options = array_values(array_unique($options));
    }

    return $options;
}

function admin_website_public_record(string $tab, array $record): array
{
    $tab = admin_website_sanitize_tab($tab);
    $allowed = [
        'landing' => ['id', 'loan_type', 'full_name', 'mobile', 'pincode', 'created_at'],
        'contact' => ['id', 'name', 'email', 'phone', 'subject', 'message', 'consent', 'ip_address', 'user_agent', 'created_at'],
        'cibil' => ['id', 'full_name', 'email', 'mobile', 'pan', 'score', 'pdf_url', 'ref_id', 'lead_id', 'application_id', 'created_at'],
        'partners' => ['id', 'full_name', 'email_or_mobile', 'status', 'created_at'],
    ];

    return array_intersect_key($record, array_flip($allowed[$tab]));
}

function admin_website_public_records(string $tab, array $records): array
{
    return array_map(static fn(array $record): array => admin_website_public_record($tab, $record), $records);
}

function admin_website_metric_row(mysqli $conn, string $sql): array
{
    $result = $conn->query($sql);
    return $result ? ($result->fetch_assoc() ?: []) : [];
}

function admin_website_kpi_cards(mysqli $conn): array
{
    $landing = admin_website_metric_row($conn, "
        SELECT 
            COUNT(*) total_count, 
            SUM(CASE WHEN created_at >= CURDATE() THEN 1 ELSE 0 END) today_count,
            SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND created_at < CURDATE() THEN 1 ELSE 0 END) yesterday_count
        FROM loan_leads
    ");
    $contact = admin_website_metric_row($conn, "
        SELECT 
            COUNT(*) total_count, 
            SUM(CASE WHEN created_at >= CURDATE() THEN 1 ELSE 0 END) today_count,
            SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND created_at < CURDATE() THEN 1 ELSE 0 END) yesterday_count
        FROM contact_messages
    ");
    $cibil = admin_website_metric_row($conn, "
        SELECT 
            COUNT(*) total_count, 
            SUM(CASE WHEN created_at >= CURDATE() THEN 1 ELSE 0 END) today_count,
            SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND created_at < CURDATE() THEN 1 ELSE 0 END) yesterday_count,
            SUM(CASE WHEN score >= 750 THEN 1 ELSE 0 END) strong_scores 
        FROM cibil_reports
    ");
    $partners = admin_website_metric_row($conn, "
        SELECT 
            COUNT(*) total_count, 
            SUM(CASE WHEN created_at >= CURDATE() THEN 1 ELSE 0 END) today_count,
            SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND created_at < CURDATE() THEN 1 ELSE 0 END) yesterday_count,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) pending_count 
        FROM partner_registrations
    ");

    $todayTotal = (int)($landing['today_count'] ?? 0) + (int)($contact['today_count'] ?? 0) + (int)($cibil['today_count'] ?? 0) + (int)($partners['today_count'] ?? 0);
    $yesterdayTotal = (int)($landing['yesterday_count'] ?? 0) + (int)($contact['yesterday_count'] ?? 0) + (int)($cibil['yesterday_count'] ?? 0) + (int)($partners['yesterday_count'] ?? 0);

    return [
        [
            'key' => 'today_inquiries',
            'label' => "Today's Inquiries",
            'value' => number_format($todayTotal),
            'sub' => date('d M Y'),
            'icon' => 'fa-calendar-day',
            'tone' => 'green',
        ],
        [
            'key' => 'yesterday_inquiries',
            'label' => "Yesterday's Inquiries",
            'value' => number_format($yesterdayTotal),
            'sub' => date('d M Y', strtotime('yesterday')),
            'icon' => 'fa-calendar-minus',
            'tone' => 'blue',
        ],
        [
            'key' => 'landing',
            'label' => 'Quick Quotes',
            'value' => number_format((int) ($landing['total_count'] ?? 0)),
            'sub' => number_format((int) ($landing['today_count'] ?? 0)) . ' today',
            'icon' => 'fa-list-check',
            'tone' => 'blue',
        ],
        [
            'key' => 'contact',
            'label' => 'Contact Inquiries',
            'value' => number_format((int) ($contact['total_count'] ?? 0)),
            'sub' => number_format((int) ($contact['today_count'] ?? 0)) . ' today',
            'icon' => 'fa-envelope-open-text',
            'tone' => 'green',
        ],
        [
            'key' => 'cibil',
            'label' => 'CIBIL Checks',
            'value' => number_format((int) ($cibil['total_count'] ?? 0)),
            'sub' => number_format((int) ($cibil['strong_scores'] ?? 0)) . ' strong scores',
            'icon' => 'fa-gauge-high',
            'tone' => 'yellow',
        ],
        [
            'key' => 'partners',
            'label' => 'DSA Partners',
            'value' => number_format((int) ($partners['total_count'] ?? 0)),
            'sub' => number_format((int) ($partners['pending_count'] ?? 0)) . ' pending approval',
            'icon' => 'fa-users',
            'tone' => 'red',
        ],
    ];
}

function admin_website_export_config(string $tab): array
{
    $tab = admin_website_sanitize_tab($tab);
    $configs = [
        'landing' => [
            'filename' => 'Waqt_Finance_Quick_Quotes_',
            'headers' => ['ID', 'Loan Type', 'Full Name', 'Mobile', 'Pincode', 'Created At'],
            'columns' => ['id', 'loan_type', 'full_name', 'mobile', 'pincode', 'created_at'],
        ],
        'contact' => [
            'filename' => 'Waqt_Finance_Contact_Inquiries_',
            'headers' => ['ID', 'Name', 'Email', 'Phone', 'Subject', 'Message', 'Consent', 'IP Address', 'User Agent', 'Created At'],
            'columns' => ['id', 'name', 'email', 'phone', 'subject', 'message', 'consent', 'ip_address', 'user_agent', 'created_at'],
        ],
        'cibil' => [
            'filename' => 'Waqt_Finance_CIBIL_Checks_',
            'headers' => ['ID', 'Full Name', 'Email', 'Mobile', 'PAN', 'Score', 'Reference ID', 'PDF URL', 'Lead ID', 'Application ID', 'Created At'],
            'columns' => ['id', 'full_name', 'email', 'mobile', 'pan', 'score', 'ref_id', 'pdf_url', 'lead_id', 'application_id', 'created_at'],
        ],
        'partners' => [
            'filename' => 'Waqt_Finance_DSA_Partners_',
            'headers' => ['ID', 'Full Name', 'Email / Mobile', 'Status', 'Created At'],
            'columns' => ['id', 'full_name', 'email_or_mobile', 'status', 'created_at'],
        ],
    ];

    return $configs[$tab];
}

function admin_website_export_value(string $column, $value): string
{
    if ($column === 'created_at' && $value !== null && $value !== '') {
        return date('d M Y, h:i A', strtotime((string) $value));
    }

    if ($column === 'consent') {
        return (string) $value === '1' ? 'Yes' : 'No';
    }

    return (string) ($value ?? '');
}
