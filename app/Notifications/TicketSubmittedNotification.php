<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Crypt;

class TicketSubmittedNotification extends Notification
{
    use Queueable;

    public $ticket;

    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $t = $this->ticket;
        return (new MailMessage)
            ->subject(__('New ticket submitted :id', ['id' => $t->tk_id]))
            ->line(__('A new ticket has been submitted.'))
            ->action(__('View Ticket'), route('tickets.show', ['ticket' => Crypt::encrypt($t->id)]));
    }

    public function toArray($notifiable): array
    {
        $t = $this->ticket;
        return [
            'ticket_id' => $t->id,
            'tk_id' => $t->tk_id ?? null,
            'message' => __('A new ticket :id has been submitted', ['id' => $t->tk_id ?? '']),
            'url' => route('tickets.show', ['ticket' => Crypt::encrypt($t->id)]),
        ];
    }
}
