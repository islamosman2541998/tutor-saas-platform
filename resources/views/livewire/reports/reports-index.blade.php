<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">التقارير</h1>
        <p class="text-sm text-slate-500">صدّر بياناتك كملف Excel — التقارير الكبيرة تُعَدّ في الخلفية ويصلك رابطها بالبريد</p>
    </div>

    <div class="mb-6 flex flex-wrap gap-2 border-b border-slate-200">
        <button
            wire:click="$set('activeTab', 'students')"
            class="border-b-2 px-4 py-2.5 text-sm font-medium {{ $activeTab === 'students' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}"
        >
            الطلاب
        </button>
        <button
            wire:click="$set('activeTab', 'payments')"
            class="border-b-2 px-4 py-2.5 text-sm font-medium {{ $activeTab === 'payments' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}"
        >
            الدفعات
        </button>
        <button
            wire:click="$set('activeTab', 'monthly_dues')"
            class="border-b-2 px-4 py-2.5 text-sm font-medium {{ $activeTab === 'monthly_dues' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}"
        >
            المستحقات الشهرية
        </button>
    </div>

    @if ($activeTab === 'students')
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="mb-4 flex flex-wrap gap-2">
                <input
                    type="text"
                    wire:model.live.debounce.400ms="studentsSearch"
                    placeholder="ابحث بالاسم أو الكود..."
                    class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                >
                <select wire:model.live="studentsStatus" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">كل الحالات</option>
                    <option value="active">نشط</option>
                    <option value="paused">موقوف</option>
                    <option value="graduated">متخرج</option>
                    <option value="withdrawn">منسحب</option>
                    <option value="archived">مؤرشف</option>
                </select>
            </div>

            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">عدد السجلات المطابقة: <strong class="text-slate-900">{{ $studentsCount }}</strong></p>
                <x-ui.button wire:click="exportStudents" class="w-auto px-4">تصدير Excel</x-ui.button>
            </div>
        </div>
    @endif

    @if ($activeTab === 'payments')
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="mb-4 flex flex-wrap gap-2">
                <select wire:model.live="paymentsStatus" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">كل الحالات</option>
                    <option value="completed">مكتملة</option>
                    <option value="cancelled">ملغاة</option>
                </select>
                <select wire:model.live="paymentsMethod" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">كل طرق الدفع</option>
                    <option value="cash">نقدًا</option>
                    <option value="bank_transfer">تحويل بنكي</option>
                    <option value="wallet">محفظة إلكترونية</option>
                    <option value="card">بطاقة</option>
                    <option value="other">أخرى</option>
                </select>
                <input type="date" wire:model.live="paymentsFrom" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <input type="date" wire:model.live="paymentsTo" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>

            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">عدد السجلات المطابقة: <strong class="text-slate-900">{{ $paymentsCount }}</strong></p>
                <x-ui.button wire:click="exportPayments" class="w-auto px-4">تصدير Excel</x-ui.button>
            </div>
        </div>
    @endif

    @if ($activeTab === 'monthly_dues')
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="mb-4 flex flex-wrap gap-2">
                <select wire:model.live="duesStatus" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">كل الحالات</option>
                    <option value="unpaid">غير مدفوع</option>
                    <option value="partially_paid">مدفوع جزئيًا</option>
                    <option value="paid">مدفوع</option>
                    <option value="waived">مُعفى</option>
                    <option value="cancelled">ملغي</option>
                </select>
                <select wire:model.live="duesGroupId" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">كل المجموعات</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">عدد السجلات المطابقة: <strong class="text-slate-900">{{ $duesCount }}</strong></p>
                <x-ui.button wire:click="exportMonthlyDues" class="w-auto px-4">تصدير Excel</x-ui.button>
            </div>
        </div>
    @endif
</div>
