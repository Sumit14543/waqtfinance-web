<?php
declare(strict_types=1);

require_once '../config.php';

start_secure_session();
header('Content-Type: application/json; charset=utf-8');
require_post();
require_valid_csrf();

$app_id = (string) ($_SESSION['app_id'] ?? '');
$mobile = (string) ($_SESSION['mobile_number'] ?? '');

if ($app_id === '' || $mobile === '') {
    json_response(['status' => 'error', 'message' => 'Session expired'], 401);
}

if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
    json_response(['status' => 'error', 'message' => 'Invalid mobile number'], 422);
}

$payload = [
    "Mobile_Number" => $mobile,
    "Concent" => "Y",
    "Concent_Text" => "We confirm and undertake that valid end-user consent has been obtained for fetching PHONE TO UAN using MOBILE NUMBER, and that such consent remains active and unrevoked at the time of this request."
];

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://bifrost.unifers.ai/enrich/get-phone-to-uan",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        'Authorization: ' . required_env('BIFROST_API_TOKEN')
    ],
    CURLOPT_TIMEOUT => 15
]);


$response = curl_exec($curl);

if (curl_errno($curl)) {
    error_log('UAN fetch CURL error: ' . curl_error($curl));
    curl_close($curl);
    json_response(['status' => 'error', 'message' => 'UAN service is temporarily unavailable'], 502);
}

curl_close($curl);

$data = json_decode($response, true);

$uan = $data['data']['result']['uanNumber'] ?? null;
if (!is_string($uan) || $uan === '') {
    json_response(['status' => 'error', 'message' => 'UAN not found'], 404);
}

$conn = db();

$stmt = $conn->prepare("
        UPDATE loan_applications 
        SET uan_number = ? 
        WHERE application_id = ?
    ");
$stmt->bind_param("ss", $uan, $app_id);
$stmt->execute();

json_response(['status' => 'success', 'uan_number' => $uan]);
