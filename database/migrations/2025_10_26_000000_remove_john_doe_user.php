<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove seeded placeholder user(s) matching common seed values
        DB::table('users')->where('email', 'client@smarthr.com')->orWhere(function($q){
            $q->where('firstname', 'John')->where('lastname','Doe');
        })->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // no-op: do not re-create deleted user automatically
    }
};
