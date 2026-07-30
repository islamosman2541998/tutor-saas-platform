<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class ReportExportReadyNotification extends TenantNotification
{
    public function __construct(protected string $reportLabel, protected string $signedUrl) {}

    public function type(): string
    {
        return 'report_export_ready';
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("تقرير \"{$this->reportLabel}\" جاهز للتحميل")
            ->greeting('تقريرك جاهز')
            ->line("تم إعداد تقرير \"{$this->reportLabel}\" الذي طلبته وهو جاهز الآن للتحميل.")
            ->action('تحميل التقرير', $this->signedUrl)
            ->line('الرابط صالح لمدة 24 ساعة.');
    }
}
