<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Attendance;
use Livewire\Attributes\Js;
use Livewire\Attributes\On;
use Illuminate\Support\Carbon;
use App\Models\AttendanceTimestamp;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;

class EmployeeAttendance extends Component
{
    public $forProject,$project, $clockedIn, $timeStarted;
    public $tz, $currentDateString, $clockHour, $clockMinute, $clockSecond;
    public $totalHours = 0;
    public $totalHoursFloat = 0;
    public $timeId = null;
    public $attendances, $todayActivity;
    
    public $totalHoursToday;
    public $totalHoursThisMonth;
    public $totalHoursThisWeek;
    public $totalOvertimeToday = 0;
    public $totalUndertimeToday = 0;
    public $hoursRemainingToday = 0;

    public function clockin()
    {
        try{

            $user  = Auth::user();
            $tz = LocaleSettings('timezone') ?? config('app.timezone');
            
        // compute today's UTC range based on localization timezone
        $start = Carbon::now($tz)->startOfDay()->setTimezone('UTC');
        $end = Carbon::now($tz)->endOfDay()->setTimezone('UTC');
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereBetween('created_at', [$start, $end])->first();
            if(!empty($todayAttendance)){
                $attendance = $todayAttendance;
            }else{
                // Create attendance with proper timezone handling
                $localNow = Carbon::now($tz);
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    // Store the date portion in user's timezone, then convert to UTC for storage
                    'startDate' => $localNow->copy()->startOfDay()->setTimezone('UTC'),
                    'endDate' => null,
                ]);
                // Override created_at to match the local date
                $attendance->created_at = $localNow->setTimezone('UTC');
                $attendance->save();
            }
            
            // Schedule validation
            $schedule = $user->schedule;
            $currentTime = Carbon::now($tz);
            $isEarly = false;
            $isLate = false;
            $minutesDifference = null;
            $scheduledStartTime = null;
            $scheduledEndTime = null;
            
            if ($schedule) {
                $scheduledStartTime = Carbon::parse($schedule->start_time, $tz);
                $scheduledEndTime = Carbon::parse($schedule->end_time, $tz);
                
                // Calculate difference in minutes
                $minutesDifference = $currentTime->diffInMinutes($scheduledStartTime, false);
                
                // Define grace period (15 minutes before schedule)
                $gracePeriodMinutes = 15;
                
                if ($minutesDifference > $gracePeriodMinutes) {
                    // Clocking in more than 15 minutes before schedule
                    $isEarly = true;
                } elseif ($minutesDifference < 0) {
                    // Clocking in after schedule start time
                    $isLate = true;
                }
            }
            
