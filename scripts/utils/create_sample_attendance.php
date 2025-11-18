<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceTimestamp;
use Carbon\Carbon;

// Get the current logged-in user (or first employee)
$user = User::where('type', \App\Enums\UserType::EMPLOYEE)->first();

if (!$user) {
    echo "No employee found!\n";
    exit(1);
}

if (!$user->schedule) {
    echo "User has no schedule assigned!\n";
    exit(1);
}

echo "Creating sample attendance for: {$user->firstname} {$user->lastname}\n";
echo "Schedule: {$user->schedule->name} ({$user->schedule->time_range})\n\n";

$schedule = $user->schedule;
$tz = 'UTC';

// Parse schedule times
$scheduleStart = Carbon::parse($schedule->start_time);
$scheduleEnd = Carbon::parse($schedule->end_time);

// Get schedule working days
$workingDays = is_array($schedule->days) ? $schedule->days : json_decode($schedule->days, true);
$workingDaysMap = [
    'Monday' => 1,
    'Tuesday' => 2,
    'Wednesday' => 3,
    'Thursday' => 4,
    'Friday' => 5,
    'Saturday' => 6,
    'Sunday' => 0,
];

// Create attendance for the past 14 days (only on working days)
for ($i = 13; $i >= 0; $i--) {
    $date = Carbon::now($tz)->subDays($i);
    $dayName = $date->format('l'); // Monday, Tuesday, etc.
    
    // Skip if not a working day
    if (!in_array($dayName, $workingDays)) {
        continue;
    }
    
    // Randomize clock-in status
    $randomStatus = rand(1, 10);
    
    if ($randomStatus <= 6) {
        // 60% - On time (within 15 min grace period)
        $clockInMinutes = rand(-10, 5); // -10 to +5 minutes
        $isEarly = false;
        $isLate = false;
    } elseif ($randomStatus <= 8) {
        // 20% - Late (5-30 minutes)
        $clockInMinutes = rand(-30, -6);
        $isEarly = false;
        $isLate = true;
    } else {
        // 20% - Early (more than 15 minutes)
        $clockInMinutes = rand(16, 45);
        $isEarly = true;
        $isLate = false;
    }
    
    $actualClockIn = $date->copy()->setTime($scheduleStart->hour, $scheduleStart->minute, 0)->addMinutes($clockInMinutes);
    
    // Add some variation to clock-out time (-10 to +20 minutes from schedule end)
    $clockOutMinutes = rand(-10, 20);
    $actualClockOut = $date->copy()->setTime($scheduleEnd->hour, $scheduleEnd->minute, 0)->addMinutes($clockOutMinutes);
    
    // Create attendance record
    $attendance = Attendance::create([
        'user_id' => $user->id,
        'startDate' => $actualClockIn,
        'endDate' => $actualClockOut,
    ]);
    
    // Simulate lunch break (split into 2 timestamps)
    $morningEnd = $date->copy()->setTime(12, 0, 0);
    $afternoonStart = $date->copy()->setTime(13, 0, 0);
    
    // Morning session
    AttendanceTimestamp::create([
        'user_id' => $user->id,
        'attendance_id' => $attendance->id,
        'startTime' => $actualClockIn,
        'endTime' => $morningEnd,
        'location' => 'Office',
        'ip' => '127.0.0.1',
        'is_early' => $isEarly,
        'is_late' => $isLate,
        'minutes_difference' => $clockInMinutes,
        'scheduled_start_time' => $scheduleStart->format('H:i:s'),
        'scheduled_end_time' => $scheduleEnd->format('H:i:s'),
    ]);
    
    // Afternoon session
    AttendanceTimestamp::create([
        'user_id' => $user->id,
        'attendance_id' => $attendance->id,
        'startTime' => $afternoonStart,
        'endTime' => $actualClockOut,
        'location' => 'Office',
        'ip' => '127.0.0.1',
        'is_early' => false,
        'is_late' => false,
        'minutes_difference' => 0,
        'scheduled_start_time' => $scheduleStart->format('H:i:s'),
        'scheduled_end_time' => $scheduleEnd->format('H:i:s'),
    ]);
    
    $statusText = $isEarly ? 'Early' : ($isLate ? 'Late' : 'On Time');
    $timeText = $actualClockIn->format('g:i A') . ' - ' . $actualClockOut->format('g:i A');
    echo "✓ {$date->format('M d, Y (D)')}: {$timeText} - {$statusText}\n";
}

echo "\n✅ Sample attendance data created successfully for {$user->firstname}!\n";
echo "All attendance records are synced with the schedule: {$schedule->name}\n";
echo "Working days: " . implode(', ', $workingDays) . "\n";

