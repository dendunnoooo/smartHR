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
        Schema::table('attendance_timestamps', function (Blueprint $table) {
            $table->boolean('is_early')->default(false)->after('ip');
            $table->boolean('is_late')->default(false)->after('is_early');
            $table->integer('minutes_difference')->nullable()->after('is_late')->comment('Minutes early (negative) or late (positive)');
            $table->time('scheduled_start_time')->nullable()->after('minutes_difference');
            $table->time('scheduled_end_time')->nullable()->after('scheduled_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_timestamps', function (Blueprint $table) {
            $table->dropColumn(['is_early', 'is_late', 'minutes_difference', 'scheduled_start_time', 'scheduled_end_time']);
        });
    }
};
