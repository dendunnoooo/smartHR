<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveAttachment extends Model
{
    protected $fillable = ['leave_id', 'path', 'original_name'];

    public function leave()
    {
        return $this->belongsTo(Leave::class);
    }
}
