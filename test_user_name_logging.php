<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\ActivityLogger;
use App\Models\User;
use Illuminate\Http\Request;

echo "🧪 Testing User Name Logging\n";
echo "============================\n\n";

try {
    $activityLogger = app(ActivityLogger::class);
    echo "✅ ActivityLogger service loaded successfully\n\n";

    // Test 1: Test with a real user from database
    echo "1. Testing with real user from database...\n";
    $realUser = User::first();
    if ($realUser) {
        echo "   Found user: " . $realUser->name . " (ID: " . $realUser->id . ")\n";
        
        $result = $activityLogger->log(
            $realUser,
            'test_user_name',
            'created',
            'Test activity with real user name: ' . $realUser->name,
            null
        );
        
        if ($result) {
            echo "   ✅ Activity logged successfully with real user\n";
        } else {
            echo "   ❌ Failed to log activity with real user\n";
        }
    } else {
        echo "   ❌ No users found in database\n";
    }

    // Test 2: Test with mock user object
    echo "\n2. Testing with mock user object...\n";
    $mockUser = new stdClass();
    $mockUser->id = 999;
    $mockUser->name = 'Test User Name';
    $mockUser->email = 'test@example.com';
    $mockUser->role_id = 1;
    
    $result = $activityLogger->log(
        $mockUser,
        'test_user_name',
        'updated',
        'Test activity with mock user: ' . $mockUser->name,
        null
    );
    
    if ($result) {
        echo "   ✅ Activity logged successfully with mock user\n";
    } else {
        echo "   ❌ Failed to log activity with mock user\n";
    }

    // Test 3: Test with user having first_name and last_name
    echo "\n3. Testing with first_name and last_name...\n";
    $nameUser = new stdClass();
    $nameUser->id = 888;
    $nameUser->first_name = 'John';
    $nameUser->last_name = 'Doe';
    $nameUser->email = 'john.doe@example.com';
    
    $result = $activityLogger->log(
        $nameUser,
        'test_user_name',
        'deleted',
        'Test activity with first/last name user',
        null
    );
    
    if ($result) {
        echo "   ✅ Activity logged successfully with first/last name user\n";
    } else {
        echo "   ❌ Failed to log activity with first/last name user\n";
    }

    // Test 4: Test with API user (no name)
    echo "\n4. Testing with API user (no name)...\n";
    $apiUser = new stdClass();
    $apiUser->id = 'api_user';
    // No name property
    
    $result = $activityLogger->log(
        $apiUser,
        'test_user_name',
        'viewed',
        'Test activity with API user',
        null
    );
    
    if ($result) {
        echo "   ✅ Activity logged successfully with API user\n";
    } else {
        echo "   ❌ Failed to log activity with API user\n";
    }

    // Test 5: Check recent logs to verify user_name field
    echo "\n5. Checking recent logs for user_name field...\n";
    $logs = $activityLogger->getLogsByModule('test_user_name', 10);
    
    if (!empty($logs)) {
        echo "   Found " . count($logs) . " test logs:\n";
        foreach ($logs as $log) {
            echo "   - User: " . ($log['user_name'] ?? 'No name') . " (ID: " . $log['user_id'] . ")\n";
            echo "     Action: " . $log['action'] . " - " . $log['description'] . "\n";
        }
    } else {
        echo "   ❌ No test logs found\n";
    }

    echo "\n✅ User name logging test completed!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
