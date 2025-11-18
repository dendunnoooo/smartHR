<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\AttendanceTimestamp;
use Illuminate\Support\Carbon;

class NormalizeAttendanceTimestamps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * timezone: timezone to interpret existing timestamps as (default: LocaleSettings('timezone') or config app timezone)
     */
    protected $signature = 'attendance:normalize
                            {--tz= : Timezone to interpret existing timestamps as}
                            {--confirm : Actually perform writes (omit for dry-run)}
                            {--chunk=500 : Chunk size for processing}
    ';

    /**
     * The console command description.
     */
    protected $description = 'Normalize historical Attendance and AttendanceTimestamp date/time fields by interpreting them as a given timezone and converting to UTC. Dry-run by default; pass --confirm to perform writes.';

    public function handle()
    {
        $tz = $this->option('tz') ?: (function(){ try{ return LocaleSettings('timezone'); }catch(\Throwable $e){ return config('app.timezone'); }})();

        if(empty($tz)){
            $tz = config('app.timezone') ?: 'UTC';
        }

        $confirm = $this->option('confirm');
        $chunk = (int) $this->option('chunk');

        $this->info("Normalize attendance timestamps assuming timezone: {$tz}");
        if(!$confirm){
            $this->info('Dry-run mode: no database writes will be performed. Use --confirm to apply changes.');
        }

        // Process Attendance records
        $this->info('Processing Attendance records...');
        Attendance::chunk($chunk, function($attendances) use ($tz, $confirm){
            foreach($attendances as $attendance){
                $changed = false;
                // convert startDate and endDate if present
                if(!empty($attendance->startDate)){
                    // interpret stored value as $tz then convert to UTC
                    $asUtc = Carbon::parse($attendance->startDate, $tz)->setTimezone('UTC');
                    if(!$asUtc->equalTo(Carbon::parse($attendance->startDate))){
                        $this->line("Attendance#{$attendance->id} startDate -> {$attendance->startDate} => {$asUtc->toDateTimeString()} UTC");
                        if($confirm){
                            $attendance->startDate = $asUtc;
                            $changed = true;
                        }
                    }
                }
                if(!empty($attendance->endDate)){
                    $asUtc = Carbon::parse($attendance->endDate, $tz)->setTimezone('UTC');
                    if(!$asUtc->equalTo(Carbon::parse($attendance->endDate))){
                        $this->line("Attendance#{$attendance->id} endDate -> {$attendance->endDate} => {$asUtc->toDateTimeString()} UTC");
                        if($confirm){
                            $attendance->endDate = $asUtc;
                            $changed = true;
                        }
                    }
                }
                if($changed && $confirm){
                    $attendance->save();
                }
            }
        });

        // Process AttendanceTimestamp records
        $this->info('Processing AttendanceTimestamp records...');
        AttendanceTimestamp::chunk($chunk, function($rows) use ($tz, $confirm){
            foreach($rows as $row){
                $changed = false;
                if(!empty($row->startTime)){
                    $asUtc = Carbon::parse($row->startTime, $tz)->setTimezone('UTC');
                    if(!$asUtc->equalTo(Carbon::parse($row->startTime))){
                        $this->line("Timestamp#{$row->id} startTime -> {$row->startTime} => {$asUtc->toDateTimeString()} UTC");
                        if($confirm){
                            $row->startTime = $asUtc;
                            $changed = true;
                        }
                    }
                }
                if(!empty($row->endTime)){
                    $asUtc = Carbon::parse($row->endTime, $tz)->setTimezone('UTC');
                    if(!$asUtc->equalTo(Carbon::parse($row->endTime))){
                        $this->line("Timestamp#{$row->id} endTime -> {$row->endTime} => {$asUtc->toDateTimeString()} UTC");
                        if($confirm){
                            $row->endTime = $asUtc;
                            $changed = true;
                        }
                    }
                }
                if($changed && $confirm){
                    $row->save();
                }
            }
        });

        $this->info('Done.');
        return 0;
    }
}
