<?php
/**
 * Comprehensive Activity Log System Test
 * This script tests the entire workflow from frontend to backend to Firebase
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 COMPREHENSIVE ACTIVITY LOG SYSTEM TEST\n";
echo "==========================================\n\n";

// Test 1: Check if ActivityLogger service exists and can be instantiated
echo "1. Testing ActivityLogger Service...\n";
try {
    $activityLogger = app(\App\Services\ActivityLogger::class);
    echo "   ✅ ActivityLogger service instantiated successfully\n";
} catch (Exception $e) {
    echo "   ❌ ActivityLogger service failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check Firebase configuration
echo "\n2. Testing Firebase Configuration...\n";
try {
    $config = config('firestore');
    echo "   Project ID: " . $config['project_id'] . "\n";
    echo "   Credentials: " . $config['credentials'] . "\n";
    echo "   Collection: " . $config['collection'] . "\n";
    
    if (file_exists($config['credentials'])) {
        echo "   ✅ Firebase credentials file exists\n";
    } else {
        echo "   ❌ Firebase credentials file missing\n";
    }
} catch (Exception $e) {
    echo "   ❌ Firebase config error: " . $e->getMessage() . "\n";
}

// Test 3: Test direct Firestore connection
echo "\n3. Testing Firestore Connection...\n";
try {
    $firestore = new Google\Cloud\Firestore\FirestoreClient([
        'projectId' => config('firestore.project_id'),
        'keyFilePath' => config('firestore.credentials'),
        'databaseId' => config('firestore.database_id'),
    ]);
    echo "   ✅ Firestore client created successfully\n";
    
    // Test writing a document
    $testData = [
        'test' => true,
        'timestamp' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
        'message' => 'Test connection from PHP script'
    ];
    
    $docRef = $firestore->collection('activity_logs')->add($testData);
    echo "   ✅ Test document written to Firestore: " . $docRef->id() . "\n";
    
    // Clean up test document
    $firestore->collection('activity_logs')->document($docRef->id())->delete();
    echo "   ✅ Test document cleaned up\n";
    
} catch (Exception $e) {
    echo "   ❌ Firestore connection failed: " . $e->getMessage() . "\n";
}

// Test 4: Test ActivityLogger log method
echo "\n4. Testing ActivityLogger Log Method...\n";
try {
    // Create a mock user
    $mockUser = new stdClass();
    $mockUser->id = 1;
    $mockUser->role_id = 1;
    
    // Create a mock request
    $request = new \Illuminate\Http\Request();
    $request->server->set('REMOTE_ADDR', '127.0.0.1');
    $request->server->set('HTTP_USER_AGENT', 'Test Script');
    
    $result = $activityLogger->log($mockUser, 'test', 'test_action', 'Test log from script', $request);
    
    if ($result) {
        echo "   ✅ ActivityLogger log method executed successfully\n";
    } else {
        echo "   ❌ ActivityLogger log method returned false\n";
    }
} catch (Exception $e) {
    echo "   ❌ ActivityLogger log method failed: " . $e->getMessage() . "\n";
}

// Test 5: Test API endpoint
echo "\n5. Testing API Endpoint...\n";
try {
    // Get CSRF token
    $response = file_get_contents('http://127.0.0.1:8000/activity-logs');
    if ($response === false) {
        echo "   ❌ Cannot access activity-logs page\n";
    } else {
        echo "   ✅ Activity-logs page accessible\n";
        
        // Extract CSRF token from response
        if (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $response, $matches)) {
            $csrfToken = $matches[1];
            echo "   ✅ CSRF token extracted: " . substr($csrfToken, 0, 10) . "...\n";
            
            // Test API call
            $postData = http_build_query([
                'module' => 'test',
                'action' => 'test_action',
                'description' => 'Test from script',
                '_token' => $csrfToken
            ]);
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Content-Type: application/x-www-form-urlencoded',
                        'Content-Length: ' . strlen($postData)
                    ],
                    'content' => $postData
                ]
            ]);
            
            $apiResponse = file_get_contents('http://127.0.0.1:8000/api/activity-logs/log', false, $context);
            
            if ($apiResponse !== false) {
                echo "   ✅ API endpoint responded: " . substr($apiResponse, 0, 100) . "...\n";
            } else {
                echo "   ❌ API endpoint failed to respond\n";
            }
        } else {
            echo "   ❌ CSRF token not found in page\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ API test failed: " . $e->getMessage() . "\n";
}

// Test 6: Check routes
echo "\n6. Testing Routes...\n";
try {
    $routes = [
        '/activity-logs' => 'GET',
        '/api/activity-logs/log' => 'POST',
        '/api/activity-logs/module/test' => 'GET',
        '/api/activity-logs/all' => 'GET',
        '/api/activity-logs/cuisines' => 'GET'
    ];
    
    foreach ($routes as $route => $method) {
        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'timeout' => 5
            ]
        ]);
        
        $response = @file_get_contents('http://127.0.0.1:8000' . $route, false, $context);
        
        if ($response !== false) {
            echo "   ✅ Route {$route} ({$method}) - OK\n";
        } else {
            $httpResponse = $http_response_header ?? [];
            $statusCode = 0;
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $httpResponse[0] ?? '', $matches)) {
                $statusCode = $matches[1];
            }
            echo "   ❌ Route {$route} ({$method}) - HTTP {$statusCode}\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Route test failed: " . $e->getMessage() . "\n";
}

// Test 7: Check JavaScript files
echo "\n7. Testing JavaScript Files...\n";
$jsFiles = [
    'public/js/global-activity-logger.js',
    'resources/views/activity_logs/index.blade.php',
    'resources/views/cuisines/create.blade.php',
    'resources/views/cuisines/edit.blade.php',
    'resources/views/cuisines/index.blade.php'
];

foreach ($jsFiles as $file) {
    if (file_exists($file)) {
        echo "   ✅ {$file} exists\n";
        
        // Check for logActivity calls in cuisine files
        if (strpos($file, 'cuisines') !== false) {
            $content = file_get_contents($file);
            if (strpos($content, 'logActivity') !== false) {
                echo "      ✅ Contains logActivity calls\n";
            } else {
                echo "      ❌ No logActivity calls found\n";
            }
        }
    } else {
        echo "   ❌ {$file} missing\n";
    }
}

// Test 8: Check menu integration
echo "\n8. Testing Menu Integration...\n";
$menuFile = 'resources/views/layouts/menu.blade.php';
if (file_exists($menuFile)) {
    $menuContent = file_get_contents($menuFile);
    if (strpos($menuContent, 'activity-logs') !== false) {
        echo "   ✅ Activity logs menu item found\n";
    } else {
        echo "   ❌ Activity logs menu item not found\n";
    }
} else {
    echo "   ❌ Menu file not found\n";
}

// Test 9: Check layout file
echo "\n9. Testing Layout File...\n";
$layoutFile = 'resources/views/layouts/app.blade.php';
if (file_exists($layoutFile)) {
    $layoutContent = file_get_contents($layoutFile);
    
    $checks = [
        'global-activity-logger.js' => 'Global activity logger script',
        'firebase-app-compat.js' => 'Firebase app compat script',
        'firebase-firestore-compat.js' => 'Firebase firestore compat script',
        'csrf-token' => 'CSRF token meta tag'
    ];
    
    foreach ($checks as $search => $description) {
        if (strpos($layoutContent, $search) !== false) {
            echo "   ✅ {$description} found\n";
        } else {
            echo "   ❌ {$description} missing\n";
        }
    }
} else {
    echo "   ❌ Layout file not found\n";
}

echo "\n==========================================\n";
echo "🎯 SUMMARY & RECOMMENDATIONS\n";
echo "==========================================\n\n";

echo "Based on the test results above, here are the likely issues:\n\n";

echo "1. 🔧 IMMEDIATE FIXES NEEDED:\n";
echo "   - Clear Laravel caches: php artisan config:clear && php artisan cache:clear && php artisan view:clear\n";
echo "   - Restart your web server\n";
echo "   - Check browser console for JavaScript errors\n\n";

echo "2. 🧪 TESTING STEPS:\n";
echo "   - Open browser console on cuisine pages\n";
echo "   - Try calling logActivity('test', 'test', 'test') manually\n";
echo "   - Check Network tab for failed AJAX requests\n";
echo "   - Verify CSRF token is present in page source\n\n";

echo "3. 🔍 DEBUGGING TIPS:\n";
echo "   - Add console.log statements to cuisine JavaScript files\n";
echo "   - Check if Firebase is properly initialized on cuisine pages\n";
echo "   - Verify that cuisine save operations complete successfully\n";
echo "   - Ensure logActivity calls happen AFTER successful operations\n\n";

echo "4. 📊 SUCCESS INDICATORS:\n";
echo "   - Browser console shows '🔍 logActivity called with: ...'\n";
echo "   - Network tab shows successful POST to /api/activity-logs/log\n";
echo "   - Activity logs page shows new entries in real-time\n";
echo "   - No JavaScript errors in console\n\n";

echo "The most likely issue is that the cuisine save operations are failing or completing before the logActivity calls, or there are JavaScript errors preventing the calls from executing.\n";
