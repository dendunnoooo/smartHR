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
        Schema::table('leaves', function (Blueprint $table) {
            if (!Schema::hasColumn('leaves', 'date_filed')) {
                $table->date('date_filed')->nullable()->after('id');
            }
            if (!Schema::hasColumn('leaves', 'total_days')) {
                $table->decimal('total_days', 8, 2)->default(0)->after('status');
            }
            if (!Schema::hasColumn('leaves', 'day_type')) {
                $table->string('day_type')->default('full')->after('total_days');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            if (Schema::hasColumn('leaves', 'day_type')) {
                $table->dropColumn('day_type');
            }
            if (Schema::hasColumn('leaves', 'total_days')) {
                $table->dropColumn('total_days');
            }
            if (Schema::hasColumn('leaves', 'date_filed')) {
                $table->dropColumn('date_filed');
            }
        });
    }
};
