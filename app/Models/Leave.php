<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    protected $fillable = [
        'user_id', 'leave_type_id', 'start_date', 'end_date', 'reason', 'status', 'date_filed', 'total_days', 'day_type'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'date_filed' => 'date',
        'total_days' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function attachments()
    {
        return $this->hasMany(LeaveAttachment::class);
    }
}
