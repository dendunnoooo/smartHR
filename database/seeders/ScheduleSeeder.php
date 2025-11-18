<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schedules = [
            [
                'name' => 'Day Shift',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'work_hours' => 8,
                'days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
                'description' => 'Standard day shift - 8:00 AM to 4:00 PM',
                'is_active' => true,
            ],
            [
                'name' => 'Mid Shift',
                'start_time' => '12:00:00',
                'end_time' => '20:00:00',
                'work_hours' => 8,
                'days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
                'description' => 'Mid shift - 12:00 PM to 8:00 PM',
                'is_active' => true,
            ],
            [
                'name' => 'Night Shift',
                'start_time' => '20:00:00',
                'end_time' => '04:00:00',
                'work_hours' => 8,
                'days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
                'description' => 'Night shift - 8:00 PM to 4:00 AM',
                'is_active' => true,
            ],
            [
                'name' => 'Morning Shift',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'work_hours' => 8,
                'days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
                'description' => 'Early morning shift - 6:00 AM to 2:00 PM',
                'is_active' => true,
            ],
            [
                'name' => 'Flexible Shift',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'work_hours' => 8,
                'days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
                'description' => 'Flexible working hours - 9:00 AM to 5:00 PM',
                'is_active' => true,
            ],
        ];

        foreach ($schedules as $schedule) {
            Schedule::create($schedule);
        }
    }
}
