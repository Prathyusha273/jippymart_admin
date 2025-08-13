<?php
/**
 * Script to update .env file with Firebase configuration
 */

echo "🔧 Updating .env file with Firebase configuration...\n\n";

$envFile = '.env';
if (!file_exists($envFile)) {
    echo "❌ .env file not found!\n";
    exit(1);
}

$envContent = file_get_contents($envFile);

// Firebase configuration to add/update
$firebaseConfig = [
    'FIREBASE_APIKEY' => 'AIzaSyAf_lICoxPh8qKE1QnVkmQYTFJXKkYmRXU',
    'FIREBASE_AUTH_DOMAIN' => 'jippymart-27c08.firebaseapp.com',
    'FIREBASE_DATABASE_URL' => 'https://jippymart-27c08-default-rtdb.firebaseio.com',
    'FIREBASE_PROJECT_ID' => 'jippymart-27c08',
    'FIREBASE_STORAGE_BUCKET' => 'jippymart-27c08.firebasestorage.app',
    'FIREBASE_MESSAAGING_SENDER_ID' => '592427852800',
    'FIREBASE_APP_ID' => '1:592427852800:web:f74df8ceb2a4b597d1a4e5',
    'FIREBASE_MEASUREMENT_ID' => 'G-ZYBQYPZWCF',
    'FIRESTORE_DATABASE_ID' => '(default)',
    'FIRESTORE_COLLECTION' => 'activity_logs'
];

$updated = false;
foreach ($firebaseConfig as $key => $value) {
    if (strpos($envContent, $key . '=') !== false) {
        // Update existing value
        $envContent = preg_replace('/^' . preg_quote($key . '=', '/') . '.*$/m', $key . '=' . $value, $envContent);
        echo "✅ Updated $key\n";
    } else {
        // Add new value at the end
        $envContent .= "\n" . $key . '=' . $value;
        echo "✅ Added $key\n";
    }
    $updated = true;
}

if ($updated) {
    file_put_contents($envFile, $envContent);
    echo "\n🎉 .env file updated successfully!\n";
    echo "📝 Please restart your application or clear cache:\n";
    echo "   php artisan config:clear\n";
    echo "   php artisan cache:clear\n";
} else {
    echo "\n⚠️  No changes were made to .env file\n";
}

echo "\n🔗 Next steps:\n";
echo "1. Place your Firebase service account key in: storage/app/firebase/serviceAccount.json\n";
echo "2. Clear Laravel cache: php artisan cache:clear\n";
echo "3. Test the Activity Logs page: http://your-domain.com/activity-logs\n";
