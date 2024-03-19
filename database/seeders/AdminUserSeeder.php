<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    protected $adminEmail;
    protected $adminPassword;

    public function __construct()
    {
        $this->adminEmail = env('APP_ADMIN');
        $this->adminPassword = env('APP_ADMIN_PASSWORD');
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = DB::table('roles')->where('title', 'admin')->first();
        $existingUser = DB::table('users')->where('email', $this->adminEmail)->first();

        if (!$adminRole) {
            $this->command->error('Admin role not found in database');
            return;
        }

        if(!$existingUser) {
            DB::table('users')->insert([
                'name' => 'Administrator',
                'email' => $this->adminEmail,
                'password' => bcrypt($this->adminPassword),
                'main_role_id' => $adminRole->id,
            ]);

            $this->command->info('Administator created');
        } else {
            DB::table('users')->where('email', $this->adminEmail)->update(['main_role_id' => $adminRole->id]);
            $this->command->info('Administrator user already exists. Role has been updated.');
        }
    }
}
