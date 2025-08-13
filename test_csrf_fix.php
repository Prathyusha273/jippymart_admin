<?php
/**
 * Test CSRF Fix for Activity Logging
 */

echo "🧪 Testing CSRF Fix for Activity Logging API\n";
echo "==========================================\n\n";

// Test 1: Test API endpoint without CSRF token (should work now)
echo "1. Testing API endpoint without CSRF token...\n";
$postData = http_build_query([
    'module' => 'test',
    'action' => 'test_action',
    'description' => 'Test from script without CSRF'
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

// Test 2: Test with CSRF token (should also work)
echo "\n2. Testing API endpoint with CSRF token...\n";

// Get CSRF token from activity-logs page
$pageResponse = file_get_contents('http://127.0.0.1:8000/activity-logs');
if (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $pageResponse, $matches)) {
    $csrfToken = $matches[1];
    echo "   ✅ CSRF token extracted: " . substr($csrfToken, 0, 10) . "...\n";
    
    $postDataWithToken = http_build_query([
        'module' => 'test',
        'action' => 'test_action',
        'description' => 'Test from script with CSRF',
        '_token' => $csrfToken
    ]);
    
    $contextWithToken = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/x-www-form-urlencoded',
                'Content-Length: ' . strlen($postDataWithToken)
            ],
            'content' => $postDataWithToken
        ]
    ]);
    
    $responseWithToken = @file_get_contents('http://127.0.0.1:8000/api/activity-logs/log', false, $contextWithToken);
    
    if ($responseWithToken !== false) {
        echo "   ✅ API endpoint with CSRF token responded successfully\n";
        echo "   Response: " . substr($responseWithToken, 0, 200) . "...\n";
    } else {
        $httpResponse = $http_response_header ?? [];
        $statusCode = 0;
        if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $httpResponse[0] ?? '', $matches)) {
            $statusCode = $matches[1];
        }
        echo "   ❌ API endpoint with CSRF token failed - HTTP {$statusCode}\n";
    }
} else {
    echo "   ❌ Could not extract CSRF token from page\n";
}

echo "\n==========================================\n";
echo "🎯 SUMMARY\n";
echo "==========================================\n\n";

if ($response !== false) {
    echo "✅ SUCCESS: The CSRF fix is working!\n";
    echo "   - API endpoint now accepts requests without CSRF token\n";
    echo "   - Activity logging should work from cuisine pages\n";
    echo "   - Frontend JavaScript calls should succeed\n\n";
    
    echo "🚀 NEXT STEPS:\n";
    echo "   1. Test cuisine operations in the admin panel\n";
    echo "   2. Check browser console for logActivity calls\n";
    echo "   3. Verify activity logs page shows new entries\n";
    echo "   4. Monitor Network tab for successful API calls\n";
} else {
    echo "❌ ISSUE: The CSRF fix may not be working properly\n";
    echo "   - Check if Laravel caches were cleared\n";
    echo "   - Verify the VerifyCsrfToken middleware was updated\n";
    echo "   - Restart the web server if needed\n";
}
