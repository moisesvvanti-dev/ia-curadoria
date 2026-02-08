<?php
// Script de teste de autenticação CodexPay

const CLIENT_ID = 'moisesviannavanti_0JI1GH7V';
const CLIENT_SECRET = 'xDnJgar9CfABwabVDefRmDbztKxIXB8qqPyPy4RLOf64vuzWXf5tKpPzArU3aMQ0OqzH2SMVzsuksALndEYFICY3BLKrSG8e5V6j';

$endpoints = [
    'https://api.codexpay.app/api/auth/login', // Correto conforme docs
    'https://api.codexpay.app/api/auth',
    'https://api.codexpay.app/api/login',
    'https://api.codexpay.app/api/v1/auth',
    'https://api.codexpay.app/oauth/token',
    'https://api.codexpay.app/api/oauth/token'
];

echo "Iniciando testes de autenticação...\n\n";

foreach ($endpoints as $url) {
    echo "------------------------------------------------\n";
    echo "Testando: $url\n";
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
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
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    echo "Status Code: $httpCode\n";
    echo "Response: " . substr($response, 0, 200) . "...\n"; // Mostra inicio da resposta
    
    if ($httpCode == 200 && strpos($response, 'token') !== false) {
        echo ">>> SUCESSO! Endpoint encontrado: $url <<<\n";
    }
}
?>
