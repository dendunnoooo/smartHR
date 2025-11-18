<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'work_hours',
        'days',
        'description',
        'is_active',
        'rotation_day',
        'next_schedule_id'
    ];

    protected $casts = [
        'days' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get users with this schedule
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the next schedule for rotation
     */
    public function nextSchedule()
    {
        return $this->belongsTo(Schedule::class, 'next_schedule_id');
    }

    /**
     * Get formatted time range
     */
    public function getTimeRangeAttribute()
    {
        return date('g:i A', strtotime($this->start_time)) . ' - ' . date('g:i A', strtotime($this->end_time));
    }

    /**
     * Get working days as string
     */
    public function getWorkingDaysAttribute()
    {
        if (empty($this->days) || !is_array($this->days)) {
            return 'Monday - Friday';
        }
        return implode(', ', $this->days);
    }
}
