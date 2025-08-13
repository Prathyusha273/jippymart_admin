<?php

// Test CSRF token generation and validation
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Start a session
session_start();

// Generate a CSRF token
$token = csrf_token();
echo "Generated CSRF Token: " . $token . "\n";

// Test if the token is valid
$request = new \Illuminate\Http\Request();
$request->merge([
    'module' => 'test',
    'action' => 'test_action',
    'description' => 'Test activity log',
    '_token' => $token
]);

// Validate the token
try {
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
        'module' => 'required|string',
        'action' => 'required|string',
        'description' => 'required|string',
        '_token' => 'required'
    ]);
    
    if ($validator->fails()) {
        echo "❌ Validation failed: " . $validator->errors()->toJson() . "\n";
    } else {
        echo "✅ Validation passed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Validation error: " . $e->getMessage() . "\n";
}

// Test the middleware
try {
    $middleware = app(\App\Http\Middleware\VerifyCsrfToken::class);
    echo "✅ CSRF middleware loaded successfully\n";
} catch (Exception $e) {
    echo "❌ CSRF middleware error: " . $e->getMessage() . "\n";
}
