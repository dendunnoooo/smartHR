<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttendanceTimestamp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','attendance_id','project_id','startTime','endTime','location',
        'billable','ip','note','is_early','is_late','minutes_difference',
        'scheduled_start_time','scheduled_end_time'
    ];

    protected $casts = [
        'startTime' => 'datetime:H:i:s',
        'endTime' => 'datetime:H:i:s',
        'is_early' => 'boolean',
        'is_late' => 'boolean',
        'scheduled_start_time' => 'datetime:H:i:s',
        'scheduled_end_time' => 'datetime:H:i:s',
    ];

    public function getTotalHoursAttribute()
    {
        if (!empty($this->endTime)) {
            $diff = $this->endTime->diff($this->startTime);
            return sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
        }
        $diff = now()->diff($this->startTime);
        return sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
    }

    public function getTotalHoursNumericAttribute()
    {
        if (!empty($this->endTime)) {
            return $this->startTime->diffInHours($this->endTime, true);
        }
        return $this->startTime->diffInHours(now(), true);
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    public function project(){
        return $this->belongsTo(\Modules\Project\Models\Project::class, 'project_id');
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->is_early) {
            return '<span class="badge bg-info">Early</span>';
        } elseif ($this->is_late) {
            return '<span class="badge bg-warning">Late</span>';
        }
        return '<span class="badge bg-success">On Time</span>';
    }

    public function getTimeDifferenceTextAttribute()
    {
        if ($this->minutes_difference === null) {
            return null;
        }
        
        $abs = abs($this->minutes_difference);
        if ($this->is_early) {
            return $abs . ' min early';
        } elseif ($this->is_late) {
            return $abs . ' min late';
        }
        return 'On time';
    }
}
