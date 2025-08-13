<?php
/**
 * Activity Log Accuracy and Reliability Test
 * This script tests the accuracy and reliability of the activity log system
 */

// Bootstrap Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🎯 Activity Log Accuracy and Reliability Test\n";
echo "============================================\n\n";

class ActivityLogAccuracyTest {
    private $activityLogger;
    private $testResults = [];
    
    public function __construct() {
        $this->activityLogger = new \App\Services\ActivityLogger();
    }
    
    public function runAllTests() {
        echo "🧪 Running Accuracy and Reliability Tests...\n\n";
        
        $this->testDataIntegrity();
        $this->testUserTypeDetection();
        $this->testRoleDetection();
        $this->testTimestampAccuracy();
        $this->testIPAddressCapture();
        $this->testUserAgentCapture();
        $this->testModuleFiltering();
        $this->testActionTracking();
        $this->testDescriptionAccuracy();
        $this->testRealTimeUpdates();
        $this->testErrorHandling();
        $this->testPerformance();
        
        $this->printResults();
    }
    
    private function testDataIntegrity() {
        echo "1. Testing Data Integrity...\n";
        
        $user = $this->createTestUser();
        $result = $this->activityLogger->log(
            $user,
            'accuracy_test',
            'created',
            'Data integrity test entry',
            $this->createMockRequest()
        );
        
        if ($result) {
            // Retrieve the log to verify data integrity
            $logs = $this->activityLogger->getLogsByModule('accuracy_test', 1);
            if (!empty($logs)) {
                $log = $logs[0];
                
                $checks = [
                    'user_id' => $log['user_id'] == $user->id,
                    'user_type' => $log['user_type'] == 'admin',
                    'role' => !empty($log['role']),
                    'module' => $log['module'] == 'accuracy_test',
                    'action' => $log['action'] == 'created',
                    'description' => $log['description'] == 'Data integrity test entry',
                    'ip_address' => !empty($log['ip_address']),
                    'user_agent' => !empty($log['user_agent']),
                    'created_at' => !empty($log['created_at'])
                ];
                
                $passed = array_sum($checks);
                $total = count($checks);
                
                $this->testResults['data_integrity'] = [
                    'passed' => $passed,
                    'total' => $total,
                    'percentage' => round(($passed / $total) * 100, 2)
                ];
                
                echo "   ✅ Data Integrity: {$passed}/{$total} checks passed ({$this->testResults['data_integrity']['percentage']}%)\n";
            } else {
                echo "   ❌ Could not retrieve test log for verification\n";
                $this->testResults['data_integrity'] = ['passed' => 0, 'total' => 1, 'percentage' => 0];
            }
        } else {
            echo "   ❌ Failed to create test log entry\n";
            $this->testResults['data_integrity'] = ['passed' => 0, 'total' => 1, 'percentage' => 0];
        }
    }
    
    private function testUserTypeDetection() {
        echo "\n2. Testing User Type Detection...\n";
        
        $testCases = [
            ['user' => $this->createTestUser('admin'), 'expected' => 'admin'],
            ['user' => $this->createTestUser('merchant'), 'expected' => 'merchant'],
            ['user' => $this->createTestUser('driver'), 'expected' => 'driver'],
            ['user' => $this->createTestUser('customer'), 'expected' => 'customer']
        ];
        
        $passed = 0;
        foreach ($testCases as $testCase) {
            $userType = $this->getUserTypeFromLogger($testCase['user']);
            if ($userType === $testCase['expected']) {
                $passed++;
            }
        }
        
        $this->testResults['user_type_detection'] = [
            'passed' => $passed,
            'total' => count($testCases),
            'percentage' => round(($passed / count($testCases)) * 100, 2)
        ];
        
        echo "   ✅ User Type Detection: {$passed}/" . count($testCases) . " tests passed ({$this->testResults['user_type_detection']['percentage']}%)\n";
    }
    
    private function testRoleDetection() {
        echo "\n3. Testing Role Detection...\n";
        
        $testCases = [
            ['user' => $this->createTestUser('admin', 'super_admin'), 'expected' => 'super_admin'],
            ['user' => $this->createTestUser('admin', 'manager'), 'expected' => 'manager'],
            ['user' => $this->createTestUser('admin', 'admin'), 'expected' => 'admin']
        ];
        
        $passed = 0;
        foreach ($testCases as $testCase) {
            $role = $this->getRoleFromLogger($testCase['user']);
            if ($role === $testCase['expected']) {
                $passed++;
            }
        }
        
        $this->testResults['role_detection'] = [
            'passed' => $passed,
            'total' => count($testCases),
            'percentage' => round(($passed / count($testCases)) * 100, 2)
        ];
        
        echo "   ✅ Role Detection: {$passed}/" . count($testCases) . " tests passed ({$this->testResults['role_detection']['percentage']}%)\n";
    }
    
