<div>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold">أهلًا، {{ auth()->user()->name }} 👋</h1>
            <p class="mt-1 text-sm text-slate-500">هذه لوحة تحكم <strong>{{ $currentTenant->name }}</strong>.</p>
        </div>

        <a
            href="{{ route('tenant.website.home', ['tenant' => $currentTenant->slug]) }}"
            target="_blank"
            class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            <x-ui.icon name="building" class="h-5 w-5" />
            زيارة الموقع التعريفي
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <x-ui.icon name="users" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $studentsCount }}</p>
                    <p class="text-xs text-slate-500">طالب نشط</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <x-ui.icon name="groups" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $groupsCount }}</p>
                    <p class="text-xs text-slate-500">مجموعة نشطة</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <x-ui.icon name="calendar" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $sessionsThisMonth }}</p>
                    <p class="text-xs text-slate-500">حصة هذا الشهر</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                    <x-ui.icon name="academic-cap" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $attendanceRate !== null ? $attendanceRate.'%' : '—' }}</p>
                    <p class="text-xs text-slate-500">نسبة الحضور هذا الشهر</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <x-ui.icon name="banknotes" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ number_format($revenueThisMonth, 2) }}</p>
                    <p class="text-xs text-slate-500">إيرادات هذا الشهر</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600">
                    <x-ui.icon name="banknotes" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-red-700">{{ $overdueCount }}</p>
                    <p class="text-xs text-red-500">استحقاق متأخر ({{ number_format($overdueTotal, 2) }})</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="mb-3 text-sm font-semibold text-slate-600">الإيرادات — آخر 6 أشهر</h2>
            <div data-chart="{{ json_encode($revenueChart) }}"></div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="mb-3 text-sm font-semibold text-slate-600">الاشتراكات الجديدة — آخر 6 أشهر</h2>
            <div data-chart="{{ json_encode($enrollmentsChart) }}"></div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="mb-3 text-sm font-semibold text-slate-600">نسبة الحضور — آخر 6 أشهر</h2>
            <div data-chart="{{ json_encode($attendanceChart) }}"></div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="mb-3 text-sm font-semibold text-slate-600">طرق الدفع هذا الشهر</h2>
            <div data-chart="{{ json_encode($paymentMethodChart) }}"></div>
        </div>
    </div>

    <div class="mt-8">
        <h2 class="mb-3 text-sm font-semibold text-slate-500">روابط سريعة</h2>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <a href="{{ route('tenant.students', ['tenant' => $currentTenant->slug]) }}" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-white p-4 text-center text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50">
                <x-ui.icon name="users" class="h-6 w-6 text-indigo-600" />
                الطلاب
            </a>
            <a href="{{ route('tenant.groups', ['tenant' => $currentTenant->slug]) }}" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-white p-4 text-center text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50">
                <x-ui.icon name="groups" class="h-6 w-6 text-indigo-600" />
                المجموعات
            </a>
            <a href="{{ route('tenant.dues', ['tenant' => $currentTenant->slug]) }}" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-white p-4 text-center text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50">
                <x-ui.icon name="banknotes" class="h-6 w-6 text-indigo-600" />
                المستحقات
            </a>
            <a href="{{ route('tenant.payments', ['tenant' => $currentTenant->slug]) }}" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-white p-4 text-center text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50">
                <x-ui.icon name="banknotes" class="h-6 w-6 text-indigo-600" />
                الدفعات
            </a>
            <a href="{{ route('tenant.overdue', ['tenant' => $currentTenant->slug]) }}" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-white p-4 text-center text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50">
                <x-ui.icon name="banknotes" class="h-6 w-6 text-indigo-600" />
                المتأخرات
            </a>
            <a href="{{ route('tenant.academic-years', ['tenant' => $currentTenant->slug]) }}" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-white p-4 text-center text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50">
                <x-ui.icon name="calendar" class="h-6 w-6 text-indigo-600" />
                السنوات الدراسية
            </a>
            <a href="{{ route('tenant.subjects', ['tenant' => $currentTenant->slug]) }}" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-white p-4 text-center text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50">
                <x-ui.icon name="book" class="h-6 w-6 text-indigo-600" />
                المواد
            </a>
        </div>
    </div>
</div>
