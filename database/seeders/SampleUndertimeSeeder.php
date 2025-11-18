<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SampleUndertimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Adding sample undertime hours to attendance records...');
        
        // Get attendance records from the current month
        $attendances = Attendance::whereMonth('startDate', now()->month)
            ->whereYear('startDate', now()->year)
            ->get();
        
        if($attendances->isEmpty()){
            $this->command->warn('No attendance records found for this month.');
            return;
        }
        
        // Update some attendance records with undertime hours
        $updatedCount = 0;
        foreach($attendances as $attendance){
            // Random undertime: 20% chance of having 0.5-2 hours undertime
            $hasUndertime = rand(1, 10) <= 2;
            
            if($hasUndertime){
                $undertimeHours = (rand(1, 4) * 0.5); // 0.5, 1.0, 1.5, 2.0
                $attendance->update(['undertime_hours' => $undertimeHours]);
                $updatedCount++;
                
                $user = $attendance->user;
                $userName = $user ? $user->name : 'Unknown';
                $this->command->line("  Added {$undertimeHours}h UT for {$userName} on {$attendance->startDate}");
            }
        }
        
        // Display summary
        $totalUndertimeHours = Attendance::whereMonth('startDate', now()->month)
            ->whereYear('startDate', now()->year)
            ->sum('undertime_hours');
        
        $recordsWithUndertime = Attendance::whereMonth('startDate', now()->month)
            ->whereYear('startDate', now()->year)
            ->where('undertime_hours', '>', 0)
            ->count();
        
        $this->command->info("Summary:");
        $this->command->info("  Updated: {$updatedCount} records");
        $this->command->info("  Total UT Hours: {$totalUndertimeHours}");
        $this->command->info("  Records with UT: {$recordsWithUndertime}");
        $this->command->info("  Sample undertime data created successfully!");
    }
}
