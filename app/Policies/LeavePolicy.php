<?php

namespace App\Policies;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Enums\UserType;

class LeavePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the leave.
     */
    public function view(User $user, Leave $leave): bool
    {
        // Allow owners, admins or super admins to view
        if ($user->id === $leave->user_id) {
            return true;
        }
        if (method_exists($user, 'hasRole') && ($user->hasRole('Admin') || $user->hasRole('Super Admin'))) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can cancel the leave.
     */
    public function cancel(User $user, Leave $leave): bool
    {
        // Allow owner to cancel if it's pending, or admins to cancel any
        if (method_exists($user, 'hasRole') && ($user->hasRole('Admin') || $user->hasRole('Super Admin'))) {
            return true;
        }
        // also allow enum-based super admin
        if (isset($user->type) && $user->type === UserType::SUPERADMIN) {
            return true;
        }
        if ($user->id === $leave->user_id && $leave->status === 'pending') {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can approve the leave.
     */
    public function approve(User $user, Leave $leave): bool
    {
        // Only admins or super admins can approve
        if (method_exists($user, 'hasRole') && ($user->hasRole('Admin') || $user->hasRole('Super Admin'))) {
            return true;
        }
        if (isset($user->type) && $user->type === UserType::SUPERADMIN) {
            return true;
        }
        return false;
    }

}
