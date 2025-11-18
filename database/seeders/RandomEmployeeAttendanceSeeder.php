<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Enums\UserType;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RandomEmployeeAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Generating random attendance records for employees...');
        
        // Get all active employees
        $employees = User::where('is_active', true)
            ->where('type', UserType::EMPLOYEE)
            ->get();
        
        if ($employees->isEmpty()) {
            $this->command->warn('No active employees found.');
            return;
        }
        
        $totalRecordsCreated = 0;
        
        foreach ($employees as $employee) {
            // Get employee's joining date or created_at
            $joiningDate = $employee->employeeDetail?->joining_date 
                ?? $employee->created_at;
            
            if (!$joiningDate) {
                $this->command->warn("Skipping {$employee->name} - no joining date");
                continue;
            }
            
            $startDate = Carbon::parse($joiningDate);
            $today = Carbon::today();
            
            // Clear existing attendance for this employee
            Attendance::where('user_id', $employee->id)->delete();
            
            $recordsCreated = 0;
            $currentWeekStart = $startDate->copy()->startOfWeek(); // Monday
            
            // Generate attendance week by week
            while ($currentWeekStart->lte($today)) {
                $weekEnd = $currentWeekStart->copy()->endOfWeek(); // Sunday
                
                // Random number of days present per week (5-7)
                $daysPresent = rand(5, 7);
                
                // Create array of all weekdays (Mon-Sun)
                $allDays = [];
                for ($i = 0; $i < 7; $i++) {
                    $day = $currentWeekStart->copy()->addDays($i);
                    if ($day->gte($startDate) && $day->lte($today)) {
                        $allDays[] = $day;
                    }
                }
                
                // Don't create more attendance than available days
                $daysPresent = min($daysPresent, count($allDays));
                
                // Randomly select which days the employee was present
                shuffle($allDays);
                $presentDays = array_slice($allDays, 0, $daysPresent);
                
                // Create attendance records for selected days
                foreach ($presentDays as $day) {
                    // Set working hours (8 AM - 5 PM with 1 hour lunch)
                    $startTime = $day->copy()->setTime(8, 0, 0);
                    $endTime = $day->copy()->setTime(17, 0, 0);
                    
                    // Randomly add overtime (20% chance of 1-3 hours OT)
                    $overtimeHours = 0;
                    if (rand(1, 100) <= 20) {
                        $overtimeHours = rand(1, 3);
                        $endTime->addHours($overtimeHours);
                    }
                    
                    // Randomly add undertime (15% chance of 0.5-2 hours undertime)
                    $undertimeHours = 0;
                    if (rand(1, 100) <= 15 && $overtimeHours == 0) {
                        $undertimeHours = rand(1, 4) * 0.5; // 0.5, 1, 1.5, or 2 hours
                        $endTime->subMinutes($undertimeHours * 60);
                    }
                    
                    Attendance::create([
                        'user_id' => $employee->id,
                        'startDate' => $startTime,
                        'endDate' => $endTime,
                        'overtime_hours' => $overtimeHours,
                        'undertime_hours' => $undertimeHours,
                    ]);
                    
                    $recordsCreated++;
                    $totalRecordsCreated++;
                }
                
                // Move to next week
                $currentWeekStart->addWeek();
            }
            
            $this->command->info("Created {$recordsCreated} attendance records for {$employee->name}");
        }
        
        $this->command->info("Total attendance records created: {$totalRecordsCreated}");
        $this->command->info("Done! Run 'php artisan leave-tokens:grant' to check for eligible credits.");
    }
}
