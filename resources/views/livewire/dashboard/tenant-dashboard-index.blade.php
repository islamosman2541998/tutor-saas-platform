<div>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold">أهلًا، {{ auth()->user()->name }} 👋</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">هذه لوحة تحكم <strong>{{ $currentTenant->name }}</strong>.</p>
        </div>

        <a
            href="{{ route('tenant.website.home', ['tenant' => $currentTenant->slug]) }}"
            target="_blank"
            class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700"
        >
            <x-ui.icon name="building" class="h-5 w-5" />
            زيارة الموقع التعريفي
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <x-ui.card>
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                    <x-ui.icon name="users" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $studentsCount }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">طالب نشط</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                    <x-ui.icon name="groups" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $groupsCount }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">مجموعة نشطة</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                    <x-ui.icon name="calendar" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $sessionsThisMonth }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">حصة هذا الشهر</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400">
                    <x-ui.icon name="academic-cap" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $attendanceRate !== null ? $attendanceRate.'%' : '—' }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">نسبة الحضور هذا الشهر</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                    <x-ui.icon name="banknotes" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($revenueThisMonth, 2) }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">إيرادات هذا الشهر</p>
                </div>
            </div>
        </x-ui.card>

        <div class="rounded-2xl border border-red-200 bg-red-50 p-5 dark:border-red-900/50 dark:bg-red-900/20">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400">
                    <x-ui.icon name="banknotes" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $overdueCount }}</p>
                    <p class="text-xs text-red-500 dark:text-red-400/80">استحقاق متأخر ({{ number_format($overdueTotal, 2) }})</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-ui.card>
            <h2 class="mb-3 text-sm font-semibold text-slate-600 dark:text-slate-300">الإيرادات — آخر 6 أشهر</h2>
            <div data-chart="{{ json_encode($revenueChart) }}"></div>
        </x-ui.card>

        <x-ui.card>
            <h2 class="mb-3 text-sm font-semibold text-slate-600 dark:text-slate-300">الاشتراكات الجديدة — آخر 6 أشهر</h2>
            <div data-chart="{{ json_encode($enrollmentsChart) }}"></div>
        </x-ui.card>

        <x-ui.card>
            <h2 class="mb-3 text-sm font-semibold text-slate-600 dark:text-slate-300">نسبة الحضور — آخر 6 أشهر</h2>
            <div data-chart="{{ json_encode($attendanceChart) }}"></div>
        </x-ui.card>

        <x-ui.card>
            <h2 class="mb-3 text-sm font-semibold text-slate-600 dark:text-slate-300">طرق الدفع هذا الشهر</h2>
            <div data-chart="{{ json_encode($paymentMethodChart) }}"></div>
        </x-ui.card>
    </div>

    <div class="mt-8">
        <h2 class="mb-3 text-sm font-semibold text-slate-500 dark:text-slate-400">روابط سريعة</h2>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <a href="{{ route('tenant.students', ['tenant' => $currentTenant->slug]) }}" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-white p-4 text-center text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-indigo-800 dark:hover:bg-indigo-900/20">
                <x-ui.icon name="users" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                الطلاب
            </a>
            <a href="{{ route('tenant.groups', ['tenant' => $currentTenant->slug]) }}" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-white p-4 text-center text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-indigo-800 dark:hover:bg-indigo-900/20">
                <x-ui.icon name="groups" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                المجموعات
            </a>
            <a href="{{ route('tenant.dues', ['tenant' => $currentTenant->slug]) }}" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-white p-4 text-center text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-indigo-800 dark:hover:bg-indigo-900/20">
                <x-ui.icon name="banknotes" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                المستحقات
            </a>
            <a href="{{ route('tenant.payments', ['tenant' => $currentTenant->slug]) }}" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-white p-4 text-center text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-indigo-800 dark:hover:bg-indigo-900/20">
                <x-ui.icon name="banknotes" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                الدفعات
            </a>
            <a href="{{ route('tenant.overdue', ['tenant' => $currentTenant->slug]) }}" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-white p-4 text-center text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-indigo-800 dark:hover:bg-indigo-900/20">
                <x-ui.icon name="banknotes" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                المتأخرات
            </a>
            <a href="{{ route('tenant.academic-years', ['tenant' => $currentTenant->slug]) }}" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-white p-4 text-center text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-indigo-800 dark:hover:bg-indigo-900/20">
                <x-ui.icon name="calendar" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                السنوات الدراسية
            </a>
            <a href="{{ route('tenant.subjects', ['tenant' => $currentTenant->slug]) }}" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-white p-4 text-center text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-indigo-800 dark:hover:bg-indigo-900/20">
                <x-ui.icon name="book" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                المواد
            </a>
        </div>
    </div>
</div>
