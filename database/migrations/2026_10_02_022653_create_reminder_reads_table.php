<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('record_type', ['domain', 'hosting']);
            $table->unsignedBigInteger('record_id');
            $table->enum('stage', ['warning', 'urgent', 'expired']);
            $table->timestamp('read_at')->useCurrent();

            // Prevent the same stage being marked read twice by the same user
            $table->unique(['user_id', 'record_type', 'record_id', 'stage'], 'unique_read_per_stage');

            // If the user is deleted, their reads go too
            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_reads');
    }
};