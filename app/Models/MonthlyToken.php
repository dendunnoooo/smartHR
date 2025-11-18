<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tokens',
        'earned_tokens',
        'converted_tokens',
        'last_granted_month',
    ];

    protected $casts = [
        'last_granted_month' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addTokens(int $amount = 1)
    {
        $this->tokens += $amount;
        $this->earned_tokens += $amount;
        $this->save();
    }

    public function convertTokens(int $amount, string $type)
    {
        if ($this->tokens >= $amount) {
            $this->tokens -= $amount;
            $this->converted_tokens += $amount;
            $this->save();
            return true;
        }
        return false;
    }
}
