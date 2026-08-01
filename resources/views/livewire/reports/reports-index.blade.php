<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">التقارير</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">صدّر بياناتك كملف Excel — التقارير الكبيرة تُعَدّ في الخلفية ويصلك رابطها بالبريد</p>
    </div>

    <div class="mb-6 flex flex-wrap gap-2 border-b border-slate-200 dark:border-slate-700">
        <button
            wire:click="$set('activeTab', 'students')"
            class="border-b-2 px-4 py-2.5 text-sm font-medium {{ $activeTab === 'students' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
        >
            الطلاب
        </button>
        <button
            wire:click="$set('activeTab', 'payments')"
            class="border-b-2 px-4 py-2.5 text-sm font-medium {{ $activeTab === 'payments' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
        >
            الدفعات
        </button>
        <button
            wire:click="$set('activeTab', 'monthly_dues')"
            class="border-b-2 px-4 py-2.5 text-sm font-medium {{ $activeTab === 'monthly_dues' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
        >
            المستحقات الشهرية
        </button>
        <button
            wire:click="$set('activeTab', 'users')"
            class="border-b-2 px-4 py-2.5 text-sm font-medium {{ $activeTab === 'users' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
        >
            المستخدمين
        </button>
        <button
            wire:click="$set('activeTab', 'registration_requests')"
            class="border-b-2 px-4 py-2.5 text-sm font-medium {{ $activeTab === 'registration_requests' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
        >
            طلبات التسجيل
        </button>
        <button
            wire:click="$set('activeTab', 'class_sessions')"
            class="border-b-2 px-4 py-2.5 text-sm font-medium {{ $activeTab === 'class_sessions' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
        >
            الحصص الدراسية
        </button>
    </div>

    @if ($activeTab === 'students')
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
            <div class="mb-4 flex flex-wrap gap-2">
                <input
                    type="text"
                    wire:model.live.debounce.400ms="studentsSearch"
                    placeholder="ابحث بالاسم أو الكود..."
                    class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                >
                <select wire:model.live="studentsStatus" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">كل الحالات</option>
                    <option value="active">نشط</option>
                    <option value="paused">موقوف</option>
                    <option value="graduated">متخرج</option>
                    <option value="withdrawn">منسحب</option>
                    <option value="archived">مؤرشف</option>
                </select>
            </div>

            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">عدد السجلات المطابقة: <strong class="text-slate-900 dark:text-slate-100">{{ $studentsCount }}</strong></p>
                <x-ui.button wire:click="exportStudents" class="w-auto px-4">تصدير Excel</x-ui.button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full min-w-[760px] text-right text-sm">
                    <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">كود الطالب</th>
                            <th class="px-4 py-3 font-medium">الاسم</th>
                            <th class="px-4 py-3 font-medium">الهاتف</th>
                            <th class="px-4 py-3 font-medium">ولي الأمر</th>
                            <th class="px-4 py-3 font-medium">هاتف ولي الأمر</th>
                            <th class="px-4 py-3 font-medium">الحالة</th>
                            <th class="px-4 py-3 font-medium">تاريخ الانضمام</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($previewRows as $student)
                            <tr wire:key="preview-student-{{ $student->id }}">
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $student->student_code }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $student->name }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $student->phone ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $student->guardian_name ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $student->guardian_phone ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $student->statusLabel() }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ optional($student->joined_at)->format('Y-m-d') ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">لا توجد سجلات مطابقة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $previewRows->links() }}</div>
        </div>
    @endif

    @if ($activeTab === 'payments')
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
            <div class="mb-4 flex flex-wrap gap-2">
                <select wire:model.live="paymentsStatus" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">كل الحالات</option>
                    <option value="completed">مكتملة</option>
                    <option value="cancelled">ملغاة</option>
                </select>
                <select wire:model.live="paymentsMethod" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">كل طرق الدفع</option>
                    <option value="cash">نقدًا</option>
                    <option value="bank_transfer">تحويل بنكي</option>
                    <option value="wallet">محفظة إلكترونية</option>
                    <option value="card">بطاقة</option>
                    <option value="other">أخرى</option>
                </select>
                <input type="date" wire:model.live="paymentsFrom" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <input type="date" wire:model.live="paymentsTo" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>

            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">عدد السجلات المطابقة: <strong class="text-slate-900 dark:text-slate-100">{{ $paymentsCount }}</strong></p>
                <x-ui.button wire:click="exportPayments" class="w-auto px-4">تصدير Excel</x-ui.button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full min-w-[760px] text-right text-sm">
                    <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">رقم الإيصال</th>
                            <th class="px-4 py-3 font-medium">الطالب</th>
                            <th class="px-4 py-3 font-medium">كود الطالب</th>
                            <th class="px-4 py-3 font-medium">المبلغ</th>
                            <th class="px-4 py-3 font-medium">طريقة الدفع</th>
                            <th class="px-4 py-3 font-medium">تاريخ الدفع</th>
                            <th class="px-4 py-3 font-medium">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($previewRows as $payment)
                            <tr wire:key="preview-payment-{{ $payment->id }}">
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $payment->receipt_number }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $payment->student->name }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $payment->student->student_code }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $payment->methodLabel() }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $payment->paid_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $payment->statusLabel() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">لا توجد سجلات مطابقة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $previewRows->links() }}</div>
        </div>
    @endif

    @if ($activeTab === 'monthly_dues')
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
            <div class="mb-4 flex flex-wrap gap-2">
                <select wire:model.live="duesStatus" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">كل الحالات</option>
                    <option value="unpaid">غير مدفوع</option>
                    <option value="partially_paid">مدفوع جزئيًا</option>
                    <option value="paid">مدفوع</option>
                    <option value="waived">مُعفى</option>
                    <option value="cancelled">ملغي</option>
                </select>
                <select wire:model.live="duesGroupId" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">كل المجموعات</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">عدد السجلات المطابقة: <strong class="text-slate-900 dark:text-slate-100">{{ $duesCount }}</strong></p>
                <x-ui.button wire:click="exportMonthlyDues" class="w-auto px-4">تصدير Excel</x-ui.button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full min-w-[960px] text-right text-sm">
                    <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">الطالب</th>
                            <th class="px-4 py-3 font-medium">كود الطالب</th>
                            <th class="px-4 py-3 font-medium">المجموعة</th>
                            <th class="px-4 py-3 font-medium">الشهر</th>
                            <th class="px-4 py-3 font-medium">السنة</th>
                            <th class="px-4 py-3 font-medium">تاريخ الاستحقاق</th>
                            <th class="px-4 py-3 font-medium">المبلغ النهائي</th>
                            <th class="px-4 py-3 font-medium">المدفوع</th>
                            <th class="px-4 py-3 font-medium">المتبقي</th>
                            <th class="px-4 py-3 font-medium">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($previewRows as $due)
                            <tr wire:key="preview-due-{{ $due->id }}">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $due->student->name }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $due->student->student_code }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $due->group->name }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ \App\Models\MonthlyDue::monthLabel($due->billing_month) }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $due->billing_year }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $due->due_date->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ number_format((float) $due->final_amount, 2) }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ number_format((float) $due->paid_amount, 2) }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ number_format((float) $due->remaining_amount, 2) }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $due->statusLabel() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">لا توجد سجلات مطابقة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $previewRows->links() }}</div>
        </div>
    @endif

    @if ($activeTab === 'users')
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
            <div class="mb-4 flex flex-wrap gap-2">
                <input
                    type="text"
                    wire:model.live.debounce.400ms="usersSearch"
                    placeholder="ابحث بالاسم أو البريد أو الهاتف..."
                    class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                >
                <select wire:model.live="usersStatus" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">كل الحالات</option>
                    <option value="active">مفعل</option>
                    <option value="inactive">موقوف</option>
                </select>
            </div>

            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">عدد السجلات المطابقة: <strong class="text-slate-900 dark:text-slate-100">{{ $usersCount }}</strong></p>
                <x-ui.button wire:click="exportUsers" class="w-auto px-4">تصدير Excel</x-ui.button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full min-w-[760px] text-right text-sm">
                    <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">الاسم</th>
                            <th class="px-4 py-3 font-medium">البريد الإلكتروني</th>
                            <th class="px-4 py-3 font-medium">الهاتف</th>
                            <th class="px-4 py-3 font-medium">الأدوار</th>
                            <th class="px-4 py-3 font-medium">الحالة</th>
                            <th class="px-4 py-3 font-medium">آخر تسجيل دخول</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($previewRows as $user)
                            <tr wire:key="preview-user-{{ $user->id }}">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $user->name }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $user->email }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $user->phone ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $user->getRoleNames()->implode('، ') ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $user->is_active ? 'مفعل' : 'موقوف' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ optional($user->last_login_at)->format('Y-m-d H:i') ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">لا توجد سجلات مطابقة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $previewRows->links() }}</div>
        </div>
    @endif

    @if ($activeTab === 'registration_requests')
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
            <div class="mb-4 flex flex-wrap gap-2">
                <input
                    type="text"
                    wire:model.live.debounce.400ms="registrationsSearch"
                    placeholder="ابحث باسم الطالب أو الهاتف..."
                    class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                >
                <select wire:model.live="registrationsStatus" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">كل الحالات</option>
                    <option value="pending">قيد المراجعة</option>
                    <option value="approved">مقبول</option>
                    <option value="rejected">مرفوض</option>
                </select>
                <select wire:model.live="registrationsGroupId" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">كل المجموعات</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">عدد السجلات المطابقة: <strong class="text-slate-900 dark:text-slate-100">{{ $registrationsCount }}</strong></p>
                <x-ui.button wire:click="exportRegistrationRequests" class="w-auto px-4">تصدير Excel</x-ui.button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full min-w-[760px] text-right text-sm">
                    <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">اسم الطالب</th>
                            <th class="px-4 py-3 font-medium">الهاتف</th>
                            <th class="px-4 py-3 font-medium">هاتف ولي الأمر</th>
                            <th class="px-4 py-3 font-medium">المجموعة</th>
                            <th class="px-4 py-3 font-medium">الحالة</th>
                            <th class="px-4 py-3 font-medium">تاريخ الطلب</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($previewRows as $request)
                            <tr wire:key="preview-registration-{{ $request->id }}">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $request->student_name }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $request->phone }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $request->guardian_phone ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $request->group->name }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $request->statusLabel() }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $request->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">لا توجد سجلات مطابقة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $previewRows->links() }}</div>
        </div>
    @endif

    @if ($activeTab === 'class_sessions')
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
            <div class="mb-4 flex flex-wrap gap-2">
                <select wire:model.live="sessionsStatus" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">كل الحالات</option>
                    <option value="scheduled">مجدولة</option>
                    <option value="open">مفتوحة</option>
                    <option value="completed">مكتملة</option>
                    <option value="cancelled">ملغاة</option>
                </select>
                <select wire:model.live="sessionsGroupId" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">كل المجموعات</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
                <input type="date" wire:model.live="sessionsFrom" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <input type="date" wire:model.live="sessionsTo" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>

            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">عدد السجلات المطابقة: <strong class="text-slate-900 dark:text-slate-100">{{ $sessionsCount }}</strong></p>
                <x-ui.button wire:click="exportClassSessions" class="w-auto px-4">تصدير Excel</x-ui.button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full min-w-[860px] text-right text-sm">
                    <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">المجموعة</th>
                            <th class="px-4 py-3 font-medium">تاريخ الحصة</th>
                            <th class="px-4 py-3 font-medium">الوقت المتوقع</th>
                            <th class="px-4 py-3 font-medium">بدأت الساعة</th>
                            <th class="px-4 py-3 font-medium">انتهت الساعة</th>
                            <th class="px-4 py-3 font-medium">الحالة</th>
                            <th class="px-4 py-3 font-medium">الموضوع</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($previewRows as $session)
                            <tr wire:key="preview-session-{{ $session->id }}">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $session->group->name }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $session->scheduled_date->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $session->expected_start_time }} - {{ $session->expected_end_time }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ optional($session->actual_started_at)->format('Y-m-d H:i') ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ optional($session->actual_closed_at)->format('Y-m-d H:i') ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $session->statusLabel() }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $session->lesson_topic ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">لا توجد سجلات مطابقة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $previewRows->links() }}</div>
        </div>
    @endif
</div>
