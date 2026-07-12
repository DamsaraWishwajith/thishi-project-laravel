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
        Schema::create('water_records', function (Blueprint $table) {
            $table->id();
            $table->integer('nic');
            $table->date('date');
            $table->decimal('water_rate', 10, 2)->default(0.00);
            $table->decimal('points', 10, 2)->default(0.00);
            $table->decimal('bill', 12, 2)->default(0.00);
            $table->timestamps();

            // Set up foreign key relation referencing users.nic
            $table->foreign('nic')
                ->references('nic')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            // Unique index to prevent duplicate user entries on the same date
            $table->unique(['nic', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_records');
    }
};
