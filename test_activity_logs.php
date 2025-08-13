<?php
/**
 * Activity Log System Test Script
 * 
 * This script tests the activity log implementation
 * Run this from your Laravel project root: php test_activity_logs.php
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\ActivityLogger;
use Illuminate\Http\Request;

echo "🧪 Activity Log System Test\n";
echo "==========================\n\n";

try {
    // Test 1: Check if ActivityLogger class exists
    echo "1. Testing ActivityLogger class...\n";
    $activityLogger = new ActivityLogger();
    echo "✅ ActivityLogger class instantiated successfully\n\n";

    // Test 2: Check configuration
    echo "2. Testing configuration...\n";
    $projectId = config('firestore.project_id');
    $credentials = config('firestore.credentials');
    
    if ($projectId) {
        echo "✅ Project ID configured: $projectId\n";
    } else {
        echo "❌ Project ID not configured\n";
    }
    
    if (file_exists($credentials)) {
        echo "✅ Credentials file exists: $credentials\n";
    } else {
        echo "❌ Credentials file not found: $credentials\n";
    }
    echo "\n";

    // Test 3: Test logging functionality (if credentials exist)
    if ($projectId && file_exists($credentials)) {
        echo "3. Testing logging functionality...\n";
        
        // Create a mock user
        $mockUser = (object) [
            'id' => 1,
            'role_id' => 1,
            'name' => 'Test User'
        ];
        
        // Create a mock request
        $request = new Request();
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $request->server->set('HTTP_USER_AGENT', 'Test Script');
        
        // Test logging
        $result = $activityLogger->log(
            $mockUser,
            'test',
            'test_action',
            'Test log entry from script',
            $request
        );
        
        if ($result) {
            echo "✅ Log entry created successfully\n";
        } else {
            echo "❌ Failed to create log entry\n";
        }
    } else {
        echo "3. Skipping logging test (Firebase not configured)\n";
    }
    echo "\n";

    // Test 4: Check routes
    echo "4. Testing routes...\n";
    $routes = [
        '/activity-logs' => 'Activity Logs Page',
        '/api/activity-logs/log' => 'Log Activity API',
        '/api/activity-logs/all' => 'Get All Logs API',
    ];
    
    foreach ($routes as $route => $description) {
        try {
            $response = app('router')->dispatch(
                \Illuminate\Http\Request::create($route, 'GET')
            );
            echo "✅ Route exists: $route ($description)\n";
        } catch (Exception $e) {
            echo "❌ Route error: $route - " . $e->getMessage() . "\n";
        }
    }
    echo "\n";

    // Test 5: Check files exist
    echo "5. Testing file structure...\n";
    $files = [
        'app/Services/ActivityLogger.php' => 'ActivityLogger Service',
        'app/Http/Controllers/ActivityLogController.php' => 'ActivityLog Controller',
        'resources/views/activity_logs/index.blade.php' => 'Activity Logs View',
        'public/js/activity-logger.js' => 'Activity Logger JS',
        'config/firestore.php' => 'Firestore Config',
    ];
    
    foreach ($files as $file => $description) {
        if (file_exists($file)) {
            echo "✅ File exists: $file ($description)\n";
        } else {
            echo "❌ File missing: $file ($description)\n";
        }
    }
    echo "\n";

    // Test 6: Check menu integration
    echo "6. Testing menu integration...\n";
    $menuFile = 'resources/views/layouts/menu.blade.php';
    if (file_exists($menuFile)) {
        $menuContent = file_get_contents($menuFile);
        if (strpos($menuContent, 'activity-logs') !== false) {
            echo "✅ Activity logs menu item found\n";
        } else {
            echo "❌ Activity logs menu item not found\n";
        }
    } else {
        echo "❌ Menu file not found\n";
    }
    echo "\n";

    echo "🎉 Test completed!\n\n";
    
    if ($projectId && file_exists($credentials)) {
        echo "📋 Next Steps:\n";
        echo "1. Visit /activity-logs to see the logs page\n";
        echo "2. Test with cuisines module\n";
        echo "3. Check real-time updates\n";
    } else {
        echo "📋 Configuration Required:\n";
        echo "1. Set FIRESTORE_PROJECT_ID in .env\n";
        echo "2. Place service account key in storage/app/firebase/serviceAccount.json\n";
        echo "3. Run this test again\n";
    }

} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
