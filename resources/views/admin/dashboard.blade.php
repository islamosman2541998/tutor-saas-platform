<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">لوحة تحكم المنصة</h1>
        <p class="mt-1 text-sm text-slate-500">نظرة سريعة على المدرسين والباقات.</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <x-ui.icon name="building" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $tenantsCount }}</p>
                    <p class="text-xs text-slate-500">إجمالي المدرسين</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <x-ui.icon name="users" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $activeTenantsCount }}</p>
                    <p class="text-xs text-slate-500">حساب نشط</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <x-ui.icon name="building" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $pendingTenantsCount }}</p>
                    <p class="text-xs text-slate-500">قيد المراجعة</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <x-ui.icon name="banknotes" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $plansCount }}</p>
                    <p class="text-xs text-slate-500">باقة مفعّلة</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <a href="{{ route('admin.tenants') }}" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50">
            <x-ui.icon name="building" class="h-6 w-6 text-indigo-600" />
            إدارة المدرسين والاشتراكات
        </a>
        <a href="{{ route('admin.plans') }}" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50">
            <x-ui.icon name="banknotes" class="h-6 w-6 text-indigo-600" />
            إدارة الباقات
        </a>
    </div>
</x-layouts.app>
