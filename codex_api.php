<?php
// ====== CONFIGURAÇÕES ======
// Carregar .env simples
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

define('CLIENT_ID', $_ENV['CODEX_CLIENT_ID'] ?? '');
define('CLIENT_SECRET', $_ENV['CODEX_CLIENT_SECRET'] ?? '');
define('CALLBACK_URL', $_ENV['CODEX_CALLBACK_URL'] ?? 'https://negolasbankpro.netlify.app/webhook.php');

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Recebe dados do POST
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// Verifica se JSON é válido
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'error' => 'JSON inválido']);
    exit;
}

// Pega a action
$action = $input['action'] ?? '';

// Se action estiver vazia, retorna erro
if (empty($action)) {
    echo json_encode(['success' => false, 'error' => 'Action não informada']);
    exit;
}

// ====== AUTENTICAÇÃO ======
function getAuthToken()
{
    $cacheFile = __DIR__ . '/token_cache.json';

    if (file_exists($cacheFile)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if ($cached && isset($cached['expires_at']) && $cached['expires_at'] > time()) {
            return $cached['token'];
        }
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.codexpay.app/api/auth/login",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'client_id' => CLIENT_ID,
            'client_secret' => CLIENT_SECRET
        ]),
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    $data = json_decode($response, true);

    if ($httpCode === 200 && isset($data['token'])) {
        file_put_contents($cacheFile, json_encode([
            'token' => $data['token'],
            'expires_at' => time() + 3500
        ]));
        return $data['token'];
    }

    return null;
}

// ====== ENVIAR REQUISIÇÃO ======
function sendCodexRequest($endpoint, $payload)
{
    $token = getAuthToken();

    if (!$token) {
        return ['error' => 'Falha na autenticação'];
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.codexpay.app/api" . $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . $token,
            "Content-Type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if (!$response) {
        return ['error' => 'Erro de conexão'];
    }

    $result = json_decode($response, true);

    if ($httpCode < 200 || $httpCode >= 300) {
        return ['error' => $result['message'] ?? 'Erro HTTP ' . $httpCode];
    }

    return $result;
}

// ====== DEPOSIT / ADD BALANCE ======
if ($action === 'deposit' || $action === 'add_balance') {
    $amount = floatval($input['amount'] ?? 0);

    if ($amount < 20) {
        echo json_encode(['success' => false, 'error' => 'Valor da assinatura é R$20,00']);
        exit;
    }

    $userId = $input['userid'] ?? 'unknown'; // This should now be the User Email (sanitized)
    $externalId = 'SUB-' . time() . '-' . rand(1000, 9999);
    $txType = ($action === 'add_balance') ? 'BALANCE' : 'PARTICIPATION';

    $payerName = $input['username'] ?? 'Usuario';
    $payerEmail = $input['useremail'] ?? 'email@teste.com';
    $payerDoc = $input['userdocument'] ?? '12345678901';

    // Payload COM SPLIT (5% para Admin)
    $payload = [
        "amount" => $amount,
        "external_id" => $externalId,
        "clientCallbackUrl" => CALLBACK_URL,
        "payer" => [
            "name" => $payerName,
            "email" => $payerEmail,
            "document" => $payerDoc
        ],
        "splits" => [
            [
                "email" => "moisesvvanti@gmail.com",
                "percentage" => 5
            ]
        ]
    ];

    $response = sendCodexRequest('/payments/deposit', $payload);

    if (isset($response['qrCodeResponse'])) {
        $qrData = $response['qrCodeResponse'];

        $transaction = [
            'userid' => $userId,
            'externalid' => $externalId,
            'transaction_id_gateway' => $qrData['transactionId'] ?? null,
            'type' => $txType,
            'amount' => $amount,
            'status' => 'PENDING',
            'qrcode' => $qrData['qrcode'],
            'created_at' => time()
        ];

        $qrCodeImage = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrData['qrcode']);

        $fbBase = "https://sunshinecursos-5f92a-default-rtdb.firebaseio.com";
        $url = $fbBase . '/transactions/' . $externalId . '.json';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($transaction));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);

        echo json_encode([
            'success' => true,
            'externalid' => $externalId,
            'amount' => $amount,
            'qrcode' => $qrCodeImage,
            'pixcopy' => $qrData['qrcode']
        ]);
    } else {
        $errorMsg = $response['message'] ?? ($response['error'] ?? 'Erro ao gerar PIX');
        echo json_encode(['success' => false, 'error' => $errorMsg]);
    }
    exit;
}

// ====== CHECK STATUS ======
if ($action === 'check_status') {
    $externalId = $input['external_id'] ?? '';

    if (empty($externalId)) {
        echo json_encode(['success' => false, 'error' => 'ID não informado']);
        exit;
    }

    // Checking status on Firebase first (if webhook updated it)
    // In a real scenario, we might query Codex directly if we didn't get the webhook yet.
    // For this implementation, let's query Codex Pay API properly.

    // Codex Pay 'Get Transaction by External ID' or similar. 
    // Assuming endpoint: /payments/transactions/by-external-id/{id} or similar
    // Since I don't have the exact docs here, I'll assume we can query by the ID we sent.
    // If not, we rely on the webhook. Let's try to query the transaction from Codex.

    // Fallback: Check Firebase if implemented, but here we will try to query Codex API.
    // Note: If Codex API doesn't support query by external_id, we would rely on our DB.
    // Let's implement a direct query if possible, or check our local DB (Firebase)

    // Using Firebase REST to check our own record (updated by Webhook)
    $FIREBASE_URL = "https://sunshinecursos-5f92a-default-rtdb.firebaseio.com/transactions/$externalId.json";
    $ch = curl_init($FIREBASE_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $fbResponse = curl_exec($ch);
    curl_close($ch);

    $txData = json_decode($fbResponse, true);

    if ($txData) {
        $status = $txData['status'] ?? 'PENDING';
        if ($status === 'PAID' || $status === 'CONFIRMED' || $status === 'COMPLETED') {
            echo json_encode(['success' => true, 'status' => 'PAID']);
        } else {
            echo json_encode(['success' => true, 'status' => 'PENDING']);
        }
    } else {
        // Not found in our DB, maybe query Codex directly?
        // Simulating Codex check response if not in DB yet (or if DB write failed)
        // For now, return pending
        echo json_encode(['success' => true, 'status' => 'PENDING']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Ação inválida: ' . $action]);
?>