            AttendanceTimestamp::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'project_id' => null,
                // store startTime in UTC
                'startTime' => Carbon::now($tz)->setTimezone('UTC'),
                'endTime' => null,
                'location' => $user->employeeDetail->department->location ?? null,
                'billable' => false,
                'ip' => request()->ip() ?? null,
                'is_early' => $isEarly,
                'is_late' => $isLate,
                'minutes_difference' => $minutesDifference,
                'scheduled_start_time' => $scheduledStartTime ? $scheduledStartTime->format('H:i:s') : null,
                'scheduled_end_time' => $scheduledEndTime ? $scheduledEndTime->format('H:i:s') : null,
            ]);
            $this->dispatch('IsClockedIn');
            $this->dispatch('refreshAttendance');
            
            // Custom message based on clock-in status
            if ($isEarly) {
                $this->dispatch('Notification', __('Clocked in early. Your shift starts at ' . $scheduledStartTime->format('g:i A')));
            } elseif ($isLate) {
                $this->dispatch('Notification', __('Clocked in late. Your shift started at ' . $scheduledStartTime->format('g:i A')));
            } else {
                $this->dispatch('Notification', __('You have clocked in successfully'));
            }
            
        }catch(\Exception $e){
            $this->dispatch('Notification',__('Something went wrong'));
        }
    }

    public function clockout($timestampId)
    {
        try{
            $timestamp = AttendanceTimestamp::find(Crypt::decrypt($timestampId));
            $tz = LocaleSettings('timezone') ?? config('app.timezone');
            $user = Auth::user();
            
            // store endDate/endTime as UTC
            $timestamp->attendance->update([
                'endDate' => Carbon::now($tz)->setTimezone('UTC'),
            ]);
            $timestamp->update([
                'endTime' => Carbon::now($tz)->setTimezone('UTC'),
            ]);
            
            // Calculate overtime and undertime
            if ($user->schedule && $timestamp->attendance) {
                $attendance = $timestamp->attendance;
                $schedule = $user->schedule;
                
                // Get all timestamps for this attendance
                $allTimestamps = $attendance->timestamps()->whereNotNull('endTime')->get();
                
                // Calculate total hours worked
                $totalMinutesWorked = 0;
                foreach ($allTimestamps as $ts) {
                    $start = Carbon::parse($ts->startTime, 'UTC')->setTimezone($tz);
                    $end = Carbon::parse($ts->endTime, 'UTC')->setTimezone($tz);
                    $totalMinutesWorked += $start->diffInMinutes($end);
                }
                
                $hoursWorked = $totalMinutesWorked / 60;
                $scheduledHours = $schedule->work_hours;
                
                // Calculate overtime (hours worked beyond scheduled hours)
                $overtimeHours = 0;
                $undertimeHours = 0;
                
                if ($hoursWorked > $scheduledHours) {
                    $overtimeHours = $hoursWorked - $scheduledHours;
                } elseif ($hoursWorked < $scheduledHours) {
                    $undertimeHours = $scheduledHours - $hoursWorked;
                }
                
                // Update attendance record
                $attendance->update([
                    'overtime_hours' => round($overtimeHours, 2),
                    'undertime_hours' => round($undertimeHours, 2),
                ]);
            }
            
            $this->dispatch('IsClockedIn');
            $this->dispatch('refreshAttendance');
            $this->dispatch('Notification',__('You have clockout successfully'));
        }catch(\Exception $e){
            $this->dispatch('Notification',__('Something went wrong'));
        }
    }

   
    #[On('refreshAttendance')]
    public function getAttendance()
    {
        $userId = Auth::user()->id;
        $tz = LocaleSettings('timezone') ?? config('app.timezone');
        $attendancesQuery = AttendanceTimestamp::where('user_id', $userId)
                    ->whereNotNull('attendance_id');
        $this->attendances = $attendancesQuery->get()->map(function($item) use ($tz){
            if(!empty($item->startTime)) $item->startTime = Carbon::parse($item->startTime, 'UTC')->setTimezone($tz);
            if(!empty($item->endTime)) $item->endTime = Carbon::parse($item->endTime, 'UTC')->setTimezone($tz);
            if(!empty($item->created_at)) $item->created_at = Carbon::parse($item->created_at, 'UTC')->setTimezone($tz);
            return $item;
        });
        $start = Carbon::now($tz)->startOfDay()->setTimezone('UTC');
        $end = Carbon::now($tz)->endOfDay()->setTimezone('UTC');
        $this->todayActivity = $attendancesQuery->whereBetween('created_at', [$start, $end])->get()->map(function($item) use ($tz){
            if(!empty($item->startTime)) $item->startTime = Carbon::parse($item->startTime, 'UTC')->setTimezone($tz);
            if(!empty($item->endTime)) $item->endTime = Carbon::parse($item->endTime, 'UTC')->setTimezone($tz);
            if(!empty($item->created_at)) $item->created_at = Carbon::parse($item->created_at, 'UTC')->setTimezone($tz);
            return $item;
        });
        
    }

    #[On('fetchStatistics')]
    public function statistics()
    {
        $userId = Auth::user()->id;
        $tz = LocaleSettings('timezone') ?? config('app.timezone');
        $userAttendances = AttendanceTimestamp::where('user_id', $userId)
                        ->whereNotNull('attendance_id');
        $start = Carbon::now($tz)->startOfDay()->setTimezone('UTC');
        $end = Carbon::now($tz)->endOfDay()->setTimezone('UTC');
        $this->totalHoursToday = $userAttendances->whereBetween('created_at', [$start, $end])
                        ->get()
                        ->sum(function($item) {
                            return $item->total_hours_numeric;
                        });
        
        // Calculate overtime and undertime for today
        $user = Auth::user();
        $scheduledHours = $user->schedule ? $user->schedule->work_hours : 8;
        
        if ($this->totalHoursToday > $scheduledHours) {
            $this->totalOvertimeToday = round($this->totalHoursToday - $scheduledHours, 2);
            $this->totalUndertimeToday = 0;
            $this->hoursRemainingToday = 0;
        } elseif ($this->totalHoursToday < $scheduledHours) {
            $this->totalUndertimeToday = round($scheduledHours - $this->totalHoursToday, 2);
            $this->totalOvertimeToday = 0;
            $this->hoursRemainingToday = $this->totalUndertimeToday;
        } else {
            $this->totalOvertimeToday = 0;
            $this->totalUndertimeToday = 0;
            $this->hoursRemainingToday = 0;
        }
        $startMonth = Carbon::now($tz)->startOfMonth()->setTimezone('UTC');
        $endMonth = Carbon::now($tz)->endOfMonth()->setTimezone('UTC');
        $this->totalHoursThisMonth = $userAttendances->whereBetween('created_at', [$startMonth, $endMonth])
                        ->get()
                        ->sum(function($item) {
                            return $item->total_hours_numeric;
                        });
        $startWeek = Carbon::now($tz)->startOfWeek()->setTimezone('UTC');
        $endWeek = Carbon::now($tz)->endOfWeek()->setTimezone('UTC');
        $this->totalHoursThisWeek = $userAttendances
                        ->whereBetween('created_at', [$startWeek, $endWeek])
                        ->get()
                        ->sum(function($item) {
                            return $item->total_hours_numeric;
                        });
    }

    #[On('IsClockedIn')]
    public function getClockInData()
    {
        $user = Auth::user();
        $tz = LocaleSettings('timezone') ?? config('app.timezone');
        $start = Carbon::now($tz)->startOfDay()->setTimezone('UTC');
        $end = Carbon::now($tz)->endOfDay()->setTimezone('UTC');
        $todayClockin = Attendance::where('user_id', $user->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->first();
        if(!empty($todayClockin)){
            $latestClockin = $todayClockin->timestamps()->latest()->whereNull('endTime')->first() ?? null;
            if(!empty($latestClockin)){
                $this->clockedIn = true;
                $this->timeId = Crypt::encrypt($latestClockin->id);
                // set start time and compute total hours in localization timezone
                $this->timeStarted = Carbon::parse($latestClockin->startTime, 'UTC')->setTimezone($tz)->toDateTimeString();
                $now = Carbon::now($tz);
                $startTime = Carbon::parse($latestClockin->startTime, 'UTC')->setTimezone($tz);
                // compute fractional hours (can be fractional minutes); ensure non-negative
                $seconds = $now->diffInRealSeconds($startTime);
                $hoursFloat = $seconds / 3600;
                $this->totalHoursFloat = $hoursFloat > 0 ? round($hoursFloat, 3) : 0;
                $this->totalHours = (int) floor($this->totalHoursFloat);
            }
        }
    }
    
    public function mount()
    {
        $this->tz = LocaleSettings('timezone') ?? config('app.timezone');
        $now = Carbon::now($this->tz);
        $this->currentDateString = $now->toDateString();
        $this->clockHour = (int) $now->format('H');
        $this->clockMinute = (int) $now->format('i');
        $this->clockSecond = (int) $now->format('s');
    }
   
    public function render()
    {
        return view('livewire.employee-attendance', [
            'totalHoursThisWeek' => $this->totalHoursThisWeek,
        ]);
    }
    
}
