<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RestaurantAutoSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restaurants:auto-schedule {action=check} {--timezone=Asia/Kolkata}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically open/close restaurants based on schedule';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action');
        $timezone = $this->option('timezone');
        
        $this->info("Restaurant Auto Schedule - Action: {$action}, Timezone: {$timezone}");
        
        try {
            switch ($action) {
                case 'open':
                    $this->openAllRestaurants();
                    break;
                case 'close':
                    $this->closeAllRestaurants();
                    break;
                case 'health-check':
                    $this->healthCheck();
                    break;
                case 'check':
                default:
                    $this->checkAndExecuteSchedule($timezone);
                    break;
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error("Restaurant Auto Schedule Error: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Check current time and execute appropriate action
     */
    private function checkAndExecuteSchedule($timezone)
    {
        $now = Carbon::now($timezone);
        $currentTime = $now->format('H:i');
        
        // Get schedule from cache or config
        $schedule = $this->getSchedule();
        
        $this->info("Current time: {$currentTime}");
        $this->info("Schedule - Open: {$schedule['open_time']}, Close: {$schedule['close_time']}");
        
        if ($currentTime === $schedule['open_time']) {
            $this->info("It's opening time!");
            $this->openAllRestaurants();
        } elseif ($currentTime === $schedule['close_time']) {
            $this->info("It's closing time!");
            $this->closeAllRestaurants();
        } else {
            $this->info("No action needed at this time.");
        }
    }
    
    /**
     * Open all restaurants
     */
    private function openAllRestaurants()
    {
        $this->info("Opening all restaurants...");
        
        try {
            // Update Firestore vendors collection
            $this->updateFirestoreRestaurants(true);
            
            // Log the action
            $this->logActivity('restaurants', 'auto_schedule', 'Automatically opened all restaurants');
            
            $this->info("Successfully opened all restaurants!");
            
        } catch (\Exception $e) {
            $this->error("Failed to open restaurants: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Close all restaurants
     */
    private function closeAllRestaurants()
    {
        $this->info("Closing all restaurants...");
        
        try {
            // Update Firestore vendors collection
            $this->updateFirestoreRestaurants(false);
            
            // Log the action
            $this->logActivity('restaurants', 'auto_schedule', 'Automatically closed all restaurants');
            
            $this->info("Successfully closed all restaurants!");
            
        } catch (\Exception $e) {
            $this->error("Failed to close restaurants: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Health check - verify system is working
     */
    private function healthCheck()
    {
        $this->info("Performing health check...");
        
        try {
            // Test Firebase connection
            $firestore = app('firebase.firestore');
            $vendorsRef = $firestore->database()->collection('vendors');
            
            // Just get count, don't fetch all data
            $snapshot = $vendorsRef->limit(1)->documents();
            $count = iterator_count($snapshot);
            
            $this->info("Health check passed - Firebase connection working");
            $this->info("Sample vendor count: {$count}");
            
            // Log health check
            $this->logActivity('restaurants', 'health_check', 'Auto-schedule health check passed');
            
        } catch (\Exception $e) {
            $this->error("Health check failed: " . $e->getMessage());
            $this->logActivity('restaurants', 'health_check', 'Auto-schedule health check failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update Firestore restaurants with optimized batching
     */
    private function updateFirestoreRestaurants($isOpen)
    {
        $firestore = app('firebase.firestore');
        $vendorsRef = $firestore->database()->collection('vendors');
        
        // Get all vendor documents with pagination to avoid memory issues
        $documents = $vendorsRef->documents();
        
        $batch = $firestore->batch();
        $count = 0;
        $batchCount = 0;
        
        foreach ($documents as $document) {
            $batch->update($document->reference(), [
                ['path' => 'isOpen', 'value' => $isOpen],
                ['path' => 'lastAutoScheduleUpdate', 'value' => [
                    'timestamp' => time(),
                    'action' => $isOpen ? 'opened' : 'closed',
                    'scheduled' => true
                ]]
            ]);
            $count++;
            $batchCount++;
            
            // Commit batch every 500 documents (Firestore limit)
            if ($batchCount >= 500) {
                $batch->commit();
                $batch = $firestore->batch();
                $batchCount = 0;
                $this->info("Updated {$count} restaurants...");
                
                // Add small delay to prevent rate limiting
                usleep(100000); // 0.1 second delay
            }
        }
        
        // Commit remaining documents
        if ($batchCount > 0) {
            $batch->commit();
        }
        
        $this->info("Total restaurants updated: {$count}");
    }
    
    /**
     * Get schedule configuration from cache or default
     */
    private function getSchedule()
    {
        // Try to get from cache first (set by frontend)
        $cachedSchedule = \Illuminate\Support\Facades\Cache::get('restaurant_schedule');
        
        if ($cachedSchedule && isset($cachedSchedule['enabled']) && $cachedSchedule['enabled']) {
            return [
                'open_time' => $cachedSchedule['open_time'] ?? '09:30',
                'close_time' => $cachedSchedule['close_time'] ?? '22:30',
                'timezone' => $cachedSchedule['timezone'] ?? 'Asia/Kolkata'
            ];
        }
        
        // Default schedule if no cache
        return [
            'open_time' => '09:30',
            'close_time' => '22:30',
            'timezone' => 'Asia/Kolkata'
        ];
    }
    
    /**
     * Log activity
     */
    private function logActivity($module, $action, $description)
    {
        try {
            // Log to database if activity logger exists
            if (class_exists('\App\Services\ActivityLogger')) {
                $logger = app('\App\Services\ActivityLogger');
                // Pass null as user since this is a system action
                $logger->log(null, $module, $action, $description);
            }
            
            // Also log to Laravel log
            Log::info("Activity: {$module} - {$action} - {$description}");
            
        } catch (\Exception $e) {
            Log::warning("Failed to log activity: " . $e->getMessage());
        }
    }
}
