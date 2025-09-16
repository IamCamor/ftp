<?php

// Test catch creation endpoint specifically
$baseUrl = 'https://api.fishtrackpro.ru/api/v1';

echo "Testing catch creation endpoint...\n\n";

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
    
    // Test catch creation
    echo "Testing catch creation...\n";
    $catchData = json_encode([
        'lat' => 55.7558,
        'lng' => 37.6176,
        'species' => 'Test Fish',
        'weight' => 1.5,
        'description' => 'Test catch from API'
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json' . "\r\n" . 'Authorization: Bearer ' . $token,
            'content' => $catchData
        ]
    ]);
    
    $response = file_get_contents($baseUrl . '/catch', false, $context);
    echo "Catch creation response: " . $response . "\n\n";
    
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





