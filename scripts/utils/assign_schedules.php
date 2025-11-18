<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Schedule;
use App\Enums\UserType;

// Get all employees and schedules
$employees = User::where('type', UserType::EMPLOYEE)->get();
$schedules = Schedule::all();

if ($schedules->isEmpty()) {
    echo "No schedules found. Please run the seeder first.\n";
    exit;
}

echo "Found {$employees->count()} employees and {$schedules->count()} schedules\n\n";

// Assign schedules to employees in a round-robin fashion
$scheduleIndex = 0;
$updated = 0;

foreach ($employees as $employee) {
    $schedule = $schedules[$scheduleIndex % $schedules->count()];
    $employee->schedule_id = $schedule->id;
    $employee->save();
    
    echo "Assigned '{$schedule->name}' to {$employee->fullname}\n";
    
    $scheduleIndex++;
    $updated++;
}

echo "\n✓ Successfully assigned schedules to {$updated} employees!\n";
