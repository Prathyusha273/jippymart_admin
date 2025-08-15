<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        
        // Add cuisines permissions to super admin
        $this->call([
            CuisinesPermissionsSeeder::class,
        ]);
        
        // Add promotions, media, and activity-logs permissions to super admin
        $this->call([
            PromotionsMediaActivityLogsPermissionsSeeder::class,
        ]);
        
        // Add menu_periods permissions to super admin
        $this->call([
            MenuPeriodsPermissionsSeeder::class,
        ]);
    }
}
