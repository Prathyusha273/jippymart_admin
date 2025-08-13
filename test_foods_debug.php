<?php

/**
 * Foods Module Debug Test Script
 * Tests all foods operations to identify logging issues
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\ActivityLogger;
use Illuminate\Http\Request;

echo "🍽️  FOODS MODULE DEBUG TEST\n";
echo "=============================\n\n";

// Test 1: Check if ActivityLogger service is working
echo "1. Testing ActivityLogger Service...\n";
try {
    $activityLogger = app(ActivityLogger::class);
    echo "✅ ActivityLogger service loaded successfully\n";
} catch (Exception $e) {
    echo "❌ ActivityLogger service failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Test direct logging to Firestore
echo "\n2. Testing direct Firestore logging...\n";
try {
    $user = new stdClass();
    $user->id = 'test_user_123';
    $user->name = 'Test User';
    
    $request = new Request();
    $request->merge([
        'ip' => '127.0.0.1',
        'user_agent' => 'Test Script'
    ]);
    
    $result = $activityLogger->log($user, 'foods', 'test_operation', 'Test food operation from debug script', $request);
    
    if ($result) {
        echo "✅ Direct Firestore logging successful\n";
    } else {
        echo "❌ Direct Firestore logging failed\n";
    }
} catch (Exception $e) {
    echo "❌ Direct Firestore logging error: " . $e->getMessage() . "\n";
}

// Test 3: Test API endpoint
echo "\n3. Testing API endpoint...\n";
try {
    $client = new \GuzzleHttp\Client();
    $response = $client->post('http://127.0.0.1:8000/api/activity-logs/log', [
        'form_params' => [
            'module' => 'foods',
            'action' => 'api_test',
            'description' => 'Test from API endpoint'
        ],
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]
    ]);
    
    $statusCode = $response->getStatusCode();
    $body = json_decode($response->getBody(), true);
    
    echo "Status Code: $statusCode\n";
    echo "Response: " . json_encode($body, JSON_PRETTY_PRINT) . "\n";
    
    if ($statusCode === 200 && isset($body['success']) && $body['success']) {
        echo "✅ API endpoint test successful\n";
    } else {
        echo "❌ API endpoint test failed\n";
    }
} catch (Exception $e) {
    echo "❌ API endpoint test error: " . $e->getMessage() . "\n";
}

// Test 4: Check if foods Blade files have correct logActivity calls
echo "\n4. Checking foods Blade files for logActivity calls...\n";

$foodsFiles = [
    'resources/views/foods/create.blade.php',
    'resources/views/foods/edit.blade.php', 
    'resources/views/foods/index.blade.php'
];

foreach ($foodsFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $logActivityCount = substr_count($content, 'logActivity');
        $awaitCount = substr_count($content, 'await logActivity');
        
        echo "📄 $file:\n";
        echo "   - Total logActivity calls: $logActivityCount\n";
        echo "   - Await logActivity calls: $awaitCount\n";
        
        if ($logActivityCount > 0) {
            echo "   ✅ logActivity calls found\n";
        } else {
            echo "   ❌ No logActivity calls found\n";
        }
        
        if ($awaitCount > 0) {
            echo "   ✅ await logActivity calls found\n";
        } else {
            echo "   ❌ No await logActivity calls found\n";
        }
    } else {
        echo "❌ File not found: $file\n";
    }
}

// Test 5: Check if global activity logger is included in layout
echo "\n5. Checking global activity logger inclusion...\n";
$layoutFile = 'resources/views/layouts/app.blade.php';
if (file_exists($layoutFile)) {
    $content = file_get_contents($layoutFile);
    $globalLoggerIncluded = strpos($content, 'global-activity-logger.js') !== false;
    
    if ($globalLoggerIncluded) {
        echo "✅ Global activity logger is included in layout\n";
    } else {
        echo "❌ Global activity logger is NOT included in layout\n";
    }
} else {
    echo "❌ Layout file not found: $layoutFile\n";
}

// Test 6: Check if global activity logger file exists
echo "\n6. Checking global activity logger file...\n";
$loggerFile = 'public/js/global-activity-logger.js';
if (file_exists($loggerFile)) {
    $content = file_get_contents($loggerFile);
    $hasLogActivityFunction = strpos($content, 'window.logActivity') !== false;
    $hasPromiseReturn = strpos($content, 'return new Promise') !== false;
    
    if ($hasLogActivityFunction) {
        echo "✅ logActivity function found in global logger\n";
    } else {
        echo "❌ logActivity function NOT found in global logger\n";
    }
    
    if ($hasPromiseReturn) {
        echo "✅ Promise return found in global logger\n";
    } else {
        echo "❌ Promise return NOT found in global logger\n";
    }
} else {
    echo "❌ Global activity logger file not found: $loggerFile\n";
}

// Test 7: Check CSRF token configuration
echo "\n7. Checking CSRF token configuration...\n";
$csrfFile = 'app/Http/Middleware/VerifyCsrfToken.php';
if (file_exists($csrfFile)) {
    $content = file_get_contents($csrfFile);
    $csrfExcluded = strpos($content, 'api/activity-logs/log') !== false;
    
    if ($csrfExcluded) {
        echo "✅ Activity logs API endpoint is excluded from CSRF\n";
    } else {
        echo "❌ Activity logs API endpoint is NOT excluded from CSRF\n";
    }
} else {
    echo "❌ CSRF middleware file not found: $csrfFile\n";
}

// Test 8: Check Firebase configuration
echo "\n8. Checking Firebase configuration...\n";
$configFile = 'config/firestore.php';
if (file_exists($configFile)) {
    $content = file_get_contents($configFile);
    $hasProjectId = strpos($content, 'project_id') !== false;
    $hasKeyFilePath = strpos($content, 'key_file') !== false;
    
    if ($hasProjectId) {
        echo "✅ Firebase project_id configuration found\n";
    } else {
        echo "❌ Firebase project_id configuration NOT found\n";
    }
    
    if ($hasKeyFilePath) {
        echo "✅ Firebase key_file configuration found\n";
    } else {
        echo "❌ Firebase key_file configuration NOT found\n";
    }
} else {
    echo "❌ Firebase config file not found: $configFile\n";
}

// Test 9: Check if Firebase service account key exists
echo "\n9. Checking Firebase service account key...\n";
$keyFile = 'storage/app/firebase/serviceAccount.json';
if (file_exists($keyFile)) {
    echo "✅ Firebase service account key file exists\n";
} else {
    echo "❌ Firebase service account key file NOT found: $keyFile\n";
    echo "   This is required for Firestore operations\n";
}

echo "\n🔍 DEBUG SUMMARY:\n";
echo "================\n";
echo "If foods logging is not working, check the following:\n";
echo "1. Browser console for JavaScript errors\n";
echo "2. Network tab for failed AJAX requests\n";
echo "3. Laravel logs for backend errors\n";
echo "4. Firebase console for Firestore errors\n";
echo "5. Ensure Firebase service account key is properly configured\n";
echo "6. Verify CSRF token is being sent correctly\n";
echo "7. Check if logActivity function is available in browser console\n";

echo "\n🧪 To test in browser console:\n";
echo "=============================\n";
echo "1. Open browser console on foods page\n";
echo "2. Run: testLogActivity()\n";
echo "3. Check for any error messages\n";
echo "4. Try: logActivity('foods', 'test', 'Test from console')\n";

echo "\n✅ Debug test completed!\n";
