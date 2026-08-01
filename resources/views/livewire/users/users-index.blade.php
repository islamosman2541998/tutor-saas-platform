<div>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold">المستخدمون</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">إدارة حسابات الفريق (مساعد، محاسب، مسؤول محتوى) وصلاحياتهم</p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="ابحث بالاسم أو البريد أو الهاتف..."
                class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            >
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">كل الحالات</option>
                <option value="active">مفعل</option>
                <option value="inactive">موقوف</option>
            </select>
            <x-ui.button wire:click="create" class="w-auto px-4">+ مستخدم جديد</x-ui.button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        <table class="w-full min-w-[760px] text-right text-sm">
            <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3 font-medium">المستخدم</th>
                    <th class="px-4 py-3 font-medium">الهاتف</th>
                    <th class="px-4 py-3 font-medium">الدور</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($users as $user)
                    <tr wire:key="user-{{ $user->id }}">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-50 text-sm font-medium text-indigo-600">
                                    {{ mb_substr($user->name, 0, 1) }}
                                </span>
                                <div>
                                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ $user->name }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $user->phone ?: '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($user->id === $ownerUserId)
                                <x-ui.badge color="indigo">صاحب الحساب</x-ui.badge>
                            @else
                                <x-ui.badge color="slate">{{ \App\Support\Permissions\TenantPermissions::roleLabel($user->getRoleNames()->first() ?? '') }}</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <x-ui.badge :color="$user->is_active ? 'green' : 'red'">{{ $user->is_active ? 'مفعل' : 'موقوف' }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3">
                            @if ($user->id !== $ownerUserId)
                                <div class="flex flex-wrap gap-2">
                                    <button wire:click="edit({{ $user->id }})" class="rounded-lg border border-slate-300 dark:border-slate-600 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                        تعديل
                                    </button>
                                    @if ($user->is_active)
                                        <button wire:click="askToggleStatus({{ $user->id }})" class="rounded-lg border border-amber-200 px-2.5 py-1.5 text-xs font-medium text-amber-600 hover:bg-amber-50">
                                            إيقاف
                                        </button>
                                    @else
                                        <button wire:click="askToggleStatus({{ $user->id }})" class="rounded-lg border border-emerald-200 px-2.5 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50">
                                            تفعيل
                                        </button>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-slate-400 dark:text-slate-500">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">
                            لا يوجد أعضاء فريق بعد. أضف أول مستخدم لتبدأ.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    <x-ui.modal :show="$showForm" :title="$editingId ? 'تعديل بيانات المستخدم' : 'مستخدم جديد'" on-close="$set('showForm', false)">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="name" label="الاسم" wire:model="name" :error="$errors->first('name')" autofocus />
            <x-ui.input name="email" type="email" label="البريد الإلكتروني" wire:model="email" :error="$errors->first('email')" />
            <x-ui.input name="phone" label="رقم الهاتف (اختياري)" wire:model="phone" :error="$errors->first('phone')" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">الدور</label>
                <select wire:model="role" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    @foreach ($assignableRoles as $roleOption)
                        <option value="{{ $roleOption }}">{{ \App\Support\Permissions\TenantPermissions::roleLabel($roleOption) }}</option>
                    @endforeach
                </select>
                @error('role') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <x-ui.input
                name="password"
                type="password"
                :label="$editingId ? 'كلمة المرور الجديدة (اتركها فارغة للإبقاء على الحالية)' : 'كلمة المرور'"
                wire:model="password"
                :error="$errors->first('password')"
            />

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
