<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceTimestamp;
use Illuminate\Support\Carbon;
use App\Enums\UserType;

// Get all employees
$employees = User::where('type', UserType::EMPLOYEE)->get();

if ($employees->isEmpty()) {
    echo "No employees found!\n";
    exit(1);
}

$tz = 'Asia/Manila';

// Define varied work patterns
$workPatterns = [
    ['hours' => 8, 'type' => 'exact'],      // Exactly 8 hours
    ['hours' => 6.5, 'type' => 'undertime'], // 1.5 hours undertime
    ['hours' => 7, 'type' => 'undertime'],   // 1 hour undertime
    ['hours' => 9, 'type' => 'overtime'],    // 1 hour overtime
    ['hours' => 9.5, 'type' => 'overtime'],  // 1.5 hours overtime
    ['hours' => 7.5, 'type' => 'undertime'], // 0.5 hour undertime
    ['hours' => 8, 'type' => 'exact'],       // Exactly 8 hours
    ['hours' => 10, 'type' => 'overtime'],   // 2 hours overtime
    ['hours' => 6, 'type' => 'undertime'],   // 2 hours undertime
    ['hours' => 8.5, 'type' => 'overtime'],  // 0.5 hour overtime
];

$daysInMonth = Carbon::create(2025, 11, 1)->daysInMonth;

foreach ($employees as $employee) {
    echo "\n--- Processing {$employee->fullname} ({$employee->email}) ---\n";
    
    $scheduledHours = $employee->schedule ? $employee->schedule->work_hours : 8;
    
    // Delete existing attendance for November
    Attendance::where('user_id', $employee->id)
        ->whereYear('startDate', 2025)
        ->whereMonth('startDate', 11)
        ->delete();
    
    $patternIndex = 0;
    $presentCount = 0;
    
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $currentDate = Carbon::create(2025, 11, $day, 0, 0, 0, $tz);
        
        // Skip future dates
        if ($currentDate->isFuture()) {
            continue;
        }
        
        // Skip some days to create absence pattern (skip day 3, 4, 9, 14, 15)
        if (in_array($day, [3, 4, 9, 14, 15])) {
            continue;
        }
        
        // Get pattern for this day
        $pattern = $workPatterns[$patternIndex % count($workPatterns)];
        $patternIndex++;
        
        // Set clock in time (8:00 AM)
        $clockInTime = $currentDate->copy()->setTime(8, 0, 0);
        
        // Calculate clock out time based on hours worked
        $hoursWorked = $pattern['hours'];
        $clockOutTime = $clockInTime->copy()->addHours(floor($hoursWorked))->addMinutes(($hoursWorked - floor($hoursWorked)) * 60);
        
        // Calculate overtime/undertime
        $overtimeHours = 0;
        $undertimeHours = 0;
        
        if ($hoursWorked > $scheduledHours) {
            $overtimeHours = round($hoursWorked - $scheduledHours, 2);
        } elseif ($hoursWorked < $scheduledHours) {
            $undertimeHours = round($scheduledHours - $hoursWorked, 2);
        }
        
        // Create attendance record
        $attendance = Attendance::create([
            'user_id' => $employee->id,
            'startDate' => $clockInTime->copy()->setTimezone('UTC'),
            'endDate' => $clockOutTime->copy()->setTimezone('UTC'),
            'overtime_hours' => $overtimeHours,
            'undertime_hours' => $undertimeHours,
            'created_at' => $clockInTime->copy()->setTimezone('UTC'),
            'updated_at' => $clockOutTime->copy()->setTimezone('UTC'),
        ]);
        
        // Create timestamp record
        AttendanceTimestamp::create([
            'user_id' => $employee->id,
            'attendance_id' => $attendance->id,
            'startTime' => $clockInTime->copy()->setTimezone('UTC'),
            'endTime' => $clockOutTime->copy()->setTimezone('UTC'),
            'location' => 'Office',
            'ip' => '127.0.0.1',
            'billable' => false,
            'is_early' => false,
            'is_late' => false,
            'minutes_difference' => 0,
            'scheduled_start_time' => '08:00:00',
            'scheduled_end_time' => '17:00:00',
            'created_at' => $clockInTime->copy()->setTimezone('UTC'),
            'updated_at' => $clockOutTime->copy()->setTimezone('UTC'),
        ]);
        
        $presentCount++;
    }
    
    echo "Created $presentCount days of attendance\n";
}

echo "\n=== COMPLETED ===\n";
echo "Varied attendance data created for all employees!\n";
echo "Present days have different patterns: undertime, overtime, and exact 8 hours.\n";
