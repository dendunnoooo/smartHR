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
        Schema::table('token_conversions', function (Blueprint $table) {
            $table->boolean('included_in_payroll')->default(false)->after('notes');
            $table->timestamp('payroll_date')->nullable()->after('included_in_payroll');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('token_conversions', function (Blueprint $table) {
            $table->dropColumn(['included_in_payroll', 'payroll_date']);
        });
    }
};
