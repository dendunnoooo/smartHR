<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RotateSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedules:rotate {--force : Force rotation regardless of date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rotate employee schedules based on configured rotation settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $currentDay = $today->day;
        $force = $this->option('force');

        $this->info("Checking for schedule rotations on day: {$currentDay}");

        // Get schedules that have rotation configured
        $schedules = Schedule::whereNotNull('rotation_day')
            ->whereNotNull('next_schedule_id')
            ->where('is_active', true)
            ->get();

        if ($schedules->isEmpty()) {
            $this->info('No schedules with rotation configured.');
            return 0;
        }

        $rotatedCount = 0;

        foreach ($schedules as $schedule) {
            // Check if today is the rotation day or if force is enabled
            if (!$force && $schedule->rotation_day != $currentDay) {
                continue;
            }

            // Get users with this schedule
            $users = $schedule->users;

            if ($users->isEmpty()) {
                $this->info("Schedule '{$schedule->name}' has no assigned employees.");
                continue;
            }

            $nextSchedule = $schedule->nextSchedule;

            if (!$nextSchedule) {
                $this->error("Schedule '{$schedule->name}' has invalid next schedule.");
                continue;
            }

            // Rotate each user to the next schedule
            foreach ($users as $user) {
                $user->update(['schedule_id' => $nextSchedule->id]);
                $this->info("✓ Rotated {$user->firstname} {$user->lastname} from '{$schedule->name}' to '{$nextSchedule->name}'");
                $rotatedCount++;
            }
        }

        if ($rotatedCount > 0) {
            $this->info("\n✅ Successfully rotated {$rotatedCount} employee(s).");
        } else {
            $this->info('No employees rotated today.');
        }

        return 0;
    }
}
