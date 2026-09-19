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
        if (!Schema::hasColumn('water_records', 'liters')) {
            Schema::table('water_records', function (Blueprint $table) {
                $table->decimal('liters', 12, 2)->default(0.00)->after('points');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('water_records', 'liters')) {
            Schema::table('water_records', function (Blueprint $table) {
                $table->dropColumn('liters');
            });
        }
    }
};
