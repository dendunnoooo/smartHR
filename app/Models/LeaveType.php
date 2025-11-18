<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'name', 'code', 'accrual_rate_per_month', 'requires_approval', 'max_days'
    ];

    protected $casts = [
        'accrual_rate_per_month' => 'float',
        'requires_approval' => 'boolean',
        'max_days' => 'integer',
    ];

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }
}
