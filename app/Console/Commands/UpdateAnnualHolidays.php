<?php

namespace App\Console\Commands;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateAnnualHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:update-annual';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update annual holidays to repeat for the current year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating annual holidays...');
        
        $currentYear = Carbon::now()->year;
        $updatedCount = 0;
        
        // Get all annual holidays
        $annualHolidays = Holiday::where('is_annual', true)->get();
        
        foreach ($annualHolidays as $holiday) {
            $startDate = Carbon::parse($holiday->startDate);
            $endDate = Carbon::parse($holiday->endDate);
            
            // Check if the holiday is in a past year
            if ($startDate->year < $currentYear) {
                // Calculate the duration of the holiday
                $duration = $startDate->diffInDays($endDate);
                
                // Update to current year while maintaining month and day
                $newStartDate = Carbon::createFromDate($currentYear, $startDate->month, $startDate->day);
                $newEndDate = $newStartDate->copy()->addDays($duration);
                
                $holiday->update([
                    'startDate' => $newStartDate->format('Y-m-d'),
                    'endDate' => $newEndDate->format('Y-m-d'),
                ]);
                
                $updatedCount++;
                $this->line("✓ Updated: {$holiday->name} to {$newStartDate->format('M d, Y')}");
            }
        }
        
        if ($updatedCount > 0) {
            $this->info("Successfully updated {$updatedCount} annual holiday(s) to {$currentYear}.");
        } else {
            $this->info('No annual holidays needed updating.');
        }
        
        return Command::SUCCESS;
    }
}
