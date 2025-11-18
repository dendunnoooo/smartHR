<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceTimestamp;

// Get the first employee
$user = User::where('type', \App\Enums\UserType::EMPLOYEE)->first();

if (!$user) {
    echo "No employee found!\n";
    exit(1);
}

echo "Deleting attendance records for: {$user->firstname} {$user->lastname}\n";

// Delete timestamps first
$timestampCount = AttendanceTimestamp::where('user_id', $user->id)->count();
AttendanceTimestamp::where('user_id', $user->id)->delete();
echo "✓ Deleted {$timestampCount} attendance timestamps\n";

// Delete attendance records
$attendanceCount = Attendance::where('user_id', $user->id)->count();
Attendance::where('user_id', $user->id)->delete();
echo "✓ Deleted {$attendanceCount} attendance records\n";

echo "\n✅ All attendance data deleted for {$user->firstname}!\n";
