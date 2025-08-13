<?php
/**
 * Comprehensive Test Script for Reports, Attributes, Documents, and Subscription Plans Activity Logging
 * 
 * This script tests the activity logging implementation for all four modules:
 * - Reports: Report generation
 * - Attributes: Create, update, delete operations
 * - Documents: Create, update, delete, enable/disable operations
 * - Subscription Plans: Create, update, delete, enable/disable operations
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ActivityLoggingTest
{
    private $activityLogger;
    private $client;
    private $baseUrl = 'http://127.0.0.1:8000';

    public function __construct()
    {
        $this->activityLogger = new ActivityLogger();
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false
        ]);
    }

    public function runAllTests()
    {
        echo "🚀 Starting Comprehensive Activity Logging Tests for Reports, Attributes, Documents, and Subscription Plans\n";
        echo "================================================================================\n\n";

        $this->testActivityLoggerService();
        $this->testDirectFirestoreLogging();
        $this->testApiEndpoint();
        $this->testBladeFileImplementations();

        echo "\n✅ All tests completed!\n";
    }

    private function testActivityLoggerService()
    {
        echo "📋 Testing ActivityLogger Service...\n";
        
        try {
            // Create a mock user
            $user = new \stdClass();
            $user->id = 'test_user_123';
            $user->name = 'Test User';

            // Create a mock request
            $request = new Request();
            $request->merge([
                'ip' => '127.0.0.1',
                'user_agent' => 'Test Browser'
            ]);

            // Test logging for each module
            $modules = [
                'reports' => 'Generated sales report in PDF format',
                'attributes' => 'Created new attribute: Test Attribute',
                'documents' => 'Created new document: Test Document for driver',
                'subscription_plans' => 'Created new subscription plan: Test Plan'
            ];

            foreach ($modules as $module => $description) {
                $result = $this->activityLogger->log($user, $module, 'test_action', $description, $request);
                if ($result) {
                    echo "  ✅ ActivityLogger service test passed for {$module}\n";
                } else {
                    echo "  ❌ ActivityLogger service test failed for {$module}\n";
                }
            }

        } catch (\Exception $e) {
            echo "  ❌ ActivityLogger service test failed: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }

    private function testDirectFirestoreLogging()
    {
        echo "🔥 Testing Direct Firestore Logging...\n";
        
        try {
            $firestore = app('firebase.firestore');
            $collection = $firestore->collection('activity_logs');

            // Test data for each module
            $testData = [
                [
                    'user_id' => 'test_user_123',
                    'user_type' => 'admin',
                    'role' => 'super_admin',
                    'module' => 'reports',
                    'action' => 'generated',
                    'description' => 'Generated sales report in CSV format',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Test Browser',
                    'created_at' => new \Google\Cloud\Core\Timestamp(new \DateTime())
                ],
                [
                    'user_id' => 'test_user_123',
                    'user_type' => 'admin',
                    'role' => 'super_admin',
                    'module' => 'attributes',
                    'action' => 'created',
                    'description' => 'Created new attribute: Test Attribute',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Test Browser',
                    'created_at' => new \Google\Cloud\Core\Timestamp(new \DateTime())
                ],
                [
                    'user_id' => 'test_user_123',
                    'user_type' => 'admin',
                    'role' => 'super_admin',
                    'module' => 'documents',
                    'action' => 'created',
                    'description' => 'Created new document: Test Document for restaurant',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Test Browser',
                    'created_at' => new \Google\Cloud\Core\Timestamp(new \DateTime())
                ],
                [
                    'user_id' => 'test_user_123',
                    'user_type' => 'admin',
                    'role' => 'super_admin',
                    'module' => 'subscription_plans',
                    'action' => 'created',
                    'description' => 'Created new subscription plan: Test Plan',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Test Browser',
                    'created_at' => new \Google\Cloud\Core\Timestamp(new \DateTime())
                ]
            ];

            foreach ($testData as $data) {
                $collection->add($data);
                echo "  ✅ Direct Firestore logging test passed for {$data['module']}\n";
            }

        } catch (\Exception $e) {
            echo "  ❌ Direct Firestore logging test failed: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }

    private function testApiEndpoint()
    {
        echo "🌐 Testing API Endpoint...\n";
        
        try {
            $testData = [
                [
                    'module' => 'reports',
                    'action' => 'generated',
                    'description' => 'Generated sales report in PDF format'
                ],
                [
                    'module' => 'attributes',
                    'action' => 'created',
                    'description' => 'Created new attribute: Test Attribute'
                ],
                [
                    'module' => 'documents',
                    'action' => 'created',
                    'description' => 'Created new document: Test Document for driver'
                ],
                [
                    'module' => 'subscription_plans',
                    'action' => 'created',
                    'description' => 'Created new subscription plan: Test Plan'
                ]
            ];

            foreach ($testData as $data) {
                $response = $this->client->post($this->baseUrl . '/api/activity-logs/log', [
                    'form_params' => $data,
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/x-www-form-urlencoded'
                    ]
                ]);

                $statusCode = $response->getStatusCode();
                $responseData = json_decode($response->getBody(), true);

                if ($statusCode === 200 && isset($responseData['success']) && $responseData['success']) {
                    echo "  ✅ API endpoint test passed for {$data['module']} (Status: {$statusCode})\n";
                } else {
                    echo "  ❌ API endpoint test failed for {$data['module']} (Status: {$statusCode})\n";
                    echo "     Response: " . json_encode($responseData) . "\n";
                }
            }

        } catch (RequestException $e) {
            echo "  ❌ API endpoint test failed: " . $e->getMessage() . "\n";
            if ($e->hasResponse()) {
                echo "     Status Code: " . $e->getResponse()->getStatusCode() . "\n";
                echo "     Response: " . $e->getResponse()->getBody() . "\n";
            }
        }
        echo "\n";
    }

    private function testBladeFileImplementations()
    {
        echo "📄 Testing Blade File Implementations...\n";
        
        $bladeFiles = [
            'reports' => [
                'resources/views/reports/sales-report.blade.php'
            ],
            'attributes' => [
                'resources/views/attributes/create.blade.php',
                'resources/views/attributes/edit.blade.php',
                'resources/views/attributes/index.blade.php'
            ],
            'documents' => [
                'resources/views/documents/create.blade.php',
                'resources/views/documents/edit.blade.php',
                'resources/views/documents/index.blade.php'
            ],
            'subscription_plans' => [
                'resources/views/subscription_plans/save.blade.php',
                'resources/views/subscription_plans/index.blade.php'
            ]
        ];

        foreach ($bladeFiles as $module => $files) {
            echo "  📁 Testing {$module} module:\n";
            
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $content = file_get_contents($file);
                    
                    // Check for logActivity calls
                    if (strpos($content, 'logActivity') !== false) {
                        echo "    ✅ {$file} - logActivity calls found\n";
                        
                        // Count logActivity calls
                        $count = substr_count($content, 'logActivity');
                        echo "       Found {$count} logActivity call(s)\n";
                        
                        // Check for await logActivity
                        if (strpos($content, 'await logActivity') !== false) {
                            echo "       ✅ Properly awaited logActivity calls\n";
                        } else {
                            echo "       ⚠️  Some logActivity calls may not be awaited\n";
                        }
                    } else {
                        echo "    ❌ {$file} - No logActivity calls found\n";
                    }
                } else {
                    echo "    ❌ {$file} - File not found\n";
                }
            }
            echo "\n";
        }
    }
}

// Run the tests
try {
    $test = new ActivityLoggingTest();
    $test->runAllTests();
} catch (Exception $e) {
    echo "❌ Test execution failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
