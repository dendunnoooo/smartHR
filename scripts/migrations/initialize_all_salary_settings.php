<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $timestamp = now();
    
    $settingsToAdd = [
        ['name' => 'enable_absent_deduction', 'value' => false],
        ['name' => 'enable_auto_payslip', 'value' => false],
        ['name' => 'enable_overtime', 'value' => false],
        ['name' => 'enable_undertime', 'value' => false],
        ['name' => 'absent_deduction_amount', 'value' => '500'],
        ['name' => 'absent_deduction_percent', 'value' => '0'],
        ['name' => 'absent_calculation_method', 'value' => 'calendar_days'],
        ['name' => 'auto_payslip_day', 'value' => '25'],
        ['name' => 'auto_payslip_type', 'value' => 'monthly'],
        ['name' => 'auto_payslip_send_email', 'value' => false],
        ['name' => 'overtime_threshold_hours', 'value' => '8'],
        ['name' => 'overtime_rate_multiplier', 'value' => '1.25'],
        ['name' => 'overtime_calculation_method', 'value' => 'calendar_days'],
        ['name' => 'default_overtime_hours', 'value' => '2'],
        ['name' => 'undertime_threshold_hours', 'value' => '8'],
        ['name' => 'undertime_rate_multiplier', 'value' => '1'],
        ['name' => 'undertime_calculation_method', 'value' => 'calendar_days'],
        ['name' => 'default_undertime_hours', 'value' => '1'],
    ];
    
    foreach ($settingsToAdd as $setting) {
        $exists = DB::table('settings')
            ->where('group', 'general_salary')
            ->where('name', $setting['name'])
            ->exists();
        
        if (!$exists) {
            DB::table('settings')->insert([
                'group' => 'general_salary',
                'name' => $setting['name'],
                'locked' => 0,
                'payload' => json_encode($setting['value']),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
            echo "✓ Added: {$setting['name']}\n";
        } else {
            echo "- Exists: {$setting['name']}\n";
        }
    }
    
    echo "\nAll settings initialized!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
