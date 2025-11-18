<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('tokens')->default(0);
            $table->integer('earned_tokens')->default(0);
            $table->integer('converted_tokens')->default(0);
            $table->date('last_granted_month')->nullable();
            $table->timestamps();
        });

        Schema::create('token_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('tokens_converted');
            $table->enum('conversion_type', ['cash', 'leave_credits']);
            $table->decimal('cash_amount', 10, 2)->nullable();
            $table->integer('leave_credits_added')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('converted_at');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('token_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('token_settings')->insert([
            [
                'key' => 'token_to_cash',
                'value' => '500',
                'label' => 'Token to Cash Value (PHP)',
                'description' => 'Amount in PHP per token when converted to cash',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'token_to_leave_credits',
                'value' => '2',
                'label' => 'Token to Leave Credits',
                'description' => 'Number of leave credits per token',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('token_conversions');
        Schema::dropIfExists('monthly_tokens');
        Schema::dropIfExists('token_settings');
    }
};
