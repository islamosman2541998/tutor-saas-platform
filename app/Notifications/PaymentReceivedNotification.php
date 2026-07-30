<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentReceivedNotification extends TenantNotification
{
    public function __construct(protected Payment $payment) {}

    public function type(): string
    {
        return 'payment_received';
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $payment = $this->payment;

        return (new MailMessage)
            ->subject('تأكيد استلام دفعة — إيصال '.$payment->receipt_number)
            ->greeting("مرحبًا {$payment->student->name}")
            ->line("تم استلام دفعة بمبلغ {$payment->amount} بتاريخ {$payment->paid_at->format('Y-m-d')}.")
            ->line("رقم الإيصال: {$payment->receipt_number}")
            ->line('شكرًا لالتزامكم بالمواعيد.');
    }
}