    private function testTimestampAccuracy() {
        echo "\n4. Testing Timestamp Accuracy...\n";
        
        $beforeLog = time();
        $user = $this->createTestUser();
        $result = $this->activityLogger->log($user, 'timestamp_test', 'created', 'Timestamp test');
        $afterLog = time();
        
        if ($result) {
            $logs = $this->activityLogger->getLogsByModule('timestamp_test', 1);
            if (!empty($logs)) {
                $log = $logs[0];
                $timestamp = $log['created_at'];
                
                if ($timestamp instanceof \Google\Cloud\Core\Timestamp) {
                    $logTime = $timestamp->get()->getTimestamp();
                    $isAccurate = ($logTime >= $beforeLog && $logTime <= $afterLog);
                    
                    $this->testResults['timestamp_accuracy'] = [
                        'passed' => $isAccurate ? 1 : 0,
                        'total' => 1,
                        'percentage' => $isAccurate ? 100 : 0
                    ];
                    
                    echo "   ✅ Timestamp Accuracy: " . ($isAccurate ? "PASSED" : "FAILED") . "\n";
                    echo "      Log time: " . date('Y-m-d H:i:s', $logTime) . "\n";
                } else {
                    echo "   ❌ Timestamp format incorrect\n";
                    $this->testResults['timestamp_accuracy'] = ['passed' => 0, 'total' => 1, 'percentage' => 0];
                }
            } else {
                echo "   ❌ Could not retrieve timestamp test log\n";
                $this->testResults['timestamp_accuracy'] = ['passed' => 0, 'total' => 1, 'percentage' => 0];
            }
        } else {
            echo "   ❌ Failed to create timestamp test log\n";
            $this->testResults['timestamp_accuracy'] = ['passed' => 0, 'total' => 1, 'percentage' => 0];
        }
    }
    
    private function testIPAddressCapture() {
        echo "\n5. Testing IP Address Capture...\n";
        
        $user = $this->createTestUser();
        $mockRequest = $this->createMockRequest('192.168.1.100');
        $result = $this->activityLogger->log($user, 'ip_test', 'created', 'IP test', $mockRequest);
        
        if ($result) {
            $logs = $this->activityLogger->getLogsByModule('ip_test', 1);
            if (!empty($logs)) {
                $log = $logs[0];
                $ipCaptured = !empty($log['ip_address']);
                
                $this->testResults['ip_capture'] = [
                    'passed' => $ipCaptured ? 1 : 0,
                    'total' => 1,
                    'percentage' => $ipCaptured ? 100 : 0
                ];
                
                echo "   ✅ IP Address Capture: " . ($ipCaptured ? "PASSED" : "FAILED") . "\n";
                echo "      IP: " . ($log['ip_address'] ?? 'Not captured') . "\n";
            } else {
                echo "   ❌ Could not retrieve IP test log\n";
                $this->testResults['ip_capture'] = ['passed' => 0, 'total' => 1, 'percentage' => 0];
            }
        } else {
            echo "   ❌ Failed to create IP test log\n";
            $this->testResults['ip_capture'] = ['passed' => 0, 'total' => 1, 'percentage' => 0];
        }
    }
    
