<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RestaurantScheduleController extends Controller
{
    /**
     * Get current schedule configuration
     */
    public function getSchedule()
    {
        $schedule = Cache::get('restaurant_schedule', [
            'enabled' => false,
            'open_time' => '09:30',
            'close_time' => '22:30',
            'timezone' => 'Asia/Kolkata'
        ]);
        
        return response()->json([
            'success' => true,
            'data' => $schedule
        ]);
    }
    
    /**
     * Update schedule configuration
     */
    public function updateSchedule(Request $request)
    {
        $request->validate([
            'enabled' => 'boolean',
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i',
            'timezone' => 'required|string'
        ]);
        
        $schedule = [
            'enabled' => $request->enabled,
            'open_time' => $request->open_time,
            'close_time' => $request->close_time,
            'timezone' => $request->timezone,
            'updated_at' => now()->toISOString()
        ];
        
        // Store in cache
        Cache::put('restaurant_schedule', $schedule, now()->addDays(30));
        
        // Log the update
        Log::info('Restaurant schedule updated', $schedule);
        
        return response()->json([
            'success' => true,
            'message' => 'Schedule updated successfully',
            'data' => $schedule
        ]);
    }
    
    /**
     * Get next scheduled action
     */
    public function getNextAction()
    {
        $schedule = Cache::get('restaurant_schedule', [
            'enabled' => false,
            'open_time' => '09:30',
            'close_time' => '22:30',
            'timezone' => 'Asia/Kolkata'
        ]);
        
        if (!$schedule['enabled']) {
            return response()->json([
                'success' => true,
                'data' => [
                    'next_action' => 'Schedule disabled',
                    'next_time' => null,
                    'current_status' => 'unknown'
                ]
            ]);
        }
        
        $now = Carbon::now($schedule['timezone']);
        $currentTime = $now->format('H:i');
        
        $openTime = Carbon::createFromFormat('H:i', $schedule['open_time'], $schedule['timezone']);
        $closeTime = Carbon::createFromFormat('H:i', $schedule['close_time'], $schedule['timezone']);
        
        $nextAction = '';
        $nextTime = null;
        $currentStatus = '';
        
        if ($currentTime < $schedule['open_time']) {
            $nextAction = 'Opening';
            $nextTime = $openTime->format('g:i A');
            $currentStatus = 'closed';
        } elseif ($currentTime >= $schedule['open_time'] && $currentTime < $schedule['close_time']) {
            $nextAction = 'Closing';
            $nextTime = $closeTime->format('g:i A');
            $currentStatus = 'open';
        } else {
            $nextAction = 'Opening tomorrow';
            $nextTime = $openTime->addDay()->format('g:i A');
            $currentStatus = 'closed';
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'next_action' => $nextAction,
                'next_time' => $nextTime,
                'current_status' => $currentStatus,
                'current_time' => $now->format('g:i A'),
                'schedule' => $schedule
            ]
        ]);
    }
    
    /**
     * Manually trigger open/close action
     */
    public function triggerAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:open,close'
        ]);
        
        $action = $request->action;
        
        try {
            // Execute the command
            $output = [];
            $returnCode = 0;
            
            exec("php artisan restaurants:auto-schedule {$action} 2>&1", $output, $returnCode);
            
            if ($returnCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully {$action}ed all restaurants",
                    'output' => implode("\n", $output)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to {$action} restaurants",
                    'output' => implode("\n", $output)
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to trigger restaurant action: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to execute action: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get schedule status and logs
     */
    public function getStatus()
    {
        $schedule = Cache::get('restaurant_schedule', [
            'enabled' => false,
            'open_time' => '09:30',
            'close_time' => '22:30',
            'timezone' => 'Asia/Kolkata'
        ]);
        
        // Check if log file exists
        $logFile = storage_path('logs/restaurant-schedule.log');
        $recentLogs = [];
        
        if (file_exists($logFile)) {
            $logs = file($logFile);
            $recentLogs = array_slice($logs, -10); // Last 10 lines
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'schedule' => $schedule,
                'recent_logs' => $recentLogs,
                'log_file_exists' => file_exists($logFile),
                'last_updated' => $schedule['updated_at'] ?? null
            ]
        ]);
    }
}
