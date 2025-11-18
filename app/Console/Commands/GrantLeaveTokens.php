<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveToken;
use App\Enums\UserType;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GrantLeaveTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave-tokens:grant {--week=last : Which week to check (last, current)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant leave credits to employees with perfect weekly attendance (5 consecutive days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking weekly attendance for leave credit eligibility...');
        
        // Determine which week to check
        $weekOption = $this->option('week');
        if ($weekOption === 'current') {
            $startOfWeek = Carbon::now()->startOfWeek(); // Monday
            $endOfWeek = Carbon::now()->endOfWeek()->subDays(2); // Friday
        } else {
            // Last week (default)
            $startOfWeek = Carbon::now()->subWeek()->startOfWeek();
            $endOfWeek = Carbon::now()->subWeek()->endOfWeek()->subDays(2); // Friday
        }
        
        $this->line("Checking week: {$startOfWeek->format('M d')} - {$endOfWeek->format('M d, Y')}");
        
        // Get all active employees
        $employees = User::where('is_active', true)
            ->where('type', UserType::EMPLOYEE)
            ->get();
        
        $grantedCount = 0;
        $skippedCount = 0;
        
        foreach ($employees as $employee) {
            // Get or create leave token record
            $leaveToken = LeaveToken::firstOrCreate(
                ['user_id' => $employee->id],
                [
                    'tokens' => 0,
                    'earned_tokens' => 0,
                    'used_tokens' => 0,
                ]
            );
            
            // Check if token already granted for this week
            if ($leaveToken->last_granted_week && 
                Carbon::parse($leaveToken->last_granted_week)->isSameWeek($startOfWeek)) {
                $skippedCount++;
                continue;
            }
            
            // Count attendance for Monday-Friday (5 days)
            $attendanceCount = Attendance::where('user_id', $employee->id)
                ->whereBetween('startDate', [$startOfWeek, $endOfWeek])
                ->count();
            
            // Grant token if perfect attendance (5 days)
            if ($attendanceCount >= 5) {
                $leaveToken->addTokens(1);
                $leaveToken->last_granted_week = $startOfWeek;
                $leaveToken->save();
                
                $grantedCount++;
                $this->line("✓ Granted credit to {$employee->name} (Total: {$leaveToken->tokens})");
            } else {
                $skippedCount++;
            }
        }
        
        $this->info("Summary:");
        $this->info("  Credits Granted: {$grantedCount}");
        $this->info("  Employees Skipped: {$skippedCount}");
        $this->info("  Total Employees Checked: {$employees->count()}");
        
        return Command::SUCCESS;
    }
}
