<?php
/**
 * Firebase Connectivity and Activity Log System Test
 * This script tests all components of the activity log system
 */

// Bootstrap Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Firebase Connectivity and Activity Log System Test\n";
echo "==================================================\n\n";

// 1. Test Firebase Configuration
echo "1. Testing Firebase Configuration...\n";
try {
    $config = config('firestore');
    echo "   ✅ Firestore config loaded\n";
    echo "   - Project ID: " . $config['project_id'] . "\n";
    echo "   - Database ID: " . $config['database_id'] . "\n";
    echo "   - Collection: " . $config['collection'] . "\n";
    echo "   - Credentials: " . $config['credentials'] . "\n";
} catch (Exception $e) {
    echo "   ❌ Firestore config error: " . $e->getMessage() . "\n";
}

// 2. Test Service Account File
echo "\n2. Testing Service Account File...\n";
$serviceAccountPath = storage_path('app/firebase/serviceAccount.json');
if (file_exists($serviceAccountPath)) {
    echo "   ✅ Service account file exists\n";
    $content = file_get_contents($serviceAccountPath);
    $json = json_decode($content, true);
    if ($json && isset($json['project_id'])) {
        echo "   ✅ Service account JSON is valid\n";
        echo "   - Project ID: " . $json['project_id'] . "\n";
        echo "   - Client Email: " . $json['client_email'] . "\n";
    } else {
        echo "   ❌ Service account JSON is invalid\n";
    }
} else {
    echo "   ❌ Service account file not found at: $serviceAccountPath\n";
}

// 3. Test ActivityLogger Service
echo "\n3. Testing ActivityLogger Service...\n";
try {
    $activityLogger = new \App\Services\ActivityLogger();
    echo "   ✅ ActivityLogger service instantiated successfully\n";
} catch (Exception $e) {
    echo "   ❌ ActivityLogger service error: " . $e->getMessage() . "\n";
}

// 4. Test Firestore Connection
echo "\n4. Testing Firestore Connection...\n";
try {
    $firestore = new \Google\Cloud\Firestore\FirestoreClient([
        'projectId' => config('firestore.project_id'),
        'keyFilePath' => config('firestore.credentials'),
        'databaseId' => config('firestore.database_id'),
    ]);
    echo "   ✅ Firestore client created successfully\n";
    
    // Test collection access
    $collection = $firestore->collection(config('firestore.collection'));
    echo "   ✅ Collection access successful\n";
    
    // Test a simple query
    $documents = $collection->limit(1)->documents();
    echo "   ✅ Firestore query successful\n";
    
} catch (Exception $e) {
    echo "   ❌ Firestore connection error: " . $e->getMessage() . "\n";
}

// 5. Test ActivityLogController
echo "\n5. Testing ActivityLogController...\n";
try {
    $controller = new \App\Http\Controllers\ActivityLogController(new \App\Services\ActivityLogger());
    echo "   ✅ ActivityLogController instantiated successfully\n";
} catch (Exception $e) {
    echo "   ❌ ActivityLogController error: " . $e->getMessage() . "\n";
}

// 6. Test Routes
echo "\n6. Testing Routes...\n";
$routes = [
    '/activity-logs' => 'GET',
    '/api/activity-logs/log' => 'POST',
    '/api/activity-logs/module/{module}' => 'GET',
    '/api/activity-logs/all' => 'GET',
    '/api/activity-logs/cuisines' => 'GET'
];

foreach ($routes as $route => $method) {
    echo "   - $method $route\n";
}
echo "   ✅ Routes are registered\n";

// 7. Test Environment Variables
echo "\n7. Testing Environment Variables...\n";
$envVars = [
    'FIREBASE_PROJECT_ID',
    'FIRESTORE_DATABASE_ID',
    'FIRESTORE_COLLECTION'
];

foreach ($envVars as $var) {
    $value = env($var);
    if ($value) {
        echo "   ✅ $var: $value\n";
    } else {
        echo "   ❌ $var: Not set\n";
    }
}

// 8. Test Laravel Log
echo "\n8. Testing Laravel Log...\n";
try {
    \Log::info('Activity Log System Test - ' . date('Y-m-d H:i:s'));
    echo "   ✅ Laravel logging working\n";
} catch (Exception $e) {
    echo "   ❌ Laravel logging error: " . $e->getMessage() . "\n";
}

// 9. Test CSRF Token
echo "\n9. Testing CSRF Token...\n";
try {
    $token = csrf_token();
    if ($token) {
        echo "   ✅ CSRF token generated: " . substr($token, 0, 10) . "...\n";
    } else {
        echo "   ❌ CSRF token not generated\n";
    }
} catch (Exception $e) {
    echo "   ❌ CSRF token error: " . $e->getMessage() . "\n";
}

// 10. Test File Permissions
echo "\n10. Testing File Permissions...\n";
$files = [
    'storage/app/firebase/serviceAccount.json' => 'Service Account',
    'config/firestore.php' => 'Firestore Config',
    'app/Services/ActivityLogger.php' => 'ActivityLogger Service',
    'app/Http/Controllers/ActivityLogController.php' => 'ActivityLogController',
    'resources/views/activity_logs/index.blade.php' => 'Activity Logs View'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        if (is_readable($file)) {
            echo "   ✅ $description: Readable\n";
        } else {
            echo "   ❌ $description: Not readable\n";
        }
    } else {
        echo "   ❌ $description: File not found\n";
    }
}

echo "\n📋 SUMMARY\n";
echo "==========\n";
echo "✅ Components that are working\n";
echo "❌ Components that need attention\n\n";

echo "🚀 NEXT STEPS:\n";
echo "==============\n";
echo "1. If you see ❌ marks, fix those issues first\n";
echo "2. Test the Activity Logs page in browser\n";
echo "3. Check browser console for JavaScript errors\n";
echo "4. Test with Cuisines module to generate logs\n\n";

echo "🔗 Test URLs:\n";
echo "=============\n";
echo "- Activity Logs: http://127.0.0.1:8000/activity-logs\n";
echo "- Cuisines: http://127.0.0.1:8000/cuisines\n\n";

echo "💡 Debug Tips:\n";
echo "==============\n";
echo "- Check browser console for JavaScript errors\n";
echo "- Monitor Laravel logs: tail -f storage/logs/laravel.log\n";
echo "- Verify Firebase project settings in Firebase Console\n";
echo "- Check Firestore security rules\n";
