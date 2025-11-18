<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facades\Pdf;

class PayslipCreatedNotification extends Notification
{
    use Queueable;

    public $payslip;

    public function __construct($payslip)
    {
        $this->payslip = $payslip;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $p = $this->payslip;
        $mail = (new MailMessage)
            ->subject(__("You have received a payslip :id", ['id' => $p->ps_id ?? '']))
            ->line(__('A new payslip has been created for you.'))
            ->action(__('View Payslip'), route('payslips.show', ['payslip' => Crypt::encrypt($p->id)]));

        // Attempt to generate a PDF attachment of the payslip using barryvdh/laravel-dompdf.
        try {
            // Build view data consistent with PayrollsController@show
            $currency = function_exists('LocaleSettings') ? LocaleSettings('currency_symbol') : null;
            $employee = $p->employee ?? null;
            $allowances = method_exists($p, 'allowances') ? $p->allowances() : null;
            $deductions = method_exists($p, 'deductions') ? $p->deductions() : null;

            // Use the package facade if available
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.payroll.payslips.show', compact('p','employee','currency','allowances','deductions'))
                    ->setPaper('a4', 'portrait');
                $pdfData = $pdf->output();
                $filename = ($p->ps_id ?? 'payslip').'.pdf';
                $mail->attachData($pdfData, $filename, ['mime' => 'application/pdf']);
            }
        } catch (\Throwable $e) {
            // If PDF generation fails, swallow the error but log it — don't prevent the mail from sending
            logger()->error('Failed to generate payslip PDF for payslip id '.$p->id.': '.$e->getMessage());
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification (stored in the database).
     */
    public function toArray($notifiable): array
    {
        $p = $this->payslip;
        return [
            'payslip_id' => $p->id,
            'ps_id' => $p->ps_id ?? null,
            'message' => __('You have received a payslip :id', ['id' => $p->ps_id ?? '']),
            'url' => route('payslips.show', ['payslip' => Crypt::encrypt($p->id)]),
        ];
    }
}
