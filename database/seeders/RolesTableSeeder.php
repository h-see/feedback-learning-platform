<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Roles definition
     */
    private function roles() {
        return [
            'admin',
            'editingteacher',
            'teacher',
            'student'
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {           
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('roles')->truncate();

        foreach ($this->roles() as $role) {
            Role::create(['title' => $role]);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
