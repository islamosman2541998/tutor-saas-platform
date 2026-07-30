<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إيصال {{ $payment->receipt_number }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 py-10 font-sans text-slate-900 antialiased">
    <div class="mx-auto max-w-xl">
        <div class="no-print mb-4 flex justify-end">
            <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                طباعة الإيصال
            </button>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="mb-6 flex items-start justify-between border-b border-dashed border-slate-200 pb-6">
                <div>
                    <p class="text-lg font-bold text-slate-900">{{ $payment->tenant->teacher_name ?? $payment->tenant->name }}</p>
                    @if ($payment->tenant->phone)
                        <p class="text-sm text-slate-500">{{ $payment->tenant->phone }}</p>
                    @endif
                </div>
                <div class="text-left">
                    <p class="text-xs font-medium text-slate-400">رقم الإيصال</p>
                    <p class="text-lg font-bold text-indigo-600">{{ $payment->receipt_number }}</p>
                </div>
            </div>

            @if ($payment->status === 'cancelled')
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-center text-sm font-semibold text-red-700">
                    هذه الدفعة مُلغاة
                </div>
            @endif

            <dl class="grid grid-cols-2 gap-y-4 text-sm">
                <dt class="text-slate-500">الطالب</dt>
                <dd class="text-left font-medium text-slate-900">{{ $payment->student->name }} ({{ $payment->student->student_code }})</dd>

                <dt class="text-slate-500">عن استحقاق</dt>
                <dd class="text-left font-medium text-slate-900">
                    @if ($payment->monthlyDue)
                        {{ \App\Models\MonthlyDue::monthLabel($payment->monthlyDue->billing_month) }} {{ $payment->monthlyDue->billing_year }}
                    @else
                        رصيد مقدَّم
                    @endif
                </dd>

                <dt class="text-slate-500">المبلغ</dt>
                <dd class="text-left text-lg font-bold text-emerald-600">{{ number_format((float) $payment->amount, 2) }}</dd>

                <dt class="text-slate-500">طريقة الدفع</dt>
                <dd class="text-left font-medium text-slate-900">{{ $payment->methodLabel() }}</dd>

                @if ($payment->reference_number)
                    <dt class="text-slate-500">رقم مرجعي</dt>
                    <dd class="text-left font-medium text-slate-900">{{ $payment->reference_number }}</dd>
                @endif

                <dt class="text-slate-500">تاريخ الدفع</dt>
                <dd class="text-left font-medium text-slate-900">{{ $payment->paid_at->format('Y-m-d H:i') }}</dd>

                @if ($payment->notes)
                    <dt class="text-slate-500">ملاحظات</dt>
                    <dd class="text-left font-medium text-slate-900">{{ $payment->notes }}</dd>
                @endif
            </dl>

            <p class="mt-8 text-center text-xs text-slate-400">تم إصدار هذا الإيصال إلكترونيًا ولا يحتاج توقيعًا.</p>
        </div>
    </div>
</body>
</html>
