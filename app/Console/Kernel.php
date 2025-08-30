<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Restaurant Auto Schedule - Run at specific times instead of every minute
        // This reduces process load from 1440 times/day to just 2 times/day
        
        // Open restaurants at 9:30 AM
        $schedule->command('restaurants:auto-schedule open')
                 ->dailyAt('09:30')
                 ->timezone('Asia/Kolkata')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/restaurant-schedule.log'));

        // Close restaurants at 10:30 PM  
        $schedule->command('restaurants:auto-schedule close')
                 ->dailyAt('22:30')
                 ->timezone('Asia/Kolkata')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/restaurant-schedule.log'));

        // Optional: Health check every 6 hours instead of every minute
        $schedule->command('restaurants:auto-schedule health-check')
                 ->everyFourHours()
                 ->timezone('Asia/Kolkata')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/restaurant-schedule.log'));

        // REMOVED: The problematic everyMinute() check that was causing 503 errors
        // $schedule->command('restaurants:auto-schedule check')
        //          ->everyMinute()
        //          ->timezone('Asia/Kolkata')
        //          ->withoutOverlapping()
        //          ->runInBackground()
        //          ->appendOutputTo(storage_path('logs/restaurant-schedule.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
