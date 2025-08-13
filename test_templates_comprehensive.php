<?php
/**
 * Comprehensive Test Script for Template Modules Activity Logging
 * Tests: terms_conditions, privacy_policy, footer_template, landing_page_template
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class TemplatesActivityLoggerTest
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
        echo "🧪 Starting Comprehensive Templates Activity Logging Tests\n";
        echo "==========================================================\n\n";

        // Test Terms and Conditions
        $this->testTermsAndConditions();
        
        // Test Privacy Policy
        $this->testPrivacyPolicy();
        
        // Test Footer Template
        $this->testFooterTemplate();
        
        // Test Landing Page Template
        $this->testLandingPageTemplate();

        // Print Summary
        $this->printSummary();
    }

    private function testTermsAndConditions()
    {
        echo "📜 Testing Terms and Conditions Logging...\n";
        
        $this->testLogActivity('terms_conditions', 'updated', 'Updated Terms and Conditions content');
        
        echo "✅ Terms and Conditions tests completed\n\n";
    }

    private function testPrivacyPolicy()
    {
        echo "🔒 Testing Privacy Policy Logging...\n";
        
        $this->testLogActivity('privacy_policy', 'updated', 'Updated Privacy Policy content');
        
        echo "✅ Privacy Policy tests completed\n\n";
    }

    private function testFooterTemplate()
    {
        echo "🦶 Testing Footer Template Logging...\n";
        
        $this->testLogActivity('footer_template', 'updated', 'Updated Footer Template content');
        
        echo "✅ Footer Template tests completed\n\n";
    }

    private function testLandingPageTemplate()
    {
        echo "🏠 Testing Landing Page Template Logging...\n";
        
        $this->testLogActivity('landing_page_template', 'updated', 'Updated Landing Page Template content');
        
        echo "✅ Landing Page Template tests completed\n\n";
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
            echo "🎉 All tests passed! Template modules activity logging is working correctly.\n";
        } else {
            echo "⚠️  Some tests failed. Please check the implementation.\n";
        }
    }
}

// Run the tests
try {
    $test = new TemplatesActivityLoggerTest();
    $test->runAllTests();
} catch (Exception $e) {
    echo "❌ Test execution failed: " . $e->getMessage() . "\n";
    exit(1);
}
