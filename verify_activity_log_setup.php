<?php
/**
 * Activity Log Setup Verification Script
 * This script checks the current status of your Activity Log implementation
 */

echo "🔍 Activity Log Implementation Verification\n";
echo "==========================================\n\n";

// 1. Check if ActivityLogger service exists
echo "1. Checking ActivityLogger Service...\n";
if (file_exists('app/Services/ActivityLogger.php')) {
    echo "   ✅ ActivityLogger service exists\n";
} else {
    echo "   ❌ ActivityLogger service missing\n";
}

// 2. Check if ActivityLogController exists
echo "2. Checking ActivityLogController...\n";
if (file_exists('app/Http/Controllers/ActivityLogController.php')) {
    echo "   ✅ ActivityLogController exists\n";
} else {
    echo "   ❌ ActivityLogController missing\n";
}

// 3. Check if activity logs view exists
echo "3. Checking Activity Logs View...\n";
if (file_exists('resources/views/activity_logs/index.blade.php')) {
    echo "   ✅ Activity logs view exists\n";
} else {
    echo "   ❌ Activity logs view missing\n";
}

// 4. Check if activity logger JS exists
echo "4. Checking Activity Logger JavaScript...\n";
if (file_exists('public/js/activity-logger.js')) {
    echo "   ✅ Activity logger JS exists\n";
} else {
    echo "   ❌ Activity logger JS missing\n";
}

// 5. Check if firestore config exists
echo "5. Checking Firestore Configuration...\n";
if (file_exists('config/firestore.php')) {
    echo "   ✅ Firestore config exists\n";
} else {
    echo "   ❌ Firestore config missing\n";
}

// 6. Check if Firebase service account exists
echo "6. Checking Firebase Service Account...\n";
if (file_exists('storage/app/firebase/serviceAccount.json')) {
    echo "   ✅ Firebase service account exists\n";
} else {
    echo "   ❌ Firebase service account missing\n";
    echo "   📝 You need to place your Firebase service account key here\n";
}

// 7. Check .env for Firebase settings
echo "7. Checking .env Firebase Settings...\n";
$envFile = '.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    $requiredVars = ['FIRESTORE_PROJECT_ID', 'FIRESTORE_DATABASE_ID', 'FIRESTORE_COLLECTION'];
    
    foreach ($requiredVars as $var) {
        if (strpos($envContent, $var) !== false) {
            echo "   ✅ $var found in .env\n";
        } else {
            echo "   ❌ $var missing from .env\n";
        }
    }
} else {
    echo "   ❌ .env file not found\n";
}

// 8. Check routes
echo "8. Checking Routes...\n";
$routesFile = 'routes/web.php';
if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);
    $routePatterns = [
        'activity-logs' => '/activity-logs',
        'api.activity-logs.log' => '/api/activity-logs/log',
        'api.activity-logs.module' => '/api/activity-logs/module',
        'api.activity-logs.all' => '/api/activity-logs/all',
        'api.activity-logs.cuisines' => '/api/activity-logs/cuisines'
    ];
    
    foreach ($routePatterns as $name => $pattern) {
        if (strpos($routesContent, $pattern) !== false) {
            echo "   ✅ Route $name exists\n";
        } else {
            echo "   ❌ Route $name missing\n";
        }
    }
} else {
    echo "   ❌ routes/web.php not found\n";
}

// 9. Check menu integration
echo "9. Checking Menu Integration...\n";
$menuFile = 'resources/views/layouts/menu.blade.php';
if (file_exists($menuFile)) {
    $menuContent = file_get_contents($menuFile);
    if (strpos($menuContent, 'activity-logs') !== false) {
        echo "   ✅ Activity logs menu item exists\n";
    } else {
        echo "   ❌ Activity logs menu item missing\n";
    }
} else {
    echo "   ❌ Menu file not found\n";
}

// 10. Check cuisine integration
echo "10. Checking Cuisine Integration...\n";
$cuisineFiles = [
    'resources/views/cuisines/create.blade.php',
    'resources/views/cuisines/edit.blade.php',
    'resources/views/cuisines/index.blade.php'
];

foreach ($cuisineFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, 'logActivity') !== false) {
            echo "   ✅ $file has activity logging\n";
        } else {
            echo "   ❌ $file missing activity logging\n";
        }
    } else {
        echo "   ❌ $file not found\n";
    }
}

echo "\n📋 SUMMARY\n";
echo "==========\n";
echo "✅ Files that exist and are properly configured\n";
echo "❌ Files that are missing or need configuration\n";
echo "📝 Items that need manual setup\n\n";

echo "🚀 NEXT STEPS:\n";
echo "==============\n";
echo "1. If you see ❌ marks, those files need to be created or configured\n";
echo "2. If you see 📝 marks, you need to manually set up those items\n";
echo "3. Once all ✅ marks are present, you can test the Activity Log feature\n";
echo "4. Follow the ADMIN_PANEL_TEST_GUIDE.md for detailed testing instructions\n\n";

echo "🔗 QUICK ACCESS:\n";
echo "===============\n";
echo "- Activity Logs Page: http://your-domain.com/activity-logs\n";
echo "- Cuisines Module: http://your-domain.com/cuisines\n";
echo "- Test Guide: ADMIN_PANEL_TEST_GUIDE.md\n";
echo "- Setup Instructions: FIREBASE_SETUP_INSTRUCTIONS.md\n\n";

echo "💡 TIP: Run 'php artisan route:list | grep activity' to verify routes are registered\n";
echo "💡 TIP: Check browser console for JavaScript errors when testing\n";
echo "💡 TIP: Monitor storage/logs/laravel.log for backend errors\n";
