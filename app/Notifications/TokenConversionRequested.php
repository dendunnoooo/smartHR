<?php

namespace App\Notifications;

use App\Models\TokenConversion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TokenConversionRequested extends Notification
{
    use Queueable;

    protected $conversion;

    public function __construct(TokenConversion $conversion)
    {
        $this->conversion = $conversion;
    }

    public function via(object $notifiable): array
    {
        // Use database notifications only to avoid mail server connection issues
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->conversion->conversion_type === 'cash' ? 'Cash' : 'Leave Credits';
        $value = $this->conversion->conversion_type === 'cash' 
            ? '₱' . number_format($this->conversion->cash_amount, 2)
            : $this->conversion->leave_credits_added . ' credits';

        return (new MailMessage)
            ->subject('Token Conversion Request - ' . $this->conversion->user->fullname)
            ->greeting('Hello ' . $notifiable->firstname . ',')
            ->line($this->conversion->user->fullname . ' has requested to convert monthly attendance tokens.')
            ->line('**Conversion Details:**')
            ->line('- Tokens: ' . $this->conversion->tokens_converted)
            ->line('- Type: ' . $type)
            ->line('- Value: ' . $value)
            ->action('Review Request', url('/token-conversions'))
            ->line('Please review and approve/reject this request.');
    }

    public function toArray(object $notifiable): array
    {
        $type = $this->conversion->conversion_type === 'cash' ? 'Cash' : 'Leave Credits';
        $value = $this->conversion->conversion_type === 'cash' 
            ? '₱' . number_format($this->conversion->cash_amount, 2)
            : $this->conversion->leave_credits_added . ' credits';

        return [
            'message' => $this->conversion->user->fullname . ' requested token conversion (' . $type . ': ' . $value . ')',
            'conversion_id' => $this->conversion->id,
            'user_id' => $this->conversion->user_id,
            'user_name' => $this->conversion->user->fullname,
            'tokens' => $this->conversion->tokens_converted,
            'type' => $this->conversion->conversion_type,
            'amount' => $this->conversion->conversion_type === 'cash' 
                ? $this->conversion->cash_amount 
                : $this->conversion->leave_credits_added,
        ];
    }
}
