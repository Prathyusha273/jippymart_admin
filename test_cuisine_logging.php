<?php
/**
 * Test Cuisine Logging Integration
 * This script tests if cuisine operations are properly calling the logActivity function
 */

echo "🧪 Testing Cuisine Logging Integration\n";
echo "=====================================\n\n";

// Test 1: Check if logActivity function is available on cuisine pages
echo "1. Testing logActivity function availability...\n";

$cuisinePages = [
    'http://127.0.0.1:8000/cuisines/create',
    'http://127.0.0.1:8000/cuisines/edit/1',
    'http://127.0.0.1:8000/cuisines'
];

foreach ($cuisinePages as $page) {
    echo "   Testing: {$page}\n";
    
    $response = @file_get_contents($page);
    if ($response === false) {
        echo "   ❌ Cannot access page\n";
        continue;
    }
    
    // Check if global-activity-logger.js is loaded
    if (strpos($response, 'global-activity-logger.js') !== false) {
        echo "   ✅ global-activity-logger.js is loaded\n";
    } else {
        echo "   ❌ global-activity-logger.js is NOT loaded\n";
    }
    
    // Check if logActivity calls are present
    if (strpos($response, 'logActivity') !== false) {
        echo "   ✅ logActivity calls found in page\n";
    } else {
        echo "   ❌ No logActivity calls found in page\n";
    }
    
    // Check if CSRF token is present
    if (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $response, $matches)) {
        echo "   ✅ CSRF token found: " . substr($matches[1], 0, 10) . "...\n";
    } else {
        echo "   ❌ CSRF token NOT found\n";
    }
    
    echo "\n";
}

// Test 2: Test API endpoint directly
echo "2. Testing API endpoint directly...\n";

$postData = http_build_query([
    'module' => 'cuisines',
    'action' => 'created',
    'description' => 'Test cuisine creation from script'
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

$response = @file_get_contents('http://127.0.0.1:8000/api/activity-logs/log', false, $context);

if ($response !== false) {
    echo "   ✅ API endpoint responded successfully\n";
    echo "   Response: " . substr($response, 0, 200) . "...\n";
} else {
    $httpResponse = $http_response_header ?? [];
    $statusCode = 0;
    if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $httpResponse[0] ?? '', $matches)) {
        $statusCode = $matches[1];
    }
    echo "   ❌ API endpoint failed - HTTP {$statusCode}\n";
}

// Test 3: Check if cuisine operations are actually completing
echo "\n3. Testing cuisine operation flow...\n";

// Get the cuisine create page and extract any JavaScript errors
$createPage = @file_get_contents('http://127.0.0.1:8000/cuisines/create');
if ($createPage !== false) {
    echo "   ✅ Cuisine create page accessible\n";
    
    // Check for common JavaScript issues
    $issues = [];
    
    if (strpos($createPage, 'firebase is not defined') !== false) {
        $issues[] = 'Firebase not defined';
    }
    
    if (strpos($createPage, 'jQuery is not defined') !== false) {
        $issues[] = 'jQuery not defined';
    }
    
    if (strpos($createPage, 'logActivity is not defined') !== false) {
        $issues[] = 'logActivity function not available';
    }
    
    if (empty($issues)) {
        echo "   ✅ No obvious JavaScript issues detected\n";
    } else {
        echo "   ❌ JavaScript issues detected: " . implode(', ', $issues) . "\n";
    }
} else {
    echo "   ❌ Cannot access cuisine create page\n";
}

echo "\n=====================================\n";
echo "🎯 DIAGNOSIS & RECOMMENDATIONS\n";
echo "=====================================\n\n";

echo "Based on the test results, here are the likely issues:\n\n";

echo "1. 🔍 POSSIBLE ISSUES:\n";
echo "   - logActivity function might not be available when cuisine save completes\n";
echo "   - Cuisine save operation might be failing before reaching the logActivity call\n";
echo "   - JavaScript errors might be preventing the function from executing\n";
echo "   - Timing issues between Firebase save and logActivity call\n\n";

echo "2. 🧪 MANUAL TESTING STEPS:\n";
echo "   a) Open browser console on /cuisines/create\n";
echo "   b) Type: logActivity('test', 'test', 'test') - should work\n";
echo "   c) Fill out cuisine form and submit\n";
echo "   d) Watch console for any errors during save process\n";
echo "   e) Check if logActivity call appears after successful save\n\n";

echo "3. 🔧 QUICK FIXES TO TRY:\n";
echo "   a) Add console.log before logActivity call to verify it's reached\n";
echo "   b) Add error handling around logActivity call\n";
echo "   c) Ensure logActivity call happens AFTER successful Firebase save\n";
echo "   d) Check if cuisine save operation is actually completing\n\n";

echo "4. 📊 EXPECTED BEHAVIOR:\n";
echo "   - Console should show: '🔍 logActivity called with: {module: \"cuisines\", action: \"created\", ...}'\n";
echo "   - Console should show: '✅ Activity logged successfully'\n";
echo "   - Network tab should show successful POST to /api/activity-logs/log\n";
echo "   - Activity logs page should show new entry in real-time\n\n";

echo "The most likely issue is that the cuisine save operation is either failing or the logActivity call is not being reached due to JavaScript errors or timing issues.\n";
