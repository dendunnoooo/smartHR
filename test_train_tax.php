<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Helpers\TrainTaxCalculator;

echo "=== TRAIN TAX CALCULATOR TEST ===\n\n";

$testSalaries = [
    15000,  // Below threshold
    20000,  // Below threshold
    25000,  // First bracket
    30000,  // First bracket
    50000,  // Second bracket
    75000,  // Third bracket
    100000, // Third bracket
    200000, // Fourth bracket
    500000, // Fifth bracket
    1000000 // Sixth bracket
];

foreach ($testSalaries as $salary) {
    $info = TrainTaxCalculator::getTaxBracketInfo($salary);
    
    echo "Monthly Salary: ₱" . number_format($salary, 2) . "\n";
    echo "Annual Salary: ₱" . number_format($info['annual_salary'], 2) . "\n";
    echo "Tax Bracket: {$info['bracket']}\n";
    echo "Tax Rate: {$info['rate']}\n";
    echo "Annual Tax: ₱" . number_format($info['annual_tax'], 2) . "\n";
    echo "Monthly Withholding: ₱" . number_format($info['monthly_tax'], 2) . "\n";
    echo "Net Monthly (after tax): ₱" . number_format($salary - $info['monthly_tax'], 2) . "\n";
    echo str_repeat("-", 60) . "\n\n";
}

echo "=== TEST COMPLETED ===\n";
