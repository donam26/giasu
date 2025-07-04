<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            UserSeeder::class,
            SubjectSeeder::class,
            ClassLevelSeeder::class,
            TutorSeeder::class,
            BookingSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
