<?php

namespace App\Notifications;

use App\Models\TokenConversion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TokenConversionRejected extends Notification
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
            ->subject('Token Conversion Rejected')
            ->greeting('Hello ' . $notifiable->firstname . ',')
            ->line('Your token conversion request has been rejected.')
            ->line('**Conversion Details:**')
            ->line('- Tokens Converted: ' . $this->conversion->tokens_converted)
            ->line('- Type: ' . $type)
            ->line('- Value: ' . $value)
            ->line($this->conversion->notes ? 'Reason: ' . $this->conversion->notes : '')
            ->line('Your tokens have been refunded to your account.')
            ->action('View Details', url('/monthly-tokens'))
            ->line('If you have questions, please contact HR.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'conversion_id' => $this->conversion->id,
            'status' => 'rejected',
            'tokens' => $this->conversion->tokens_converted,
            'type' => $this->conversion->conversion_type,
            'amount' => $this->conversion->conversion_type === 'cash' 
                ? $this->conversion->cash_amount 
                : $this->conversion->leave_credits_added,
            'message' => 'Your token conversion request has been rejected.',
            'notes' => $this->conversion->notes,
        ];
    }
}
