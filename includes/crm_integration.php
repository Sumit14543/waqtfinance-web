<?php
declare(strict_types=1);

if (!function_exists('push_payday_lead_to_crm')) {
    function push_payday_lead_to_crm(mysqli $conn, string $app_id): array
    {
        $apiUrl = required_env('PAYDAY_CRM_LEAD_PUSH_URL');
        $apiKey = required_env('PAYDAY_CRM_LEAD_PUSH_API_KEY');

        // 1. Fetch application details
        $stmt = $conn->prepare("SELECT * FROM loan_applications WHERE application_id = ? LIMIT 1");
        if (!$stmt) {
            return ['status' => 'error', 'message' => 'Failed to prepare select statement'];
        }
        $stmt->bind_param("s", $app_id);
        $stmt->execute();
        $application = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$application) {
            return ['status' => 'error', 'message' => 'Application not found'];
        }

        // 2. Fetch references
        $references = [];
        $refStmt = $conn->prepare("SELECT * FROM loan_application_references WHERE application_id = ?");
        if ($refStmt) {
            $refStmt->bind_param("s", $app_id);
            $refStmt->execute();
            $res = $refStmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $references[] = [
                    'referenceType' => $row['reference_type'],
                    'fullName' => $row['full_name'],
                    'mobile' => $row['mobile'],
                    'relation' => $row['relation']
                ];
            }
            $refStmt->close();
        }

        // 3. Resolve absolute file URLs
        $baseUrlHelper = static function (?string $path): string {
            if (!$path || trim($path) === '') return '';
            if (function_exists('waqt_is_private_file_path') && waqt_is_private_file_path((string) $path)) {
                $url = waqt_signed_private_file_url((string) $path);
                if (str_starts_with($url, 'http://') && !str_contains($url, 'localhost')) {
                    $url = 'https://' . substr($url, 7);
                }
                return $url;
            }
            $host = $_SERVER['HTTP_HOST'] ?? 'waqtfinance.com';
            $cleanPath = ltrim(trim($path), '/\\.');
            if (strpos($cleanPath, 'http://') === 0 || strpos($cleanPath, 'https://') === 0) {
                if (str_starts_with($cleanPath, 'http://') && !str_contains($cleanPath, 'localhost')) {
                    return 'https://' . substr($cleanPath, 7);
                }
                return $cleanPath;
            }
            if ($host === 'localhost') {
                return 'http://localhost/waqt-finance/' . $cleanPath;
            }
            return 'https://' . $host . '/' . $cleanPath;
        };

        // 4. Construct payload
        $payload = [
            'sourceSystem' => 'waqtfinance',
            'sourceLeadId' => (string) ($application['id'] ?? ''),
            'sourceApplicationId' => $app_id,

            'name' => $application['full_name'] ?? '',
            'phone' => $application['mobile'] ?? '',
            'email' => $application['email'] ?? '',

            'loanAmount' => (float) ($application['loan_amount'] ?? 0),
            'loanPurpose' => $application['loan_purpose'] ?? '',
            'loanType' => 'payday',

            'dob' => $application['dob'] ?? '',
            'panNumber' => $application['pan_number'] ?? '',
            'uanNumber' => $application['uan_number'] ?? '',
            'aadhaarNumber' => waqt_sensitive_plaintext((string) ($application['aadhaar_number'] ?? '')),
            'aadhaarUniqueId' => $application['aadhaar_unique_id'] ?? '',

            'employmentStatus' => $application['employment_status'] ?? 'salaried',
            'monthlyIncome' => (float) ($application['monthly_income'] ?? 0),
            'companyName' => $application['company_name'] ?? '',
            'designation' => $application['designation'] ?? '',
            'officeEmail' => $application['office_email'] ?? '',
            'officeAddress' => $application['office_address'] ?? '',

            'city' => $application['city'] ?? '',
            'pincode' => $application['pincode'] ?? '',
            'latitude' => $application['latitude'] ?? '',
            'longitude' => $application['longitude'] ?? '',
            'gpsAddress' => $application['gps_address'] ?? '',

            'bankName' => $application['bank_name'] ?? '',
            'branchName' => $application['branch_name'] ?? '',
            'accountHolder' => $application['account_holder'] ?? '',
            'accountNumber' => $application['account_number'] ?? '',
            'ifscCode' => $application['ifsc_code'] ?? '',

            'references' => $references,

            'salarySlipCurrent' => $baseUrlHelper($application['salary_slip_current'] ?? ''),
            'salarySlipPrevious' => $baseUrlHelper($application['salary_slip_previous'] ?? ''),
            'salarySlipOld' => $baseUrlHelper($application['salary_slip_old'] ?? ''),
            'companyIdCard' => $baseUrlHelper($application['company_id_card'] ?? ''),
            'selfieImage' => $baseUrlHelper($application['selfie_image'] ?? ''),
            'videoKyc' => $baseUrlHelper($application['video_kyc'] ?? ''),

            'sourceStatus' => 'submitted',
            'consentVersion' => 'v1',
            'consents' => [
                'cibil' => true,
                'privacy' => [
                    'accepted' => true,
                    'text' => 'I authorize the company to verify my KYC, employment, bank details and credit history for loan processing.',
                    'version' => 'v1'
                ],
                'terms' => true
            ]
        ];

        // 5. Send POST request via cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Integration-API-Key: ' . $apiKey
            ],
            CURLOPT_TIMEOUT => 20
        ]);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $err = curl_error($curl);
            curl_close($curl);
            error_log("CRM Lead Push cURL Error: " . $err);
            return ['status' => 'error', 'message' => 'cURL error: ' . $err];
        }
        curl_close($curl);

        $res_data = json_decode($response, true);
        
        if ($http_code >= 200 && $http_code < 300) {
            error_log("CRM Lead Push Status: Code $http_code");
            return [
                'status' => 'success',
                'message' => 'Lead pushed to CRM successfully',
                'data' => $res_data
            ];
        }

        error_log("CRM Lead Push Status: Code $http_code | Redacted response: " . json_encode(waqt_redact_sensitive_data($res_data ?? ['response' => (string) $response]), JSON_UNESCAPED_SLASHES));

        return [
            'status' => 'error',
            'message' => $res_data['message'] ?? 'CRM returned status code ' . $http_code,
            'data' => $res_data
        ];
    }
}

