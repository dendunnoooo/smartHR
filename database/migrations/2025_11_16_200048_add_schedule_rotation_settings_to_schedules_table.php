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
        Schema::table('schedules', function (Blueprint $table) {
            $table->integer('rotation_day')->nullable()->after('is_active')->comment('Day of month for schedule rotation (1-31)');
            $table->foreignId('next_schedule_id')->nullable()->after('rotation_day')->constrained('schedules')->onDelete('set null')->comment('Schedule to rotate to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['next_schedule_id']);
            $table->dropColumn(['rotation_day', 'next_schedule_id']);
        });
    }
};
