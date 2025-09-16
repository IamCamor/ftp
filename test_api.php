<?php

// Test API endpoints
$baseUrl = 'https://api.fishtrackpro.ru/api/v1';

echo "Testing API endpoints...\n\n";

// Test 1: Health check
echo "1. Testing health endpoint...\n";
$response = file_get_contents($baseUrl . '/../health');
echo "Health response: " . $response . "\n\n";

// Test 2: Login
echo "2. Testing login...\n";
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
echo "Login response: " . $response . "\n\n";

// Parse token from response
$loginResult = json_decode($response, true);
$token = $loginResult['token'] ?? null;

if ($token) {
    echo "Token received: " . substr($token, 0, 20) . "...\n\n";
    
    // Test 3: Profile
    echo "3. Testing profile endpoint...\n";
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Authorization: Bearer ' . $token
        ]
    ]);
    
    $response = file_get_contents($baseUrl . '/profile/me', false, $context);
    echo "Profile response: " . $response . "\n\n";
    
    // Test 4: Ratings
    echo "4. Testing ratings endpoint...\n";
    $response = file_get_contents($baseUrl . '/ratings', false, $context);
    echo "Ratings response: " . $response . "\n\n";
    
    // Test 5: Catch creation
    echo "5. Testing catch creation...\n";
    $catchData = json_encode([
        'lat' => 55.7558,
        'lng' => 37.6176,
        'species' => 'Test Fish',
        'weight' => 1.5,
        'description' => 'Test catch'
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
    
} else {
    echo "No token received, skipping authenticated tests\n";
}

echo "API testing completed.\n";
