<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosting', function (Blueprint $table) {
            $table->id('hosting_id');

            $table->string('hosting_name', 255);
            $table->string('hosting_provider', 150);
            $table->string('plan', 150)->nullable();
            $table->date('expiry_date');
            $table->decimal('cost', 10, 2)->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->foreign('created_by')
                  ->references('user_id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosting');
    }
};