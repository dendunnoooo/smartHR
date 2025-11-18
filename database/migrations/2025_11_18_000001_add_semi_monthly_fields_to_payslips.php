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
        Schema::table('payslips', function (Blueprint $table) {
            $table->string('cutoff_period')->nullable()->after('type')->comment('1st (1-15) or 2nd (16-end)');
            $table->integer('cutoff_number')->nullable()->after('cutoff_period')->comment('1 or 2');
            $table->boolean('is_semi_monthly')->default(false)->after('cutoff_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropColumn(['cutoff_period', 'cutoff_number', 'is_semi_monthly']);
        });
    }
};
