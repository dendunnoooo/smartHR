<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the general_salary settings JSON payload to include late deduction settings
        DB::table('settings')
            ->where('group', 'general_salary')
            ->update([
                'payload' => DB::raw("JSON_SET(
                    payload,
                    '$.enable_late_deduction', false,
                    '$.late_grace_minutes', '0',
                    '$.late_deduction_per_minute', '0'
                )")
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove late deduction settings from the JSON payload
        DB::table('settings')
            ->where('group', 'general_salary')
            ->update([
                'payload' => DB::raw("JSON_REMOVE(
                    payload,
                    '$.enable_late_deduction',
                    '$.late_grace_minutes',
                    '$.late_deduction_per_minute'
                )")
            ]);
    }
};
