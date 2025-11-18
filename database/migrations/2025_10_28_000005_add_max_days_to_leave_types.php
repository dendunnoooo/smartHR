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
        if (!Schema::hasTable('leave_types')) {
            return;
        }

        if (!Schema::hasColumn('leave_types', 'max_days')) {
            Schema::table('leave_types', function (Blueprint $table) {
                $table->integer('max_days')->nullable()->after('requires_approval');
            });
        }

        // Seed sensible max values for common leave types (case-insensitive match)
        DB::table('leave_types')->whereRaw('LOWER(name) LIKE ?', ['%matern%'])->update(['max_days' => 110]);
        DB::table('leave_types')->whereRaw('LOWER(name) LIKE ?', ['%patern%'])->update(['max_days' => 7]);
        DB::table('leave_types')->whereRaw('LOWER(name) LIKE ?', ['%sick%'])->update(['max_days' => 15]);
        DB::table('leave_types')->whereRaw('LOWER(name) LIKE ?', ['%annual%'])->update(['max_days' => 15]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('leave_types')) {
            return;
        }

        if (Schema::hasColumn('leave_types', 'max_days')) {
            Schema::table('leave_types', function (Blueprint $table) {
                $table->dropColumn('max_days');
            });
        }
    }
};
