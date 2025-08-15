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
        // Restaurant Auto Schedule - Check every minute
        $schedule->command('restaurants:auto-schedule check')
                 ->everyMinute()
                 ->timezone('Asia/Kolkata')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/restaurant-schedule.log'));

        // Alternative: Run at specific times
        // $schedule->command('restaurants:auto-schedule open')
        //          ->dailyAt('09:30')
        //          ->timezone('Asia/Kolkata')
        //          ->withoutOverlapping();
        //
        // $schedule->command('restaurants:auto-schedule close')
        //          ->dailyAt('22:30')
        //          ->timezone('Asia/Kolkata')
        //          ->withoutOverlapping();
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
