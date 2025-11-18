<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Attendance;
use App\Models\MonthlyToken;
use App\Enums\UserType;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GrantMonthlyTokens extends Command
{
    protected $signature = 'monthly-tokens:grant {--month=last : Which month to check (last, current)}';

    protected $description = 'Grant monthly tokens to employees with perfect monthly attendance';

    public function handle()
    {
        $this->info('Checking monthly attendance for token eligibility...');
        
        $monthOption = $this->option('month');
        if ($monthOption === 'current') {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
        } else {
            $startOfMonth = Carbon::now()->subMonth()->startOfMonth();
            $endOfMonth = Carbon::now()->subMonth()->endOfMonth();
        }
        
        $this->line("Checking month: {$startOfMonth->format('M Y')}");
        
        // Calculate expected working days (Mon-Fri only)
        $expectedDays = 0;
        $current = $startOfMonth->copy();
        while ($current->lte($endOfMonth)) {
            if ($current->isWeekday()) {
                $expectedDays++;
            }
            $current->addDay();
        }
        
        $this->line("Expected working days: {$expectedDays}");
        
        $employees = User::where('is_active', true)
            ->where('type', UserType::EMPLOYEE)
            ->get();
        
        $grantedCount = 0;
        $skippedCount = 0;
        
        foreach ($employees as $employee) {
            $monthlyToken = MonthlyToken::firstOrCreate(
                ['user_id' => $employee->id],
                [
                    'tokens' => 0,
                    'earned_tokens' => 0,
                    'converted_tokens' => 0,
                ]
            );
            
            // Check if token already granted for this month
            if ($monthlyToken->last_granted_month && 
                Carbon::parse($monthlyToken->last_granted_month)->isSameMonth($startOfMonth)) {
                $skippedCount++;
                continue;
            }
            
            // Count unique attendance days for the month (weekdays only)
            $attendanceDays = Attendance::where('user_id', $employee->id)
                ->whereBetween('startDate', [$startOfMonth, $endOfMonth])
                ->get()
                ->filter(function($attendance) {
                    return Carbon::parse($attendance->startDate)->isWeekday();
                })
                ->count();
            
            // Grant token if perfect attendance
            if ($attendanceDays >= $expectedDays) {
                $monthlyToken->addTokens(1);
                $monthlyToken->last_granted_month = $startOfMonth;
                $monthlyToken->save();
                
                $grantedCount++;
                $this->line("✓ Granted token to {$employee->name} (Total: {$monthlyToken->tokens})");
            } else {
                $this->line("  Skipped {$employee->name} ({$attendanceDays}/{$expectedDays} days)");
                $skippedCount++;
            }
        }
        
        $this->info("\nSummary:");
        $this->info("  Tokens Granted: {$grantedCount}");
        $this->info("  Employees Skipped: {$skippedCount}");
        $this->info("  Total Employees Checked: {$employees->count()}");
        
        return Command::SUCCESS;
    }
}
