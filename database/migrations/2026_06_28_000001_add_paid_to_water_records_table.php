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
        if (!Schema::hasColumn('water_records', 'paid')) {
            Schema::table('water_records', function (Blueprint $table) {
                $table->boolean('paid')->default(false)->after('bill');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('water_records', 'paid')) {
            Schema::table('water_records', function (Blueprint $table) {
                $table->dropColumn('paid');
            });
        }
    }
};
