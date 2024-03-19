<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Status;


class StatusSeeder extends Seeder
{
    /**
    * Statuses definition
    */
   private function statuses() {
       return [
           'requested',
           'in progress',
           'new available',
           'available',
           'done'
       ];
   }

   /**
    * Run the database seeds.
    */
   public function run(): void
   {           
       DB::statement('SET FOREIGN_KEY_CHECKS=0;');

       DB::table('statuses')->truncate();

       foreach ($this->statuses() as $status) {
           Status::create(['title' => $status]);
       }
       DB::statement('SET FOREIGN_KEY_CHECKS=1;');
   }
}
