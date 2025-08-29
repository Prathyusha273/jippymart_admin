<?php
// Debug script to check mart items and subcategoryID field
require_once 'vendor/autoload.php';

use Google\Cloud\Firestore\FirestoreClient;

try {
    // Initialize Firestore client
    $firestore = new FirestoreClient([
        'projectId' => 'jippymart-27c08',
        'keyFilePath' => __DIR__ . '/storage/app/firebase/serviceAccount.json',
    ]);
    
    echo "🔍 Debugging mart items and subcategoryID field...\n\n";
    
    // Test 1: Get all mart items
    echo "📊 Test 1: Getting all mart items...\n";
    $allItems = $firestore->collection('mart_items')->limit(10)->documents();
    $itemCount = 0;
    foreach ($allItems as $item) {
        $itemCount++;
        $itemData = $item->data();
        echo "📦 Item $itemCount: {$itemData['name']} (ID: {$item->id()})\n";
        echo "   - subcategoryID: " . ($itemData['subcategoryID'] ?? 'NOT SET') . "\n";
        echo "   - categoryID: " . ($itemData['categoryID'] ?? 'NOT SET') . "\n";
        echo "   - vendorID: " . ($itemData['vendorID'] ?? 'NOT SET') . "\n\n";
    }
    echo "Total items checked: $itemCount\n\n";
    
    // Test 2: Check for items with subcategoryID
    echo "📊 Test 2: Checking for items with subcategoryID field...\n";
    $itemsWithSubcategory = $firestore->collection('mart_items')
        ->where('subcategoryID', '!=', null)
        ->limit(5)
        ->documents();
    
    $subcategoryCount = 0;
    foreach ($itemsWithSubcategory as $item) {
        $subcategoryCount++;
        $itemData = $item->data();
        echo "✅ Item with subcategoryID: {$itemData['name']} (subcategoryID: {$itemData['subcategoryID']})\n";
    }
    echo "Items with subcategoryID: $subcategoryCount\n\n";
    
    // Test 3: Check specific subcategory
    echo "📊 Test 3: Checking specific subcategory (68b176db005f9)...\n";
    $specificSubcategory = $firestore->collection('mart_items')
        ->where('subcategoryID', '=', '68b176db005f9')
        ->documents();
    
    $specificCount = 0;
    foreach ($specificSubcategory as $item) {
        $specificCount++;
        $itemData = $item->data();
        echo "✅ Item for subcategory 68b176db005f9: {$itemData['name']}\n";
    }
    echo "Items for subcategory 68b176db005f9: $specificCount\n\n";
    
    // Test 4: Check all subcategories
    echo "📊 Test 4: Getting all subcategories...\n";
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
    echo "Total subcategories: " . count($subcategories) . "\n";
    foreach ($subcategories as $subcategory) {
        echo "  - {$subcategory['title']} (ID: {$subcategory['id']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
