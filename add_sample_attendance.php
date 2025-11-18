<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceTimestamp;
use Illuminate\Support\Carbon;

$user = User::where('email', 'employee1@smarthr.com')->first();

if (!$user) {
    echo "Employee not found!\n";
    exit(1);
}

$tz = 'Asia/Manila';

// Delete existing attendance for Nov 17-18 to avoid duplicates
Attendance::where('user_id', $user->id)
    ->whereDate('startDate', '>=', Carbon::create(2025, 11, 17, 0, 0, 0, $tz)->setTimezone('UTC'))
    ->whereDate('startDate', '<=', Carbon::create(2025, 11, 18, 23, 59, 59, $tz)->setTimezone('UTC'))
    ->delete();

foreach([17, 18] as $day) {
    $date = Carbon::create(2025, 11, $day, 8, 0, 0, $tz);
    
    $attendance = Attendance::create([
        'user_id' => $user->id,
        'startDate' => $date->copy()->setTimezone('UTC'),
        'endDate' => $date->copy()->addHours(8)->setTimezone('UTC'),
        'overtime_hours' => 0,
        'undertime_hours' => 0,
        'created_at' => $date->copy()->setTimezone('UTC'),
        'updated_at' => $date->copy()->setTimezone('UTC'),
    ]);
    
    AttendanceTimestamp::create([
        'user_id' => $user->id,
        'attendance_id' => $attendance->id,
        'startTime' => $date->copy()->setTimezone('UTC'),
        'endTime' => $date->copy()->addHours(8)->setTimezone('UTC'),
        'location' => 'Office',
        'ip' => '127.0.0.1',
        'billable' => false,
        'is_early' => false,
        'is_late' => false,
        'minutes_difference' => 0,
        'scheduled_start_time' => '08:00:00',
        'scheduled_end_time' => '17:00:00',
        'created_at' => $date->copy()->setTimezone('UTC'),
        'updated_at' => $date->copy()->setTimezone('UTC'),
    ]);
    
    echo "Created attendance for November $day, 2025\n";
}

echo "Done! Sample attendance data created successfully.\n";
