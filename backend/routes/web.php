<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\OAuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return response()->json(['message' => 'Please use API authentication']);
})->name('login');

// OAuth routes without API prefix (for external redirects)
Route::get('/auth/{provider}/redirect', [OAuthController::class, 'redirect']);
Route::get('/auth/{provider}/callback', [OAuthController::class, 'callback']);

// Special Telegram OAuth callback
Route::post('/auth/telegram/callback', [OAuthController::class, 'telegramCallback']);

// Test OAuth page
Route::get('/test-oauth', function () {
    return '
    <html>
    <head><title>OAuth Test</title></head>
    <body>
        <h1>OAuth Test Page</h1>
        <p><a href="/auth/google/redirect" target="_blank">Test Google OAuth</a></p>
        <p><a href="/auth/yandex/redirect" target="_blank">Test Yandex OAuth</a></p>
        <p>Current time: ' . now() . '</p>
        <hr>
        <h2>Manual Test Links:</h2>
        <p><a href="https://api.fishtrackpro.ru/auth/google/redirect" target="_blank">Direct Google OAuth Link</a></p>
        <p><a href="https://api.fishtrackpro.ru/auth/yandex/redirect" target="_blank">Direct Yandex OAuth Link</a></p>
    </body>
    </html>
    ';
});

// Test OAuth callback simulation
Route::get('/test-oauth-callback', function () {
    return '
    <html>
    <head><title>OAuth Callback Test</title></head>
    <body>
        <h1>OAuth Callback Test</h1>
        <p>This simulates what happens after Google OAuth callback</p>
        <p><a href="/auth/google/callback?code=test_code&state=test_state">Test Google Callback</a></p>
        <p><a href="/auth/yandex/callback?code=test_code&state=test_state">Test Yandex Callback</a></p>
    </body>
    </html>
    ';
});