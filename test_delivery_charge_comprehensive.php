<?php
/**
 * Test Script for Delivery Charge Activity Logging
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class DeliveryChargeActivityLoggerTest
{
    private $client;
    private $baseUrl = 'http://127.0.0.1:8000';
    private $testResults = [];

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false
        ]);
    }

    public function runAllTests()
    {
        echo "🧪 Starting Delivery Charge Activity Logging Tests\n";
        echo "================================================\n\n";

        // Test Delivery Charge
        $this->testDeliveryCharge();
        
        // Print Summary
        $this->printSummary();
    }

    private function testDeliveryCharge()
    {
        echo "🚚 Testing Delivery Charge Logging...\n";
        
        $this->testLogActivity('delivery_charge', 'updated', 'Updated delivery charge settings: Base=25, Free Distance=10km, Threshold=500, Per KM=5, Vendor Modify=Enabled');
        
        echo "✅ Delivery Charge tests completed\n\n";
    }

    private function testLogActivity($module, $action, $description)
    {
        $testName = "Test: $module - $action";
        
        try {
            $response = $this->client->post($this->baseUrl . '/api/activity-logs/log', [
                'form_params' => [
                    'module' => $module,
                    'action' => $action,
                    'description' => $description
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);

            if ($statusCode === 200 && isset($body['success']) && $body['success']) {
                $this->testResults[] = [
                    'test' => $testName,
                    'status' => 'PASS',
                    'message' => 'Successfully logged activity'
                ];
                echo "  ✅ $testName - PASS\n";
            } else {
                $this->testResults[] = [
                    'test' => $testName,
                    'status' => 'FAIL',
                    'message' => 'API returned success=false or unexpected response',
                    'details' => $body
                ];
                echo "  ❌ $testName - FAIL\n";
            }

        } catch (RequestException $e) {
            $this->testResults[] = [
                'test' => $testName,
                'status' => 'FAIL',
                'message' => 'Request failed: ' . $e->getMessage(),
                'details' => [
                    'status_code' => $e->getResponse() ? $e->getResponse()->getStatusCode() : 'Unknown',
                    'response' => $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response'
                ]
            ];
            echo "  ❌ $testName - FAIL (Request Exception)\n";
        } catch (Exception $e) {
            $this->testResults[] = [
                'test' => $testName,
                'status' => 'FAIL',
                'message' => 'Unexpected error: ' . $e->getMessage()
            ];
            echo "  ❌ $testName - FAIL (Unexpected Error)\n";
        }
    }

    private function printSummary()
    {
        echo "📊 Test Summary\n";
        echo "===============\n";
        
        $totalTests = count($this->testResults);
        $passedTests = count(array_filter($this->testResults, function($result) {
            return $result['status'] === 'PASS';
        }));
        $failedTests = $totalTests - $passedTests;

        echo "Total Tests: $totalTests\n";
        echo "Passed: $passedTests\n";
        echo "Failed: $failedTests\n";
        echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 2) . "%\n\n";

        if ($failedTests > 0) {
            echo "❌ Failed Tests:\n";
            foreach ($this->testResults as $result) {
                if ($result['status'] === 'FAIL') {
                    echo "  - {$result['test']}: {$result['message']}\n";
                    if (isset($result['details'])) {
                        echo "    Details: " . json_encode($result['details']) . "\n";
                    }
                }
            }
        }

        if ($passedTests === $totalTests) {
            echo "🎉 All tests passed! Delivery charge activity logging is working correctly.\n";
        } else {
            echo "⚠️  Some tests failed. Please check the implementation.\n";
        }
    }
}

// Run the tests
try {
    $test = new DeliveryChargeActivityLoggerTest();
    $test->runAllTests();
} catch (Exception $e) {
    echo "❌ Test execution failed: " . $e->getMessage() . "\n";
    exit(1);
}
