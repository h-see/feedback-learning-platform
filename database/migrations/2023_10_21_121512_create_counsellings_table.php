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
        Schema::create('counsellings', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('title');
            $table->json('postprocessing_process');
            $table->unsignedInteger('course_member_id');
            $table->foreign('course_member_id')
                ->references('id')->on('course_members')
                ->onDelete('cascade');
            $table->unsignedInteger('counselling_setup_id');
            $table->foreign('counselling_setup_id')
                ->references('id')->on('counselling_setups')
                ->onDelete('cascade');
            $table->unsignedInteger('persona_id');
            $table->foreign('persona_id')
                ->references('id')->on('personas')
                ->nullable();
            $table->unsignedInteger('status_chat_id');
            $table->foreign('status_chat_id')
                ->references('id')->on('statuses')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counsellings');
    }
};