if (!function_exists('push_common_lead_to_crm')) {
    function waqt_common_crm_document_descriptors(string $loanType, array $row): array
    {
        if ($loanType !== 'edi') {
            return [];
        }

        $registrationType = strtolower(trim((string) ($row['registration_type'] ?? '')));
        $registrationDocumentType = $registrationType === 'gst'
            ? 'GST_PROOF'
            : ($registrationType === 'udyam' ? 'UDYAM_AADHAAR' : 'BUSINESS_LICENSE');

        $documentFields = [
            'pan_card_doc' => 'PAN_CARD',
            'aadhaar_card_doc' => 'AADHAAR',
            'bank_statement_doc' => 'BANK_STATEMENT',
            'business_registration_doc' => $registrationDocumentType,
            'address_proof_doc' => 'ADDRESS_PROOF',
            'passport_photo_doc' => 'PASSPORT_PHOTO',
            'selfie_doc' => 'SELFIE',
            'shop_front_photo' => 'SHOP_PHOTO',
            'stock_photo' => 'STOCK_PHOTO',
            'stock_photo_two' => 'STOCK_PHOTO_TWO'
        ];

        $documents = [];
        foreach ($documentFields as $field => $documentType) {
            $path = trim((string) ($row[$field] ?? ''));
            if ($path === '' || !waqt_is_private_file_path($path)) {
                continue;
            }

            $relativePath = waqt_private_relative_path($path);
            $signedUrl = waqt_signed_private_file_url($path, 1800);
            $signedUrl = str_replace('/download-file.php?', '/download-file?', $signedUrl);
            if (str_starts_with($signedUrl, 'http://') && !str_contains($signedUrl, 'localhost')) {
                $signedUrl = 'https://' . substr($signedUrl, 7);
            }
            $documents[] = [
                'documentType' => $documentType,
                'fileName' => basename($relativePath),
                'documentUrl' => $signedUrl
            ];
        }

        return $documents;
    }

    function waqt_push_common_documents_to_crm(string $leadPushUrl, string $apiKey, string $crmLeadId, array $documents): array
    {
        if ($crmLeadId === '' || $documents === []) {
            return ['uploaded' => 0, 'failed' => 0, 'errors' => []];
        }

        $documentEndpoint = rtrim($leadPushUrl, '/') . '/' . rawurlencode($crmLeadId) . '/documents/from-url';
        $uploaded = 0;
        $errors = [];

        foreach ($documents as $document) {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $documentEndpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($document, JSON_UNESCAPED_SLASHES),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-API-Key: ' . $apiKey
                ],
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 30
            ]);

            $response = curl_exec($curl);
            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = $response === false ? curl_error($curl) : '';
            curl_close($curl);

            if ($curlError === '' && $httpCode >= 200 && $httpCode < 300) {
                $uploaded++;
                continue;
            }

            $errors[] = [
                'documentType' => (string) ($document['documentType'] ?? 'UNKNOWN'),
                'statusCode' => $httpCode,
                'message' => $curlError !== '' ? $curlError : 'CRM document import failed'
            ];
        }

        return [
            'uploaded' => $uploaded,
            'failed' => count($errors),
            'errors' => $errors
        ];
    }

    function push_common_lead_to_crm(mysqli $conn, string $loanType, string $app_id): array
    {
        $apiUrl = required_env('CRM_LEAD_PUSH_URL');
        $apiKey = required_env('CRM_LEAD_PUSH_API_KEY');

        if ($loanType === 'edi') {
            // Fetch EDI details
            $stmt = $conn->prepare("SELECT * FROM edi_loan_applications WHERE application_id = ? LIMIT 1");
            if (!$stmt) return ['status' => 'error', 'message' => 'Select prepare failed'];
            $stmt->bind_param("s", $app_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$row) return ['status' => 'error', 'message' => 'EDI application not found'];

            $notes = "Website EDI Loan ID: " . $app_id . "\n" .
                     "Employee Code: " . ($row['employee_code'] ?? 'N/A') . "\n" .
                     "Registration: " . ($row['registration_type'] ?? 'N/A') . "\n" .
                     "Business Age: " . ($row['business_age'] ?? 'N/A') . "\n" .
                     "Daily Sales: " . ($row['daily_sales'] ?? 'N/A') . "\n" .
                     "IFSC: " . ($row['ifsc_code'] ?? 'N/A') . "\n" .
                     "Account Number: " . ($row['account_number'] ?? 'N/A') . "\n" .
                     "GPS Latitude: " . ($row['latitude'] ?? 'N/A') . "\n" .
                     "GPS Longitude: " . ($row['longitude'] ?? 'N/A') . "\n" .
                     "GPS Address: " . ($row['gps_address'] ?? 'N/A') . "\n" .
                     "References:\n" .
                     "1. " . ($row['primary_ref_name'] ?? 'N/A') . " (" . ($row['primary_ref_mobile'] ?? 'N/A') . " - " . ($row['primary_ref_relation'] ?? 'N/A') . ")\n" .
                     "2. " . ($row['secondary_ref_name'] ?? 'N/A') . " (" . ($row['secondary_ref_mobile'] ?? 'N/A') . " - " . ($row['secondary_ref_relation'] ?? 'N/A') . ")";

            $payload = [
                'sourceSystem' => 'waqt-finance',
                'sourceApplicationId' => $app_id,
                'name' => $row['applicant_name'] ?? '',
                'mobile' => $row['mobile_number'] ?? '',
                'email' => $row['email'] ?? '',
                'panNumber' => $row['pan_number'] ?? '',
                'aadhaarNumber' => waqt_sensitive_plaintext((string) ($row['aadhaar_number'] ?? '')),
                'addressLine' => $row['current_address'] ?? '',
                'city' => $row['city'] ?? '',
                'pincode' => $row['pincode'] ?? '',
                'employmentType' => 'BUSINESS_OWNER',
                'employerName' => $row['shop_business_name'] ?? '',
                'monthlyIncome' => (float)($row['monthly_income'] ?? 0),
                'source' => 'waqt-finance',
                'loanPurpose' => $row['loan_purpose'] ?? 'Business Expansion',
                'loanAmount' => (float)($row['loan_amount'] ?? 0),
                'latitude' => $row['latitude'] ?? '',
                'longitude' => $row['longitude'] ?? '',
                'gpsAddress' => $row['gps_address'] ?? '',
                'notes' => $notes
            ];

            $regType = strtolower($row['registration_type'] ?? '');
            $proofVal = trim($row['business_proof'] ?? '');
            if ($regType === 'udyam') {
                $payload['udyamNumber'] = $proofVal;
            } elseif ($regType === 'gst') {
                $payload['gstNumber'] = $proofVal;
            }
        } else {
            // Fetch Common Loan (business, personal, etc.) details
            $stmt = $conn->prepare("SELECT * FROM loan_applied_master WHERE application_id = ? LIMIT 1");
            if (!$stmt) return ['status' => 'error', 'message' => 'Select prepare failed'];
            $stmt->bind_param("s", $app_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$row) return ['status' => 'error', 'message' => 'Common application not found'];

            $details = [];
            if (!empty($row['loan_details'])) {
                $details = json_decode($row['loan_details'], true) ?: [];
            }

            if ($loanType === 'business') {
                $notes = "Website Business Loan ID: " . $app_id . "\n" .
                         "Annual Turnover: " . ($details['annual_turnover'] ?? 'N/A');
                $loanPurpose = 'Business Expansion';
                $employerName = $details['business_name'] ?? '';
            } else {
                $notes = "Website " . ucfirst($loanType) . " Loan ID: " . $app_id;
                $loanPurpose = $details['loan_purpose'] ?? (ucfirst($loanType) . ' Loan');
                $employerName = $details['employer'] ?? '';
            }

            // Append GPS location metadata to notes
            $notes .= "\n" .
                      "GPS Latitude: " . ($details['latitude'] ?? 'N/A') . "\n" .
                      "GPS Longitude: " . ($details['longitude'] ?? 'N/A') . "\n" .
                      "GPS Address: " . ($details['gps_address'] ?? 'N/A');

            $payload = [
                'sourceSystem' => 'waqt-finance',
                'sourceApplicationId' => $app_id,
                'name' => $row['applicant_name'] ?? '',
                'mobile' => $row['mobile_number'] ?? '',
                'email' => $details['email'] ?? '',
                'panNumber' => $row['pancard_number'] ?? '',
                'aadhaarNumber' => waqt_sensitive_plaintext((string) ($details['aadhaar'] ?? '')),
                'addressLine' => $details['address'] ?? '',
                'city' => $row['city'] ?? '',
                'pincode' => $details['pincode'] ?? '',
                'employmentType' => $loanType === 'business' ? 'BUSINESS_OWNER' : 'SALARIED',
                'employerName' => $employerName,
                'monthlyIncome' => (float)($details['monthly_income'] ?? ($details['income'] ?? 0)),
                'source' => 'waqt-finance',
                'loanPurpose' => $loanPurpose,
                'loanAmount' => (float)($row['loan_amount'] ?? 0),
                'latitude' => $details['latitude'] ?? '',
                'longitude' => $details['longitude'] ?? '',
                'gpsAddress' => $details['gps_address'] ?? '',
                'notes' => $notes
            ];
        }

        $documents = waqt_common_crm_document_descriptors($loanType, $row);

        // Send POST request via cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $apiKey
            ],
            CURLOPT_TIMEOUT => 20
        ]);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $err = curl_error($curl);
            curl_close($curl);
            error_log("CRM Lead Push cURL Error ($loanType): " . $err);
            return ['status' => 'error', 'message' => 'cURL error: ' . $err];
        }
        curl_close($curl);

        $res_data = json_decode($response, true);
        if ($http_code >= 200 && $http_code < 300) {
            $crmLeadId = trim((string) ($res_data['data']['id'] ?? ''));
            $documentSync = waqt_push_common_documents_to_crm($apiUrl, $apiKey, $crmLeadId, $documents);
            error_log("CRM Common Lead Push Status ($loanType): Code $http_code");
            if ($documentSync['failed'] > 0) {
                error_log("CRM Common Document Push Status ($loanType): {$documentSync['uploaded']} uploaded, {$documentSync['failed']} failed");
            }
            return [
                'status' => 'success',
                'message' => 'Lead pushed to CRM successfully',
                'data' => $res_data,
                'documents' => $documentSync
            ];
        }

        error_log("CRM Common Lead Push Status ($loanType): Code $http_code | Redacted response: " . json_encode(waqt_redact_sensitive_data($res_data ?? ['response' => (string) $response]), JSON_UNESCAPED_SLASHES));

        return [
            'status' => 'error',
            'message' => $res_data['message'] ?? 'CRM returned status code ' . $http_code,
            'data' => $res_data
        ];
    }
}
