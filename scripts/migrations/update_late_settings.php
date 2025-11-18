<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Check if late deduction settings already exist
    $exists = DB::table('settings')
        ->where('group', 'general_salary')
        ->where('name', 'enable_late_deduction')
        ->exists();
    
    if ($exists) {
        echo "Late deduction settings already exist!\n";
    } else {
        // Insert new settings
        $timestamp = now();
        
        DB::table('settings')->insert([
            [
                'group' => 'general_salary',
                'name' => 'enable_late_deduction',
                'locked' => 0,
                'payload' => json_encode(false),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'group' => 'general_salary',
                'name' => 'late_grace_minutes',
                'locked' => 0,
                'payload' => json_encode('0'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'group' => 'general_salary',
                'name' => 'late_deduction_per_minute',
                'locked' => 0,
                'payload' => json_encode('0'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);
        
        echo "Late deduction settings created successfully!\n";
        echo "- enable_late_deduction: false\n";
        echo "- late_grace_minutes: 0\n";
        echo "- late_deduction_per_minute: 0\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}



