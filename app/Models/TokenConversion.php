<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tokens_converted',
        'conversion_type',
        'cash_amount',
        'leave_credits_added',
        'status',
        'notes',
        'converted_at',
        'approved_by',
        'approved_at',
        'included_in_payroll',
        'payroll_date',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
        'approved_at' => 'datetime',
        'payroll_date' => 'datetime',
        'included_in_payroll' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
