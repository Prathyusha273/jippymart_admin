<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\ActivityLogger;
use App\Models\User;
use Illuminate\Http\Request;

echo "=== Comprehensive Users Activity Logging Test ===\n\n";

try {
    $logger = app(ActivityLogger::class);
    
    // Test 1: Check current logs for users module
    echo "1. Checking current logs for users module...\n";
    $currentLogs = $logger->getLogsByModule('users', 10);
    echo "Found " . count($currentLogs) . " existing logs for users module\n";
    foreach ($currentLogs as $log) {
        echo "   - " . $log['action'] . ": " . $log['description'] . " (User: " . $log['user_id'] . ")\n";
    }
    echo "\n";

    // Test 2: Test create operation logging
    echo "2. Testing create operation logging...\n";
    $createRequest = new Request();
    $createRequest->merge([
        'firstName' => 'Test',
        'lastName' => 'User',
        'email' => 'testuser@example.com',
        'phoneNumber' => '1234567890'
    ]);

    $testUser = User::first(); // Get authenticated user
    $result = $logger->log(
        $testUser,
        'users',
        'created',
        'Created new user: Test User',
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
        'firstName' => 'Updated',
        'lastName' => 'User',
        'email' => 'updateduser@example.com'
    ]);

    $result = $logger->log(
        $testUser,
        'users',
        'updated',
        'Updated user: Test User',
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
        'users',
        'deleted',
        'Deleted user: Test User',
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
        'users',
        'bulk_deleted',
        'Bulk deleted users: User1, User2, User3',
        request()
    );

    if ($result) {
        echo "✅ Bulk delete operation logged successfully\n";
    } else {
        echo "❌ Failed to log bulk delete operation\n";
    }

    // Test 6: Test activate operation logging
    echo "6. Testing activate operation logging...\n";
    $result = $logger->log(
        $testUser,
        'users',
        'activated',
        'Activated user: Test User',
        request()
    );

    if ($result) {
        echo "✅ Activate operation logged successfully\n";
    } else {
        echo "❌ Failed to log activate operation\n";
    }

    // Test 7: Test deactivate operation logging
    echo "7. Testing deactivate operation logging...\n";
    $result = $logger->log(
        $testUser,
        'users',
        'deactivated',
        'Deactivated user: Test User',
        request()
    );

    if ($result) {
        echo "✅ Deactivate operation logged successfully\n";
    } else {
        echo "❌ Failed to log deactivate operation\n";
    }

    // Test 8: Check final logs
    echo "8. Checking final logs for users module...\n";
    $finalLogs = $logger->getLogsByModule('users', 10);
    echo "Found " . count($finalLogs) . " total logs for users module\n";
    foreach ($finalLogs as $log) {
        echo "   - " . $log['action'] . ": " . $log['description'] . " (User: " . $log['user_id'] . ")\n";
    }

    // Test 9: Check if all operations are present
    $actions = array_column($finalLogs, 'action');
    $expectedActions = ['created', 'updated', 'deleted', 'bulk_deleted', 'activated', 'deactivated'];
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
