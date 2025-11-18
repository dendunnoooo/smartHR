<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $settings = app(\App\Settings\SalarySetting::class);
    
    echo "\n=== SALARY SETTINGS STATUS ===\n\n";
    echo "Allowances (COLA/HRA):     " . ($settings->enable_da_hra ? '✓ ON' : '✗ OFF') . "\n";
    echo "Provident Fund (SSS):      " . ($settings->enable_provident_fund ? '✓ ON' : '✗ OFF') . "\n";
    echo "ESI Fund (PhilHealth):     " . ($settings->enable_esi_fund ? '✓ ON' : '✗ OFF') . "\n";
    echo "Withholding Tax:           " . ($settings->enable_tax ? '✓ ON' : '✗ OFF') . "\n";
    echo "Absent Deduction:          " . ($settings->enable_absent_deduction ? '✓ ON' : '✗ OFF') . "\n";
    echo "Auto Payslip Generation:   " . ($settings->enable_auto_payslip ? '✓ ON' : '✗ OFF') . "\n";
    echo "Overtime Pay:              " . ($settings->enable_overtime ? '✓ ON' : '✗ OFF') . "\n";
    echo "Undertime Deduction:       " . ($settings->enable_undertime ? '✓ ON' : '✗ OFF') . "\n";
    echo "Late Deduction:            " . ($settings->enable_late_deduction ? '✓ ON' : '✗ OFF') . "\n";
    
    echo "\n=== CONFIGURATION VALUES ===\n\n";
    if ($settings->enable_da_hra) {
        echo "COLA: {$settings->da_percent}% | HRA: {$settings->hra_percent}%\n";
    }
    if ($settings->enable_overtime) {
        echo "Overtime Threshold: {$settings->overtime_threshold_hours} hrs | Multiplier: {$settings->overtime_rate_multiplier}x\n";
    }
    if ($settings->enable_undertime) {
        echo "Undertime Threshold: {$settings->undertime_threshold_hours} hrs | Multiplier: {$settings->undertime_rate_multiplier}x\n";
    }
    if ($settings->enable_late_deduction) {
        echo "Late Grace: {$settings->late_grace_minutes} min | Deduction/min: ₱{$settings->late_deduction_per_minute}\n";
    }
    if ($settings->enable_auto_payslip) {
        echo "Auto Payslip Day: {$settings->auto_payslip_day} | Type: {$settings->auto_payslip_type}\n";
    }
    
    echo "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