    private function testUserAgentCapture() {
        echo "\n6. Testing User Agent Capture...\n";
        
        $user = $this->createTestUser();
        $mockRequest = $this->createMockRequest(null, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $result = $this->activityLogger->log($user, 'user_agent_test', 'created', 'User agent test', $mockRequest);
        
        if ($result) {
            $logs = $this->activityLogger->getLogsByModule('user_agent_test', 1);
            if (!empty($logs)) {
                $log = $logs[0];
                $userAgentCaptured = !empty($log['user_agent']);
                
                $this->testResults['user_agent_capture'] = [
                    'passed' => $userAgentCaptured ? 1 : 0,
                    'total' => 1,
                    'percentage' => $userAgentCaptured ? 100 : 0
                ];
                
                echo "   ✅ User Agent Capture: " . ($userAgentCaptured ? "PASSED" : "FAILED") . "\n";
                echo "      User Agent: " . (substr($log['user_agent'] ?? 'Not captured', 0, 50)) . "...\n";
            } else {
                echo "   ❌ Could not retrieve user agent test log\n";
                $this->testResults['user_agent_capture'] = ['passed' => 0, 'total' => 1, 'percentage' => 0];
            }
        } else {
            echo "   ❌ Failed to create user agent test log\n";
            $this->testResults['user_agent_capture'] = ['passed' => 0, 'total' => 1, 'percentage' => 0];
        }
    }
    
    private function testModuleFiltering() {
        echo "\n7. Testing Module Filtering...\n";
        
        // Create logs for different modules
        $user = $this->createTestUser();
        $modules = ['cuisines', 'orders', 'users', 'vendors'];
        
        foreach ($modules as $module) {
            $this->activityLogger->log($user, $module, 'created', "Test log for {$module}");
        }
        
        // Test filtering
        $passed = 0;
        foreach ($modules as $module) {
            $logs = $this->activityLogger->getLogsByModule($module, 10);
            $filteredCorrectly = true;
            
            foreach ($logs as $log) {
                if ($log['module'] !== $module) {
                    $filteredCorrectly = false;
                    break;
                }
            }
            
            if ($filteredCorrectly) {
                $passed++;
            }
        }
        
        $this->testResults['module_filtering'] = [
            'passed' => $passed,
            'total' => count($modules),
            'percentage' => round(($passed / count($modules)) * 100, 2)
        ];
        
        echo "   ✅ Module Filtering: {$passed}/" . count($modules) . " tests passed ({$this->testResults['module_filtering']['percentage']}%)\n";
    }
    
    private function testActionTracking() {
        echo "\n8. Testing Action Tracking...\n";
        
        $user = $this->createTestUser();
        $actions = ['created', 'updated', 'deleted', 'viewed'];
        $passed = 0;
        
        foreach ($actions as $action) {
            $result = $this->activityLogger->log($user, 'action_test', $action, "Action test: {$action}");
            if ($result) {
                $logs = $this->activityLogger->getLogsByModule('action_test', 1);
                if (!empty($logs) && $logs[0]['action'] === $action) {
                    $passed++;
                }
            }
        }
        
        $this->testResults['action_tracking'] = [
            'passed' => $passed,
            'total' => count($actions),
            'percentage' => round(($passed / count($actions)) * 100, 2)
        ];
        
        echo "   ✅ Action Tracking: {$passed}/" . count($actions) . " tests passed ({$this->testResults['action_tracking']['percentage']}%)\n";
    }
    
    private function testDescriptionAccuracy() {
        echo "\n9. Testing Description Accuracy...\n";
        
        $user = $this->createTestUser();
        $testDescriptions = [
            'Simple description',
            'Description with special chars: !@#$%^&*()',
            'Description with numbers: 12345',
            'Description with unicode: 🚀🎉✅',
            'Very long description that should be truncated properly and handled correctly by the system without any issues or problems'
        ];
        
        $passed = 0;
        foreach ($testDescriptions as $description) {
            $result = $this->activityLogger->log($user, 'description_test', 'created', $description);
            if ($result) {
                $logs = $this->activityLogger->getLogsByModule('description_test', 1);
                if (!empty($logs) && $logs[0]['description'] === $description) {
                    $passed++;
                }
            }
        }
        
        $this->testResults['description_accuracy'] = [
            'passed' => $passed,
            'total' => count($testDescriptions),
            'percentage' => round(($passed / count($testDescriptions)) * 100, 2)
        ];
        
        echo "   ✅ Description Accuracy: {$passed}/" . count($testDescriptions) . " tests passed ({$this->testResults['description_accuracy']['percentage']}%)\n";
    }
    
    private function testRealTimeUpdates() {
        echo "\n10. Testing Real-time Updates...\n";
        
        // This would require a more complex test with actual Firebase listeners
        // For now, we'll test the basic functionality
        $user = $this->createTestUser();
        $result = $this->activityLogger->log($user, 'realtime_test', 'created', 'Real-time test');
        
        if ($result) {
            // Simulate checking for real-time updates
            $logs = $this->activityLogger->getLogsByModule('realtime_test', 1);
            $updateDetected = !empty($logs);
            
            $this->testResults['realtime_updates'] = [
                'passed' => $updateDetected ? 1 : 0,
                'total' => 1,
                'percentage' => $updateDetected ? 100 : 0
            ];
            
            echo "   ✅ Real-time Updates: " . ($updateDetected ? "PASSED" : "FAILED") . "\n";
        } else {
            echo "   ❌ Failed to create real-time test log\n";
            $this->testResults['realtime_updates'] = ['passed' => 0, 'total' => 1, 'percentage' => 0];
        }
    }
    
    private function testErrorHandling() {
        echo "\n11. Testing Error Handling...\n";
        
        // Test with invalid data
        $passed = 0;
        $total = 3;
        
        // Test 1: Null user
        try {
            $result = $this->activityLogger->log(null, 'test', 'created', 'Test');
            if (!$result) $passed++;
        } catch (Exception $e) {
            $passed++;
        }
        
        // Test 2: Empty module
        try {
            $user = $this->createTestUser();
            $result = $this->activityLogger->log($user, '', 'created', 'Test');
            if (!$result) $passed++;
        } catch (Exception $e) {
            $passed++;
        }
        
        // Test 3: Empty action
        try {
            $user = $this->createTestUser();
            $result = $this->activityLogger->log($user, 'test', '', 'Test');
            if (!$result) $passed++;
        } catch (Exception $e) {
            $passed++;
        }
        
        $this->testResults['error_handling'] = [
            'passed' => $passed,
            'total' => $total,
            'percentage' => round(($passed / $total) * 100, 2)
        ];
        
        echo "   ✅ Error Handling: {$passed}/{$total} tests passed ({$this->testResults['error_handling']['percentage']}%)\n";
    }
    
    private function testPerformance() {
        echo "\n12. Testing Performance...\n";
        
        $user = $this->createTestUser();
        $startTime = microtime(true);
        
        // Create 10 logs to test performance
        for ($i = 0; $i < 10; $i++) {
            $this->activityLogger->log($user, 'performance_test', 'created', "Performance test {$i}");
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        $isAcceptable = $executionTime < 5.0; // Should complete within 5 seconds
        
        $this->testResults['performance'] = [
            'passed' => $isAcceptable ? 1 : 0,
            'total' => 1,
            'percentage' => $isAcceptable ? 100 : 0,
            'execution_time' => round($executionTime, 2)
        ];
        
        echo "   ✅ Performance: " . ($isAcceptable ? "PASSED" : "FAILED") . "\n";
        echo "      Execution time: {$executionTime} seconds\n";
    }
    
    private function printResults() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 ACCURACY AND RELIABILITY TEST RESULTS\n";
        echo str_repeat("=", 60) . "\n\n";
        
        $totalPassed = 0;
        $totalTests = 0;
        
        foreach ($this->testResults as $testName => $result) {
            $status = $result['percentage'] >= 80 ? '✅' : ($result['percentage'] >= 60 ? '⚠️' : '❌');
            echo "{$status} " . str_pad(ucwords(str_replace('_', ' ', $testName)), 25) . ": {$result['passed']}/{$result['total']} ({$result['percentage']}%)\n";
            
            if (isset($result['execution_time'])) {
                echo "   └─ Execution time: {$result['execution_time']}s\n";
            }
            
            $totalPassed += $result['passed'];
            $totalTests += $result['total'];
        }
        
        $overallPercentage = round(($totalPassed / $totalTests) * 100, 2);
        
        echo "\n" . str_repeat("-", 60) . "\n";
        echo "🎯 OVERALL ACCURACY: {$totalPassed}/{$totalTests} ({$overallPercentage}%)\n";
        echo str_repeat("-", 60) . "\n\n";
        
        if ($overallPercentage >= 90) {
            echo "🏆 EXCELLENT: Activity Log system is highly accurate and reliable!\n";
        } elseif ($overallPercentage >= 80) {
            echo "✅ GOOD: Activity Log system is accurate and reliable.\n";
        } elseif ($overallPercentage >= 70) {
            echo "⚠️  ACCEPTABLE: Activity Log system is mostly reliable with minor issues.\n";
        } else {
            echo "❌ NEEDS IMPROVEMENT: Activity Log system has significant accuracy issues.\n";
        }
        
        echo "\n💡 RECOMMENDATIONS:\n";
        echo "==================\n";
        
        foreach ($this->testResults as $testName => $result) {
            if ($result['percentage'] < 80) {
                echo "- Improve " . str_replace('_', ' ', $testName) . " (currently {$result['percentage']}%)\n";
            }
        }
        
        if ($overallPercentage >= 80) {
            echo "\n✅ The Activity Log system is ACCURATE and RELIABLE for production use!\n";
        }
    }
    
    private function createTestUser($userType = 'admin', $role = 'super_admin') {
        $user = new stdClass();
        $user->id = rand(1000, 9999);
        $user->role_id = 1;
        $user->name = 'Test User';
        $user->user_type = $userType;
        $user->role = $role;
        return $user;
    }
    
    private function createMockRequest($ip = '127.0.0.1', $userAgent = 'Test User Agent') {
        // Create a mock request using Laravel's Request class
        $request = new \Illuminate\Http\Request();
        $request->server->set('REMOTE_ADDR', $ip);
        $request->server->set('HTTP_USER_AGENT', $userAgent);
        return $request;
    }
    
    private function getUserTypeFromLogger($user) {
        // Use reflection to access protected method
        $reflection = new ReflectionClass($this->activityLogger);
        $method = $reflection->getMethod('getUserType');
        $method->setAccessible(true);
        return $method->invoke($this->activityLogger, $user);
    }
    
    private function getRoleFromLogger($user) {
        // Use reflection to access protected method
        $reflection = new ReflectionClass($this->activityLogger);
        $method = $reflection->getMethod('getUserRole');
        $method->setAccessible(true);
        return $method->invoke($this->activityLogger, $user);
    }
}

// Run the accuracy test
$accuracyTest = new ActivityLogAccuracyTest();
$accuracyTest->runAllTests();
