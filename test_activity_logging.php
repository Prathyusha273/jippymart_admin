<?php
/**
 * Test Activity Logging
 * This script creates a test log entry to verify the system is working
 */

// Bootstrap Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Testing Activity Logging\n";
echo "==========================\n\n";

try {
    // Create a mock user
    $user = new stdClass();
    $user->id = 1;
    $user->role_id = 1;
    $user->name = 'Test Admin';

    // Create ActivityLogger instance
    $activityLogger = new \App\Services\ActivityLogger();
    
    echo "✅ ActivityLogger created successfully\n";
    
    // Create a test log entry
    $result = $activityLogger->log(
        $user,
        'test',
        'created',
        'Test activity log entry from CLI script',
        null
    );
    
    if ($result) {
        echo "✅ Test log entry created successfully\n";
        echo "📝 Log Details:\n";
        echo "   - Module: test\n";
        echo "   - Action: created\n";
        echo "   - Description: Test activity log entry from CLI script\n";
        echo "   - User ID: 1\n";
        echo "   - User Type: admin\n";
    } else {
        echo "❌ Failed to create test log entry\n";
    }
    
    // Test retrieving logs
    echo "\n📊 Testing Log Retrieval...\n";
    try {
        $logs = $activityLogger->getLogsByModule('test', 5);
        echo "✅ Successfully retrieved " . count($logs) . " test logs\n";
        
        if (!empty($logs)) {
            echo "📋 Latest Test Log:\n";
            $latestLog = $logs[0];
            echo "   - User ID: " . $latestLog['user_id'] . "\n";
            echo "   - Module: " . $latestLog['module'] . "\n";
            echo "   - Action: " . $latestLog['action'] . "\n";
            echo "   - Description: " . $latestLog['description'] . "\n";
            echo "   - Timestamp: " . $latestLog['created_at'] . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Error retrieving logs: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n🎯 Next Steps:\n";
echo "==============\n";
echo "1. Check the Activity Logs page in browser\n";
echo "2. You should see the test log entry\n";
echo "3. Test with Cuisines module to generate real logs\n";
echo "4. Verify real-time updates are working\n\n";

echo "🔗 Test URLs:\n";
echo "=============\n";
echo "- Activity Logs: http://127.0.0.1:8000/activity-logs\n";
echo "- Cuisines: http://127.0.0.1:8000/cuisines\n";
