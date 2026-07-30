<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">أماكن التدريس</h1>
            <p class="text-sm text-slate-500">السنتر، المنزل، أونلاين، أو أي مكان آخر تُقام فيه المجموعات</p>
        </div>

        <x-ui.button wire:click="create" class="w-auto px-4">+ مكان جديد</x-ui.button>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[720px] text-right text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">المكان</th>
                    <th class="px-4 py-3 font-medium">النوع</th>
                    <th class="px-4 py-3 font-medium">الموقع</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($locations as $location)
                    <tr wire:key="location-{{ $location->id }}">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $location->name }}</div>
                            @if ($location->phone)
                                <div class="text-xs text-slate-500">{{ $location->phone }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <x-ui.badge color="indigo">{{ $location->typeLabel() }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ collect([$location->city, $location->governorate])->filter()->implode('، ') ?: '—' }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($location->is_active)
                                <x-ui.badge color="green">مفعّل</x-ui.badge>
                            @else
                                <x-ui.badge color="slate">معطّل</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <button wire:click="edit({{ $location->id }})" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                    تعديل
                                </button>
                                <button wire:click="askDelete({{ $location->id }})" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                    حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-400">
                            لم تُضف أي أماكن تدريس بعد.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-ui.modal :show="$showForm" :title="$editingId ? 'تعديل المكان' : 'مكان جديد'" on-close="$set('showForm', false)">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="name" label="اسم المكان" wire:model="name" :error="$errors->first('name')" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">نوع المكان</label>
                <select wire:model="type" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="center">سنتر</option>
                    <option value="home">منزل</option>
                    <option value="online">أونلاين</option>
                    <option value="other">مكان آخر</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="governorate" label="المحافظة" wire:model="governorate" :error="$errors->first('governorate')" />
                <x-ui.input name="city" label="المدينة" wire:model="city" :error="$errors->first('city')" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="area" label="المنطقة" wire:model="area" :error="$errors->first('area')" />
                <x-ui.input name="phone" label="رقم الهاتف" wire:model="phone" :error="$errors->first('phone')" />
            </div>

            <x-ui.input name="address" label="العنوان بالتفصيل" wire:model="address" :error="$errors->first('address')" />
            <x-ui.input name="google_maps_url" type="url" label="رابط خرائط جوجل (اختياري)" wire:model="google_maps_url" :error="$errors->first('google_maps_url')" placeholder="https://maps.google.com/..." />

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-indigo-600">
                مكان مفعّل
            </label>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
