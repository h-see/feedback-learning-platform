<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesTableSeeder::class);
        $this->call(AdminUserSeeder::class);
        $this->call(CounsellingFieldsSeeder::class);
        $this->call(PersonasSeeder::class);
        $this->call(StatusSeeder::class);
        $this->call(FeedbackSourceSeeder::class);
        $this->call(FeedbackTypeSeeder::class);
    }
}
