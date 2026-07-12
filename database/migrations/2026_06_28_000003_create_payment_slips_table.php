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
        if (!Schema::hasTable('payment_slips')) {
            Schema::create('payment_slips', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('nic');
                $table->integer('year');
                $table->integer('month');
                $table->string('slip_path');
                $table->string('status', 30)->default('pending'); // pending, approved, rejected
                $table->timestamps();

                // Foreign key constraint or index
                $table->index('nic');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_slips');
    }
};
