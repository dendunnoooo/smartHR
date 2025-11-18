<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Ticket;
use App\Enums\UserType;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any tickets (not used for datatables filter).
     */
    public function viewAny(User $user)
    {
        return $user->hasRole('Super Admin');
    }

    /**
     * Determine whether the user can view the ticket.
     */
    public function view(User $user, Ticket $ticket)
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }
        // assigned user can view
        if (!is_null($ticket->user_id) && $ticket->user_id == $user->id) {
            return true;
        }
        // creator can view only when still unassigned
        if ($ticket->created_by == $user->id && is_null($ticket->user_id)) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create tickets.
     */
    public function create(User $user)
    {
        return $user->type === UserType::EMPLOYEE;
    }

    /**
     * Determine whether the user can update the ticket.
     */
    public function update(User $user, Ticket $ticket)
    {
        // superadmin can update
        if ($user->hasRole('Super Admin')) {
            return true;
        }
        // assigned user can update
        if (!is_null($ticket->user_id) && $ticket->user_id == $user->id) {
            return true;
        }
        // creator can update only when unassigned
        if ($ticket->created_by == $user->id && is_null($ticket->user_id)) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the ticket.
     */
    public function delete(User $user, Ticket $ticket)
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }
        // creator can delete only when unassigned
        if ($ticket->created_by == $user->id && is_null($ticket->user_id)) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can assign the ticket to someone.
     */
    public function assign(User $user, Ticket $ticket)
    {
        return $user->hasRole('Super Admin');
    }

    /**
     * Determine whether the user can close the ticket.
     */
    public function close(User $user, Ticket $ticket)
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }
        if (!is_null($ticket->user_id) && $ticket->user_id == $user->id) {
            return true;
        }
        return false;
    }
}
