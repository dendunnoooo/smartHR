<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Leave Token Synchronization:\n";
echo "=====================================\n\n";

$employees = App\Models\User::where('type', App\Enums\UserType::EMPLOYEE)->get();

foreach ($employees as $employee) {
    $leaveToken = $employee->leaveToken;
    
    echo "Employee: {$employee->fullname}\n";
    echo "  User.leave_tokens: " . ($employee->leave_tokens ?? 0) . "\n";
    
    if ($leaveToken) {
        echo "  LeaveToken.tokens: {$leaveToken->tokens}\n";
        echo "  Earned: {$leaveToken->earned_tokens}, Used: {$leaveToken->used_tokens}\n";
        
        if ($employee->leave_tokens != $leaveToken->tokens) {
            echo "  ⚠ MISMATCH - Syncing now...\n";
            $employee->leave_tokens = $leaveToken->tokens;
            $employee->save();
            echo "  ✓ Synced!\n";
        } else {
            echo "  ✓ Synced\n";
        }
    } else {
        echo "  No LeaveToken record\n";
    }
    echo "\n";
}

echo "All leave tokens checked and synced!\n";
