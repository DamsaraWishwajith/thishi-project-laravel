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
        Schema::table('payment_slips', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_slips', 'bill_no')) {
                $table->string('bill_no', 50)->nullable()->after('month');
            }
            if (!Schema::hasColumn('payment_slips', 'amount')) {
                $table->decimal('amount', 12, 2)->default(0.00)->after('bill_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_slips', function (Blueprint $table) {
            $table->dropColumn(['bill_no', 'amount']);
        });
    }
};
