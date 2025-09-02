<?php

/**
 * Script to update vendor types in Firebase database
 * Changes vendors with empty or "not set" vType to "restaurant"
 * Keeps "mart" vendors unchanged
 */

require_once __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Firestore\FirestoreClient;

// Initialize Firestore
$firestore = new FirestoreClient([
    'projectId' => env('FIREBASE_PROJECT_ID'),
    'keyFilePath' => env('FIREBASE_CREDENTIALS_PATH'),
]);

$usersCollection = $firestore->collection('users');
$vendorsCollection = $firestore->collection('vendors');

echo "🔍 Starting vendor type update process...\n";

try {
    // Get all vendor users
    $vendorUsers = $usersCollection->where('role', '=', 'vendor')->documents();
    
    $updatedCount = 0;
    $skippedCount = 0;
    $errorCount = 0;
    
    foreach ($vendorUsers as $userDoc) {
        $userData = $userDoc->data();
        $userId = $userDoc->id();
        
        echo "Processing vendor: " . ($userData['firstName'] ?? 'Unknown') . " " . ($userData['lastName'] ?? '') . " (ID: $userId)\n";
        
        // Check if user has vType field
        $currentVType = $userData['vType'] ?? '';
        
        echo "  Current vType: '$currentVType'\n";
        
        // Check if vType is empty, null, or "not set"
        if (empty($currentVType) || strtolower(trim($currentVType)) === 'not set' || $currentVType === '') {
            echo "  → Updating to 'restaurant'\n";
            
            try {
                // Update the user document
                $usersCollection->document($userId)->update([
                    'vType' => 'restaurant'
                ]);
                
                // Also check if there's a corresponding vendor document
                $vendorDocs = $vendorsCollection->where('author', '=', $userId)->documents();
                foreach ($vendorDocs as $vendorDoc) {
                    $vendorData = $vendorDoc->data();
                    $vendorVType = $vendorData['vType'] ?? '';
                    
                    if (empty($vendorVType) || strtolower(trim($vendorVType)) === 'not set' || $vendorVType === '') {
                        echo "  → Also updating vendor document vType to 'restaurant'\n";
                        $vendorsCollection->document($vendorDoc->id())->update([
                            'vType' => 'restaurant'
                        ]);
                    }
                }
                
                $updatedCount++;
                echo "  ✅ Updated successfully\n";
                
            } catch (Exception $e) {
                echo "  ❌ Error updating: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        } else {
            echo "  → Skipping (already has valid vType: '$currentVType')\n";
            $skippedCount++;
        }
        
        echo "\n";
    }
    
    echo "📊 Update Summary:\n";
    echo "  ✅ Updated: $updatedCount vendors\n";
    echo "  ⏭️  Skipped: $skippedCount vendors\n";
    echo "  ❌ Errors: $errorCount vendors\n";
    echo "  📝 Total processed: " . ($updatedCount + $skippedCount + $errorCount) . " vendors\n";
    
    echo "\n🎉 Vendor type update process completed!\n";
    
} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n💡 Note: You may need to refresh the vendors page to see the changes.\n";
