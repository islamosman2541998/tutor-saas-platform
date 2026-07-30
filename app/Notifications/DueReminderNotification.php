<?php

namespace App\Notifications;

use App\Models\MonthlyDue;
use Illuminate\Notifications\Messages\MailMessage;

class DueReminderNotification extends TenantNotification
{
    public function __construct(protected MonthlyDue $due) {}

    public function type(): string
    {
        return 'due_reminder';
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $due = $this->due;
        $monthLabel = MonthlyDue::monthLabel($due->billing_month).' '.$due->billing_year;

        $message = (new MailMessage)
            ->subject($due->isOverdue() ? "تذكير: استحقاق متأخر — {$monthLabel}" : "تذكير باستحقاق قريب — {$monthLabel}")
            ->greeting("مرحبًا {$due->student->name}");

        if ($due->isOverdue()) {
            $message->line("يوجد مبلغ متأخر السداد قدره {$due->remaining_amount} عن شهر {$monthLabel}، وتاريخ استحقاقه كان {$due->due_date->format('Y-m-d')}.");
        } else {
            $message->line("يقترب موعد استحقاق دفعة شهر {$monthLabel} بمبلغ {$due->remaining_amount} بتاريخ {$due->due_date->format('Y-m-d')}.");
        }

        return $message->line('برجاء السداد في أقرب وقت ممكن.');
    }
}
