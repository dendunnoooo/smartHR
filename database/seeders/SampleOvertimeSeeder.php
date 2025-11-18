<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SampleOvertimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Adding sample overtime hours to recent attendance records...');
        
        // Get attendance records from the current month
        $attendances = Attendance::whereMonth('startDate', now()->month)
            ->whereYear('startDate', now()->year)
            ->get();
        
        if($attendances->isEmpty()){
            $this->command->warn('No attendance records found for this month. Creating sample data...');
            
            // Get all active employees
            $employees = \App\Models\User::where('is_active', true)
                ->where('type', \App\Enums\UserType::EMPLOYEE)
                ->limit(5)
                ->get();
            
            foreach($employees as $employee){
                // Create 5 days of attendance with varying overtime
                for($i = 1; $i <= 5; $i++){
                    $date = now()->startOfMonth()->addDays($i - 1);
                    $overtimeHours = rand(0, 4); // 0 to 4 hours overtime
                    
                    Attendance::create([
                        'user_id' => $employee->id,
                        'startDate' => $date,
                        'endDate' => $date,
                        'overtime_hours' => $overtimeHours,
                    ]);
                    
                    if($overtimeHours > 0){
                        $this->command->line("  Added {$overtimeHours}h OT for {$employee->name} on {$date->format('M d')}");
                    }
                }
            }
        } else {
            // Update existing attendance records with random overtime hours
            $updatedCount = 0;
            foreach($attendances as $attendance){
                // Random overtime: 30% chance of having 1-3 hours overtime
                $hasOvertime = rand(1, 10) <= 3;
                
                if($hasOvertime){
                    $overtimeHours = rand(1, 3) + (rand(0, 1) * 0.5); // 1.0, 1.5, 2.0, 2.5, 3.0, 3.5
                    $attendance->update(['overtime_hours' => $overtimeHours]);
                    $updatedCount++;
                }
            }
            
            $this->command->info("Updated {$updatedCount} attendance records with overtime hours.");
        }
        
        // Display summary
        $totalOvertimeHours = Attendance::whereMonth('startDate', now()->month)
            ->whereYear('startDate', now()->year)
            ->sum('overtime_hours');
        
        $recordsWithOvertime = Attendance::whereMonth('startDate', now()->month)
            ->whereYear('startDate', now()->year)
            ->where('overtime_hours', '>', 0)
            ->count();
        
        $this->command->info("Summary:");
        $this->command->info("  Total OT Hours: {$totalOvertimeHours}");
        $this->command->info("  Records with OT: {$recordsWithOvertime}");
        $this->command->info("  Sample overtime data created successfully!");
    }
}
