<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Persona\Models\CounsellingField;

class CounsellingFieldsSeeder extends Seeder
{
    /**
     * Counselling Fields definition
     */
    private function fields() {
        return [
            'Trauerberatung',
            'Familienberatung',
            'Suchtberatung',
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('counselling_fields')->truncate();

        foreach ($this->fields() as $field) {
            CounsellingField::create(['name' => $field]);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');    }
}
