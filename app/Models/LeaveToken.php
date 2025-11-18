<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tokens',
        'earned_tokens',
        'used_tokens',
        'last_granted_week',
    ];

    protected $casts = [
        'last_granted_week' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Add tokens to the user's balance
     */
    public function addTokens(int $amount = 1)
    {
        $this->tokens += $amount;
        $this->earned_tokens += $amount;
        $this->save();
        
        // Sync with user's leave_tokens column
        if ($this->user) {
            $this->user->leave_tokens = $this->tokens;
            $this->user->save();
        }
    }

    /**
     * Use tokens from the user's balance
     */
    public function useTokens(int $amount = 1)
    {
        if ($this->tokens >= $amount) {
            $this->tokens -= $amount;
            $this->used_tokens += $amount;
            $this->save();
            
            // Sync with user's leave_tokens column
            if ($this->user) {
                $this->user->leave_tokens = $this->tokens;
                $this->user->save();
            }
            
            return true;
        }
        return false;
    }
}
