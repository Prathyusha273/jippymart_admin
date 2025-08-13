<?php

// Test activity logging directly within Laravel context
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Create a mock user
$user = new stdClass();
$user->id = 1;
$user->role_id = 1;

// Create a mock request
$request = new \Illuminate\Http\Request();
$request->merge([
    'module' => 'test',
    'action' => 'test_action',
    'description' => 'Test activity log from internal script'
]);

// Test the ActivityLogger service directly
try {
    $activityLogger = app(\App\Services\ActivityLogger::class);
    $result = $activityLogger->log($user, 'test', 'test_action', 'Test activity log from internal script', $request);
    
    echo "Activity Logger Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
    
    if ($result) {
        echo "✅ Activity logged successfully to Firestore\n";
    } else {
        echo "❌ Failed to log activity\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test the controller method directly
try {
    $controller = app(\App\Http\Controllers\ActivityLogController::class);
    
    // Mock the authenticated user
    auth()->loginUsingId(1);
    
    $result = $controller->logActivity($request);
    
    echo "Controller Result: " . $result->getContent() . "\n";
    
} catch (Exception $e) {
    echo "❌ Controller Error: " . $e->getMessage() . "\n";
}
