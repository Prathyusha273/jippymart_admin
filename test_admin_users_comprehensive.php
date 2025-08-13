<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\ActivityLogger;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

echo "=== Comprehensive Admin Users Activity Logging Test ===\n\n";

try {
    $logger = app(ActivityLogger::class);
    
    // Test 1: Check current logs for customers module
    echo "1. Checking current logs for customers module...\n";
    $currentLogs = $logger->getLogsByModule('customers', 10);
    echo "Found " . count($currentLogs) . " existing logs for customers module\n";
    foreach ($currentLogs as $log) {
        echo "   - " . $log['action'] . ": " . $log['description'] . " (User: " . $log['user_id'] . ")\n";
    }
    echo "\n";

    // Test 2: Test create operation logging
    echo "2. Testing create operation logging...\n";
    $createRequest = new Request();
    $createRequest->merge([
        'name' => 'Test Admin User',
        'email' => 'testadmin@example.com',
        'password' => 'password123',
        'confirm_password' => 'password123',
        'role' => 1
    ]);

    $testUser = User::first(); // Get authenticated user
    $result = $logger->log(
        $testUser,
        'customers',
        'created',
        'Created new admin user: Test Admin User',
        $createRequest
    );

    if ($result) {
        echo "✅ Create operation logged successfully\n";
    } else {
        echo "❌ Failed to log create operation\n";
    }

    // Test 3: Test update operation logging
    echo "3. Testing update operation logging...\n";
    $updateRequest = new Request();
    $updateRequest->merge([
        'name' => 'Updated Admin User',
        'email' => 'updatedadmin@example.com',
        'role' => 1
    ]);

    $result = $logger->log(
        $testUser,
        'customers',
        'updated',
        'Updated admin user: Test Admin User',
        $updateRequest
    );

    if ($result) {
        echo "✅ Update operation logged successfully\n";
    } else {
        echo "❌ Failed to log update operation\n";
    }

    // Test 4: Test single delete operation logging
    echo "4. Testing single delete operation logging...\n";
    $result = $logger->log(
        $testUser,
        'customers',
        'deleted',
        'Deleted admin user: Test Admin User',
        request()
    );

    if ($result) {
        echo "✅ Single delete operation logged successfully\n";
    } else {
        echo "❌ Failed to log single delete operation\n";
    }

    // Test 5: Test bulk delete operation logging
    echo "5. Testing bulk delete operation logging...\n";
    $result = $logger->log(
        $testUser,
        'customers',
        'bulk_deleted',
        'Bulk deleted admin users: User1, User2, User3',
        request()
    );

    if ($result) {
        echo "✅ Bulk delete operation logged successfully\n";
    } else {
        echo "❌ Failed to log bulk delete operation\n";
    }

    // Test 6: Check final logs
    echo "6. Checking final logs for customers module...\n";
    $finalLogs = $logger->getLogsByModule('customers', 10);
    echo "Found " . count($finalLogs) . " total logs for customers module\n";
    foreach ($finalLogs as $log) {
        echo "   - " . $log['action'] . ": " . $log['description'] . " (User: " . $log['user_id'] . ")\n";
    }

    // Test 7: Check if all operations are present
    $actions = array_column($finalLogs, 'action');
    $expectedActions = ['created', 'updated', 'deleted', 'bulk_deleted'];
    $missingActions = array_diff($expectedActions, $actions);
    
    if (empty($missingActions)) {
        echo "\n✅ All expected operations are being logged correctly!\n";
    } else {
        echo "\n❌ Missing operations: " . implode(', ', $missingActions) . "\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
