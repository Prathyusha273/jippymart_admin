<?php
// Simple test to check sub-category data and query
require_once 'vendor/autoload.php';

use Google\Cloud\Firestore\FirestoreClient;

try {
    // Initialize Firestore client
    $firestore = new FirestoreClient([
        'projectId' => 'jippymart-27c08', // Replace with your project ID
        'keyFilePath' => 'firebase-credentials.json', // Replace with your credentials path
    ]);
    
    $categoryId = '68aed0870ea19'; // The category ID from your URL
    
    echo "🔍 Testing sub-category query...\n";
    echo "Category ID: $categoryId\n\n";
    
    // Test 1: Get all sub-categories
    echo "📊 Test 1: Getting all sub-categories...\n";
    $allSubcategories = $firestore->collection('mart_subcategories')->documents();
    $totalCount = 0;
    foreach ($allSubcategories as $doc) {
        $data = $doc->data();
        $totalCount++;
        echo "  - ID: {$doc->id()}, Title: {$data['title']}, Parent ID: {$data['parent_category_id']}\n";
    }
    echo "Total sub-categories: $totalCount\n\n";
    
    // Test 2: Query by parent category ID
    echo "📊 Test 2: Querying by parent_category_id = '$categoryId'...\n";
    $query = $firestore->collection('mart_subcategories')
        ->where('parent_category_id', '=', $categoryId);
    
    $results = $query->documents();
    $matchCount = 0;
    foreach ($results as $doc) {
        $data = $doc->data();
        $matchCount++;
        echo "  ✅ Found: ID: {$doc->id()}, Title: {$data['title']}, Parent ID: {$data['parent_category_id']}\n";
    }
    echo "Matching sub-categories: $matchCount\n\n";
    
    // Test 3: Check the specific sub-category by ID
    echo "📊 Test 3: Checking specific sub-category by ID...\n";
    $subcategoryId = '68aed2ca11f46'; // From your data
    $doc = $firestore->collection('mart_subcategories')->document($subcategoryId)->snapshot();
    
    if ($doc->exists()) {
        $data = $doc->data();
        echo "  ✅ Sub-category exists:\n";
        echo "    - ID: {$doc->id()}\n";
        echo "    - Title: {$data['title']}\n";
        echo "    - Parent ID: {$data['parent_category_id']}\n";
        echo "    - Section: {$data['section']}\n";
        echo "    - Published: " . ($data['publish'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "  ❌ Sub-category not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
