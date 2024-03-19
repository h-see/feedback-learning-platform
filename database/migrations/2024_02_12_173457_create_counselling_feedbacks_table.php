<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('counselling_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp('accepted_at')->nullable(); // date peer accepted feedback request
            $table->timestamp('received_at')->nullable(); // date peer submitted feedback
            $table->longText('feedback_text')->nullable();
            $table->longText('ai_feedback_properties')->nullable();

            $table->unsignedInteger('counselling_id');
            $table->foreign('counselling_id')
                ->references('id')
                ->on('counsellings')
                ->onDelete('cascade');

            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade')
                ->nullable();

            $table->unsignedInteger('status_feedback_id');
            $table->foreign('status_feedback_id')
                ->references('id')->on('statuses');

            $table->unsignedInteger('feedback_source_id');
            $table->foreign('feedback_source_id')
                ->references('id')->on('feedback_sources')
                ->onDelete('cascade');

            $table->unsignedInteger('feedback_types_id')->nullable();
            $table->foreign('feedback_types_id')
                ->references('id')->on('feedback_types')
                ->onDelete('cascade')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counselling_feedbacks');
    }
};
