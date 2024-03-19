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
        Schema::create('counselling_messages', function (Blueprint $table) {
            $table->unsignedInteger('counselling_id');
            $table->unsignedInteger('message_number')->default(1);
            $table->text('content');
            $table->text('author');
            $table->json('additions');
            $table->timestamps();

            $table->foreign('counselling_id')
                ->references('id')
                ->on('counsellings')
                ->onDelete('cascade');

            $table->primary(['counselling_id', 'message_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counselling_messages');
    }
};
