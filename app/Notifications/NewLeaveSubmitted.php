<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewLeaveSubmitted extends Notification
{
    use Queueable;

    protected $leave;

    /**
     * Create a new notification instance.
     */
    public function __construct($leave)
    {
        $this->leave = $leave;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // store in database and also attempt mail if the user has email
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $userName = optional($this->leave->user)->firstname ?? __('Unknown');
        $start = $this->leave->start_date;
        $end = $this->leave->end_date;

        return (new MailMessage)
                    ->subject(__('New Leave Request: :name', ['name' => $userName]))
                    ->greeting(__('Hello :name', ['name' => optional($notifiable)->firstname ?? '']))
                    ->line(__('A new leave request has been submitted by :user.', ['user' => $userName]))
                    ->line(__('Period: :start — :end', ['start' => $start, 'end' => $end]))
                    ->action(__('View leave'), url(route('leaves.show', $this->leave->id)))
                    ->line(__('You can approve or reject this request from the Leaves section.'));
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toDatabase($notifiable)
    {
        return [
            'type' => 'leave_submitted',
            'leave_id' => $this->leave->id,
            'user_id' => $this->leave->user_id,
            'user_name' => optional($this->leave->user)->firstname . ' ' . optional($this->leave->user)->lastname,
            'start_date' => $this->leave->start_date,
            'end_date' => $this->leave->end_date,
            'total_days' => $this->leave->total_days,
            'message' => __('New leave request submitted'),
        ];
    }
}
