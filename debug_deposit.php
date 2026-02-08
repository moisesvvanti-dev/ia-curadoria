<?php
// debug_deposit.php

const CLIENT_ID = 'moisesviannavanti_0JI1GH7V';
const CLIENT_SECRET = 'xDnJgar9CfABwabVDefRmDbztKxIXB8qqPyPy4RLOf64vuzWXf5tKpPzArU3aMQ0OqzH2SMVzsuksALndEYFICY3BLKrSG8e5V6j';
const SPLIT_EMAIL = 'moisesvvanti@gmail.com';

function getAuthToken() {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.codexpay.app/api/auth/login",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'client_id' => CLIENT_ID,
            'client_secret' => CLIENT_SECRET,
            'grant_type' => 'client_credentials'
        ]),
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $response = curl_exec($curl);
    curl_close($curl);
    $data = json_decode($response, true);
    return $data['token'] ?? null;
}

$token = getAuthToken();
if (!$token) {
    die("Falha ao obter token\n");
}

echo "Token obtido: " . substr($token, 0, 20) . "...\n";

$externalId = 'DEBUG-' . time();
$payload = [
    "amount" => 10.00, // Valor fixo para teste
    "external_id" => $externalId,
    "clientCallbackUrl" => "https://negolasbankpro.netlify.app/callback",
    "payer" => [
        "name" => "Debug User",
        "email" => "debug@teste.com",
        "document" => "12345678901"
    ],
    "split" => [
        [
            "email" => SPLIT_EMAIL,
            "percentageSplit" => "5"
        ]
    ]
];

echo "Enviando solicitacao de deposito...\n";
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.codexpay.app/api/payments/deposit",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . $token,
        "Content-Type: application/json"
    ],
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "HTTP Code: $httpCode\n";
echo "Response Body:\n" . $response . "\n";
?>
