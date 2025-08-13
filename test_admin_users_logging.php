<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\ActivityLogger;
use App\Models\User;
use Illuminate\Http\Request;

echo "=== Testing Admin Users Activity Logging ===\n\n";

try {
    // Test 1: Check if ActivityLogger can be instantiated
    echo "1. Testing ActivityLogger instantiation...\n";
    $logger = app(ActivityLogger::class);
    echo "✅ ActivityLogger instantiated successfully\n\n";

    // Test 2: Check if we can get a test user
    echo "2. Testing user retrieval...\n";
    $testUser = User::first();
    if ($testUser) {
        echo "✅ Found test user: " . $testUser->name . " (ID: " . $testUser->id . ")\n\n";
    } else {
        echo "❌ No users found in database\n\n";
        exit(1);
    }

    // Test 3: Test logging a sample activity
    echo "3. Testing activity logging...\n";
    $mockRequest = new Request();
    $mockRequest->merge([
        'name' => 'Test User',
        'email' => 'test@example.com'
    ]);

    $result = $logger->log(
        $testUser,
        'customers',
        'test_created',
        'Test: Created new admin user: Test User',
        $mockRequest
    );

    if ($result) {
        echo "✅ Activity logged successfully\n\n";
    } else {
        echo "❌ Failed to log activity\n\n";
    }

    // Test 4: Check if logs can be retrieved
    echo "4. Testing log retrieval...\n";
    $logs = $logger->getLogsByModule('customers', 5);
    if (!empty($logs)) {
        echo "✅ Found " . count($logs) . " logs for customers module\n";
        foreach ($logs as $log) {
            echo "   - " . $log['action'] . ": " . $log['description'] . " (User: " . $log['user_id'] . ")\n";
        }
    } else {
        echo "❌ No logs found for customers module\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
