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
        Schema::create('leave_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('tokens')->default(0)->comment('Available leave tokens');
            $table->integer('earned_tokens')->default(0)->comment('Total tokens earned');
            $table->integer('used_tokens')->default(0)->comment('Total tokens used');
            $table->date('last_granted_week')->nullable()->comment('Last week start date when token was granted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_tokens');
    }
};
