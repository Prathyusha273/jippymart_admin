<?php
// Test script to verify mart items count for subcategories
require_once 'vendor/autoload.php';

use Google\Cloud\Firestore\FirestoreClient;

try {
    // Initialize Firestore client with correct credentials path
    $firestore = new FirestoreClient([
        'projectId' => 'jippymart-27c08',
        'keyFilePath' => __DIR__ . '/storage/app/firebase/serviceAccount.json',
    ]);
    
    echo "🔍 Testing mart items count for subcategories...\n\n";
    
    // Test 1: Get all sub-categories
    echo "📊 Test 1: Getting all sub-categories...\n";
    $allSubcategories = $firestore->collection('mart_subcategories')->documents();
    $subcategories = [];
    foreach ($allSubcategories as $doc) {
        $data = $doc->data();
        $subcategories[] = [
            'id' => $doc->id(),
            'title' => $data['title'],
            'parent_category_id' => $data['parent_category_id']
        ];
    }
    echo "Total sub-categories found: " . count($subcategories) . "\n\n";
    
    // Test 2: Check mart items for each sub-category
    echo "📊 Test 2: Checking mart items for each sub-category...\n";
    foreach ($subcategories as $subcategory) {
        echo "🔍 Checking sub-category: {$subcategory['title']} (ID: {$subcategory['id']})\n";
        
        // Query mart items by subcategoryID
        $itemsQuery = $firestore->collection('mart_items')
            ->where('subcategoryID', '=', $subcategory['id']);
        
        $items = $itemsQuery->documents();
        $itemCount = 0;
        foreach ($items as $item) {
            $itemCount++;
            if ($itemCount <= 3) { // Show first 3 items
                $itemData = $item->data();
                echo "  📦 Item: {$itemData['name']} (ID: {$item->id()})\n";
            }
        }
        
        if ($itemCount > 3) {
            echo "  ... and " . ($itemCount - 3) . " more items\n";
        }
        
        echo "  ✅ Total items: $itemCount\n\n";
    }
    
    // Test 3: Check sample mart items to see field structure
    echo "📊 Test 3: Checking sample mart items field structure...\n";
    $sampleItems = $firestore->collection('mart_items')->limit(5)->documents();
    foreach ($sampleItems as $item) {
        $itemData = $item->data();
        echo "📦 Item: {$itemData['name']} (ID: {$item->id()})\n";
        echo "  - subcategoryID: " . ($itemData['subcategoryID'] ?? 'NOT SET') . "\n";
        echo "  - categoryID: " . ($itemData['categoryID'] ?? 'NOT SET') . "\n";
        echo "  - vendorID: " . ($itemData['vendorID'] ?? 'NOT SET') . "\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
