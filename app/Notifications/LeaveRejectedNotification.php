<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LeaveRejectedNotification extends Notification
{
    use Queueable;

    protected $leave;
    protected $comment;

    public function __construct($leave, $comment = null)
    {
        $this->leave = $leave;
        $this->comment = $comment;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
                    ->subject(__('Your leave has been rejected'))
                    ->greeting(__('Hello :name', ['name' => optional($notifiable)->firstname ?? '']))
                    ->line(__('Your leave request from :start to :end has been rejected.', ['start' => $this->leave->start_date, 'end' => $this->leave->end_date]));

        if (!empty($this->comment)) {
            $mail->line(__('Comment from approver: :comment', ['comment' => $this->comment]));
        }

        $mail->action(__('View leave'), url(route('leaves.show', $this->leave->id)))
             ->line(__('If you have questions contact your administrator.'));

        return $mail;
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'leave_rejected',
            'leave_id' => $this->leave->id,
            'start_date' => $this->leave->start_date,
            'end_date' => $this->leave->end_date,
            'total_days' => $this->leave->total_days,
            'comment' => $this->comment,
            'message' => __('Your leave has been rejected'),
        ];
    }
}
