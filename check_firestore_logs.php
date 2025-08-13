<?php
/**
 * Check Firestore Logs Directly
 * This script directly queries Firestore to check if logs exist
 */

// Bootstrap Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Direct Firestore Log Check\n";
echo "============================\n\n";

try {
    // Create Firestore client directly
    $firestore = new \Google\Cloud\Firestore\FirestoreClient([
        'projectId' => config('firestore.project_id'),
        'keyFilePath' => config('firestore.credentials'),
        'databaseId' => config('firestore.database_id'),
    ]);
    
    $collection = config('firestore.collection', 'activity_logs');
    echo "✅ Firestore client created\n";
    echo "📁 Collection: {$collection}\n\n";
    
    // Test 1: Get all documents
    echo "1. Checking all documents in collection...\n";
    try {
        $documents = $firestore->collection($collection)->documents();
        $allDocs = [];
        foreach ($documents as $document) {
            $allDocs[] = $document->data();
        }
        echo "   ✅ Found " . count($allDocs) . " total documents\n";
        
        if (!empty($allDocs)) {
            echo "   📋 Sample document:\n";
            $sample = $allDocs[0];
            foreach ($sample as $key => $value) {
                if ($key === 'created_at' && $value instanceof \Google\Cloud\Core\Timestamp) {
                    echo "      - {$key}: " . $value->get()->format('Y-m-d H:i:s') . "\n";
                } else {
                    echo "      - {$key}: " . (is_string($value) ? $value : gettype($value)) . "\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "   ❌ Error getting all documents: " . $e->getMessage() . "\n";
    }
    
    // Test 2: Query by module
    echo "\n2. Testing module query...\n";
    try {
        $query = $firestore->collection($collection)
            ->where('module', '=', 'accuracy_test')
            ->orderBy('created_at', 'desc')
            ->limit(5);
        
        $documents = $query->documents();
        $moduleDocs = [];
        foreach ($documents as $document) {
            $moduleDocs[] = $document->data();
        }
        echo "   ✅ Found " . count($moduleDocs) . " documents for 'accuracy_test' module\n";
        
        if (!empty($moduleDocs)) {
            echo "   📋 Latest accuracy_test log:\n";
            $latest = $moduleDocs[0];
            echo "      - User ID: " . ($latest['user_id'] ?? 'N/A') . "\n";
            echo "      - Action: " . ($latest['action'] ?? 'N/A') . "\n";
            echo "      - Description: " . ($latest['description'] ?? 'N/A') . "\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Error querying by module: " . $e->getMessage() . "\n";
    }
    
    // Test 3: Query by action
    echo "\n3. Testing action query...\n";
    try {
        $query = $firestore->collection($collection)
            ->where('action', '=', 'created')
            ->orderBy('created_at', 'desc')
            ->limit(5);
        
        $documents = $query->documents();
        $actionDocs = [];
        foreach ($documents as $document) {
            $actionDocs[] = $document->data();
        }
        echo "   ✅ Found " . count($actionDocs) . " documents with 'created' action\n";
    } catch (Exception $e) {
        echo "   ❌ Error querying by action: " . $e->getMessage() . "\n";
    }
    
    // Test 4: Check specific modules
    echo "\n4. Checking specific modules...\n";
    $modules = ['accuracy_test', 'multi_test', 'action_test', 'perf_test', 'test'];
    
    foreach ($modules as $module) {
        try {
            $query = $firestore->collection($collection)
                ->where('module', '=', $module);
            
            $documents = $query->documents();
            $count = 0;
            foreach ($documents as $document) {
                $count++;
            }
            echo "   📊 Module '{$module}': {$count} logs\n";
        } catch (Exception $e) {
            echo "   ❌ Error checking module '{$module}': " . $e->getMessage() . "\n";
        }
    }
    
    // Test 5: Create a test log and immediately retrieve it
    echo "\n5. Testing create and immediate retrieve...\n";
    try {
        // Create a test log
        $testData = [
            'user_id' => '9999',
            'user_type' => 'admin',
            'role' => 'super_admin',
            'module' => 'direct_test',
            'action' => 'created',
            'description' => 'Direct test log',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Direct Test',
            'created_at' => new \Google\Cloud\Core\Timestamp(new \DateTime())
        ];
        
        $docRef = $firestore->collection($collection)->add($testData);
        echo "   ✅ Created test log with ID: " . $docRef->id() . "\n";
        
        // Immediately retrieve it
        $retrievedDoc = $firestore->collection($collection)->document($docRef->id())->snapshot();
        if ($retrievedDoc->exists()) {
            $retrievedData = $retrievedDoc->data();
            echo "   ✅ Successfully retrieved the log\n";
            echo "      - Module: " . ($retrievedData['module'] ?? 'N/A') . "\n";
            echo "      - Action: " . ($retrievedData['action'] ?? 'N/A') . "\n";
            echo "      - Description: " . ($retrievedData['description'] ?? 'N/A') . "\n";
        } else {
            echo "   ❌ Could not retrieve the created log\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Error in create/retrieve test: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "📊 DIAGNOSIS SUMMARY\n";
    echo str_repeat("=", 50) . "\n";
    
    if (isset($allDocs) && count($allDocs) > 0) {
        echo "✅ Logs exist in Firestore\n";
        echo "✅ Firestore connection is working\n";
        echo "⚠️  Issue may be in the ActivityLogger retrieval method\n";
    } else {
        echo "❌ No logs found in Firestore\n";
        echo "❌ Logs are not being created properly\n";
    }
    
    echo "\n🚀 RECOMMENDATIONS:\n";
    echo "==================\n";
    echo "1. If logs exist: Check ActivityLogger retrieval method\n";
    echo "2. If no logs: Check ActivityLogger creation method\n";
    echo "3. Test the browser interface\n";
    echo "4. Check Firestore security rules\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
