<?php

namespace Database\Seeders;

use App\Models\FeedbackSource;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeedbackSourceSeeder extends Seeder
{
    /**
     * Feedback Sources definition
     */
    private function feedbackSources() {
        return [
            'teacher',
            'peer',
            'ai',
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('feedback_sources')->truncate();

        foreach ($this->feedbackSources() as $source) {
            FeedbackSource::create(['title' => $source]);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
