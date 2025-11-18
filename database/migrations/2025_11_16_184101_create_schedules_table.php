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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Day Shift", "Night Shift"
            $table->time('start_time'); // e.g., 08:00:00
            $table->time('end_time'); // e.g., 16:00:00
            $table->integer('work_hours')->default(8); // Total work hours
            $table->string('days')->nullable(); // JSON array of working days (Monday-Sunday)
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add schedule_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('schedule_id')->nullable()->after('type')->constrained('schedules')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropColumn('schedule_id');
        });
        
        Schema::dropIfExists('schedules');
    }
};
