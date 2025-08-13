<?php

// Test the activity log API endpoint
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get CSRF token
$response = file_get_contents('http://127.0.0.1:8000/activity-logs');
preg_match('/<meta name="csrf-token" content="([^"]+)"/', $response, $matches);
$csrfToken = $matches[1] ?? '';

echo "CSRF Token: " . $csrfToken . "\n";

// Test the API endpoint
$url = 'http://127.0.0.1:8000/api/activity-logs/log';
$data = [
    'module' => 'test',
    'action' => 'test_action',
    'description' => 'Test activity log from API',
    '_token' => $csrfToken
];

$options = [
    'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "API Response: " . $result . "\n";

if ($result === false) {
    echo "Error: Failed to connect to API endpoint\n";
} else {
    $response = json_decode($result, true);
    if ($response && isset($response['success'])) {
        echo "Success: " . ($response['success'] ? 'true' : 'false') . "\n";
        echo "Message: " . ($response['message'] ?? 'No message') . "\n";
    } else {
        echo "Error: Invalid response format\n";
    }
}
