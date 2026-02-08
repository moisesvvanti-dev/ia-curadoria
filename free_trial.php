<?php
// free_trial.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (!is_array($input)) {
    echo json_encode(['success' => false, 'error' => 'Dados inválidos.']);
    exit;
}

$fingerprint = $input['fingerprint'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'];

if (empty($fingerprint)) {
    echo json_encode(['success' => false, 'error' => 'Fingerprint inválido.']);
    exit;
}

// Sanitize keys for Firebase
$safeIp = str_replace(['.', ':'], '_', $ip);
$safeFp = preg_replace('/[^a-zA-Z0-9]/', '', $fingerprint);

// Check Firebase for IP or Fingerprint
$FIREBASE_URL = "https://sunshinecursos-5f92a-default-rtdb.firebaseio.com/trials";

// Helper to count trials
function countTrials($data)
{
    if (!$data)
        return 0;
    // Check if it's a single legacy record (has 'started_at' directly)
    if (isset($data['started_at']))
        return 1;
    // Otherwise it's a list (array of records)
    return count($data);
}

// 1. Check IP
$ch = curl_init("$baseUrl/ip/$safeIp.json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$ipData = json_decode(curl_exec($ch), true);
curl_close($ch);

if (countTrials($ipData) >= 2) {
    echo json_encode(['success' => false, 'error' => 'Limite de 2 testes grátis atingido para este IP.']);
    exit;
}

// 2. Check Fingerprint
$ch = curl_init("$baseUrl/fp/$safeFp.json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$fpData = json_decode(curl_exec($ch), true);
curl_close($ch);

if (countTrials($fpData) >= 2) {
    echo json_encode(['success' => false, 'error' => 'Limite de 2 testes grátis atingido para este dispositivo.']);
    exit;
}

// 3. Register New Trial
$timestamp = time();
$expiresAt = $timestamp + 3600; // 1 Hour

$newRecord = [
    'started_at' => $timestamp,
    'expires_at' => $expiresAt,
    'ip' => $ip,
    'fingerprint' => $fingerprint
];

// Helper to update data
function updatedList($oldData, $newRec)
{
    if (!$oldData)
        return [$newRec];
    if (isset($oldData['started_at']))
        return [$oldData, $newRec]; // Convert single to list
    $oldData[] = $newRec; // Append
    return $oldData;
}

$newIpData = updatedList($ipData, $newRecord);
$newFpData = updatedList($fpData, $newRecord);

// Save IP Record
$ch = curl_init("$baseUrl/ip/$safeIp.json");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($newIpData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_exec($ch);
curl_close($ch);

// Save Fingerprint Record
$ch = curl_init("$baseUrl/fp/$safeFp.json");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($newFpData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_exec($ch);
curl_close($ch);

// Return success with expiry
echo json_encode([
    'success' => true,
    'expires_at' => $expiresAt,
    'message' => 'Teste liberado por 1 hora!'
]);
?>