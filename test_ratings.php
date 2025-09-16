<?php

// Test ratings endpoint specifically
$baseUrl = 'https://api.fishtrackpro.ru/api/v1';

echo "Testing ratings endpoint...\n\n";

// Login first
$loginData = json_encode([
    'email' => 'test@example.com',
    'password' => 'password'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $loginData
    ]
]);

$response = file_get_contents($baseUrl . '/auth/login', false, $context);
$loginResult = json_decode($response, true);
$token = $loginResult['token'] ?? null;

if ($token) {
    echo "Token received: " . substr($token, 0, 20) . "...\n\n";
    
    // Test ratings
    echo "Testing ratings endpoint...\n";
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Authorization: Bearer ' . $token
        ]
    ]);
    
    $response = file_get_contents($baseUrl . '/ratings', false, $context);
    echo "Ratings response: " . $response . "\n\n";
    
    // Check HTTP response headers
    $headers = $http_response_header ?? [];
    echo "HTTP Headers:\n";
    foreach ($headers as $header) {
        echo $header . "\n";
    }
    
} else {
    echo "No token received\n";
}

echo "Testing completed.\n";





