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

        // Add mart banners permissions to super admin
        $this->call([
            MartBannerPermissionsSeeder::class,
        ]);

        // Add banners permissions to super admin
        $this->call([
            BannerPermissionsSeeder::class,
        ]);

        // Add mart permissions to super admin
        $this->call([
            MartPermissionsSeeder::class,
        ]);

        // Add brands permissions to super admin
        $this->call([
            BrandsPermissionsSeeder::class,
        ]);
    }
}

//Generic command
//php artisan db:seed --class=MartPermissionsSeeder
//php artisan db:seed --class=BrandsPermissionsSeeder
//php artisan db:seed --class=MartItemsPermissionsSeeder
//php artisan db:seed --class=MartBannerPermissionsSeeder
//php artisan db:seed --class=BannerPermissionsSeeder
//php artisan db:seed --class=MartPermissionsSeeder
//php artisan db:seed --class=BrandsPermissionsSeeder
//php artisan db:seed --class=MartItemsPermissionsSeeder
