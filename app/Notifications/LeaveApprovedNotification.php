<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LeaveApprovedNotification extends Notification
{
    use Queueable;

    protected $leave;

    public function __construct($leave)
    {
        $this->leave = $leave;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject(__('Your leave has been approved'))
                    ->greeting(__('Hello :name', ['name' => optional($notifiable)->firstname ?? '']))
                    ->line(__('Your leave request from :start to :end has been approved.', ['start' => $this->leave->start_date, 'end' => $this->leave->end_date]))
                    ->action(__('View leave'), url(route('leaves.show', $this->leave->id)))
                    ->line(__('Thank you.'));
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'leave_approved',
            'leave_id' => $this->leave->id,
            'start_date' => $this->leave->start_date,
            'end_date' => $this->leave->end_date,
            'total_days' => $this->leave->total_days,
            'message' => __('Your leave has been approved'),
        ];
    }
}
