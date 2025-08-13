<?php
/**
 * Comprehensive Test Script for Settings Subsections Activity Logging
 * Tests: payment_methods, business_model, radius_config, dine_in, tax_settings
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SettingsSubsectionsActivityLoggerTest
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
        echo "🧪 Starting Comprehensive Settings Subsections Activity Logging Tests\n";
        echo "================================================================\n\n";

        // Test Payment Methods
        $this->testPaymentMethods();
        
        // Test Business Model
        $this->testBusinessModel();
        
        // Test Radius Configuration
        $this->testRadiusConfiguration();
        
        // Test Dine In
        $this->testDineIn();
        
        // Test Tax Settings
        $this->testTaxSettings();

        // Print Summary
        $this->printSummary();
    }

    private function testPaymentMethods()
    {
        echo "💳 Testing Payment Methods Logging...\n";
        
        $paymentMethods = [
            'stripe' => 'Stripe payment settings',
            'paypal' => 'PayPal payment settings',
            'razorpay' => 'Razorpay payment settings',
            'cod' => 'COD payment settings'
        ];

        foreach ($paymentMethods as $method => $description) {
            $this->testLogActivity('payment_methods', 'updated', $description);
        }
        
        echo "✅ Payment Methods tests completed\n\n";
    }

    private function testBusinessModel()
    {
        echo "🏢 Testing Business Model Logging...\n";
        
        $this->testLogActivity('business_model', 'updated', 'Updated subscription model: Enabled');
        $this->testLogActivity('business_model', 'updated', 'Updated commission settings: Enabled, Type: Percent, Amount: 10');
        $this->testLogActivity('business_model', 'bulk_updated', 'Bulk updated commission for 5 vendors: Type=Percent, Amount=15');
        
        echo "✅ Business Model tests completed\n\n";
    }

    private function testRadiusConfiguration()
    {
        echo "📡 Testing Radius Configuration Logging...\n";
        
        $this->testLogActivity('radius_config', 'updated', 'Updated radius configuration: Restaurant=5 km, Driver=3 km, Duration=30s');
        
        echo "✅ Radius Configuration tests completed\n\n";
    }

    private function testDineIn()
    {
        echo "🍽️ Testing Dine In Logging...\n";
        
        $this->testLogActivity('dine_in', 'updated', 'Updated dine-in settings: Restaurant=Enabled, Customer=Enabled');
        
        echo "✅ Dine In tests completed\n\n";
    }

    private function testTaxSettings()
    {
        echo "💰 Testing Tax Settings Logging...\n";
        
        $this->testLogActivity('tax_settings', 'created', 'Created new tax: VAT (United States) - Type: percentage, Amount: 8.5, Enabled: Yes');
        $this->testLogActivity('tax_settings', 'updated', 'Updated tax: VAT (United States) - Type: percentage, Amount: 9.0, Enabled: Yes');
        $this->testLogActivity('tax_settings', 'enabled', 'Enabled tax: VAT');
        $this->testLogActivity('tax_settings', 'disabled', 'Disabled tax: VAT');
        $this->testLogActivity('tax_settings', 'deleted', 'Deleted tax: VAT');
        $this->testLogActivity('tax_settings', 'bulk_deleted', 'Bulk deleted taxes: VAT, GST, Service Tax');
        
        echo "✅ Tax Settings tests completed\n\n";
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
            echo "🎉 All tests passed! Settings subsections activity logging is working correctly.\n";
        } else {
            echo "⚠️  Some tests failed. Please check the implementation.\n";
        }
    }
}

// Run the tests
try {
    $test = new SettingsSubsectionsActivityLoggerTest();
    $test->runAllTests();
} catch (Exception $e) {
    echo "❌ Test execution failed: " . $e->getMessage() . "\n";
    exit(1);
}
