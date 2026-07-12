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
        Schema::create('bill', function (Blueprint $table) {
            $table->id();
            $table->integer('nic');

            $table->string('january_point', 50)->default('0');
            $table->string('january_bill', 50)->default('0');
            $table->string('february_point', 50)->default('0');
            $table->string('february_bill', 50)->default('0');
            $table->string('march_point', 50)->default('0');
            $table->string('march_bill', 50)->default('0');
            $table->string('april_point', 50)->default('0');
            $table->string('april_bill', 50)->default('0');
            $table->string('may_point', 50)->default('0');
            $table->string('may_bill', 50)->default('0');
            $table->string('june_point', 50)->default('0');
            $table->string('june_bill', 50)->default('0');
            $table->string('july_point', 50)->default('0');
            $table->string('july_bill', 50)->default('0');
            $table->string('august_point', 50)->default('0');
            $table->string('august_bill', 50)->default('0');
            $table->string('september_point', 50)->default('0');
            $table->string('september_bill', 50)->default('0');
            $table->string('october_point', 50)->default('0');
            $table->string('october_bill', 50)->default('0');
            $table->string('november_point', 50)->default('0');
            $table->string('november_bill', 50)->default('0');
            $table->string('december_point', 50)->default('0');
            $table->string('december_bill', 50)->default('0');

            $table->string('total_points', 50)->default('0');
            $table->string('total_bill', 50)->default('0');

            $table->timestamps();

            $table->foreign('nic')
                ->references('nic')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill');
    }
};
