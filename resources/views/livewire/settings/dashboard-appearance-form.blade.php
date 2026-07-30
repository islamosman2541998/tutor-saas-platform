<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">مظهر لوحة التحكم</h1>
            <p class="text-sm text-slate-500">ألوان وشكل لوحة التحكم الخاصة بك وبفريقك — المعاينة أدناه تتحدث فورًا مع كل تغيير</p>
        </div>
        <button wire:click="resetToDefaults" type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            استعادة الافتراضي
        </button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <form wire:submit="save" class="space-y-6 lg:col-span-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="mb-4 text-sm font-semibold text-slate-600">الألوان الأساسية</h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach ([
                        'primary_color' => 'الأساسي', 'secondary_color' => 'الثانوي',
                        'sidebar_bg_color' => 'خلفية الشريط الجانبي', 'sidebar_text_color' => 'نص الشريط الجانبي', 'sidebar_active_color' => 'العنصر النشط',
                        'topbar_color' => 'الشريط العلوي (موبايل)', 'page_bg_color' => 'خلفية الصفحة', 'card_bg_color' => 'خلفية البطاقات', 'text_color' => 'النص العام',
                    ] as $field => $label)
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">{{ $label }}</label>
                            <div class="flex items-center gap-2">
                                <input type="color" wire:model.live="{{ $field }}" class="h-9 w-9 shrink-0 cursor-pointer rounded border border-slate-300">
                                <input type="text" wire:model.live="{{ $field }}" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            </div>
                            @error($field) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="mb-4 text-sm font-semibold text-slate-600">التخطيط</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <x-ui.input name="border_radius" type="number" label="استدارة الحواف (px)" wire:model.live="border_radius" :error="$errors->first('border_radius')" />

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">عرض الشريط الجانبي</label>
                        <select wire:model.live="sidebar_size" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            <option value="compact">ضيّق</option>
                            <option value="normal">عادي</option>
                            <option value="wide">واسع</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">وضع الألوان</label>
                        <select wire:model.live="theme_mode" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            <option value="light">فاتح</option>
                            <option value="dark">داكن</option>
                            <option value="user_choice">حسب اختيار المستخدم</option>
                        </select>
                        <p class="mt-1 text-xs text-amber-600">الوضع الداكن قيد التطوير حاليًا — يُحفظ اختيارك لكن اللوحة تعرض بالوضع الفاتح مؤقتًا.</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="mb-4 text-sm font-semibold text-slate-600">الشعارات</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">الشعار الكامل</label>
                        <input type="file" wire:model="logo_full" class="block w-full text-sm">
                        @if ($existingLogoFull && ! $logo_full)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingLogoFull) }}" class="mt-2 h-10 rounded border border-slate-200 object-contain">
                        @endif
                        @error('logo_full') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">الشعار المصغّر</label>
                        <input type="file" wire:model="logo_mini" class="block w-full text-sm">
                        @if ($existingLogoMini && ! $logo_mini)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingLogoMini) }}" class="mt-2 h-10 w-10 rounded border border-slate-200 object-contain">
                        @endif
                        @error('logo_mini') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">الفافيكون</label>
                        <input type="file" wire:model="favicon" class="block w-full text-sm">
                        @if ($existingFavicon && ! $favicon)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingFavicon) }}" class="mt-2 h-8 w-8 rounded border border-slate-200 object-contain">
                        @endif
                        @error('favicon') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <x-ui.button type="submit" class="w-auto px-6">حفظ المظهر</x-ui.button>
        </form>

        <div class="lg:col-span-2">
            <div class="sticky top-6">
                <p class="mb-3 text-sm font-semibold text-slate-600">معاينة حيّة</p>

                <div class="overflow-hidden rounded-2xl border border-slate-300 shadow-sm" style="border-radius: {{ (int) $border_radius }}px;">
                    <div class="flex" style="background: {{ $page_bg_color }};">
                        {{-- Mini sidebar --}}
                        <div class="w-24 shrink-0 p-2" style="background: {{ $sidebar_bg_color }};">
                            <div class="mb-3 h-4 w-14 rounded" style="background: {{ $primary_color }};"></div>
                            <div class="space-y-1.5">
                                <div class="rounded px-2 py-1.5 text-[9px] font-medium" style="background: {{ $sidebar_active_color }}; color: #fff; border-radius: {{ max(0, (int) $border_radius - 8) }}px;">
                                    الرئيسية
                                </div>
                                <div class="rounded px-2 py-1.5 text-[9px]" style="color: {{ $sidebar_text_color }};">الطلاب</div>
                                <div class="rounded px-2 py-1.5 text-[9px]" style="color: {{ $sidebar_text_color }};">المجموعات</div>
                                <div class="rounded px-2 py-1.5 text-[9px]" style="color: {{ $sidebar_text_color }};">التقارير</div>
                            </div>
                        </div>

                        {{-- Main area --}}
                        <div class="flex-1 p-3">
                            <div class="mb-3 h-6 rounded" style="background: {{ $topbar_color }}; border-radius: {{ max(0, (int) $border_radius - 8) }}px;"></div>

                            <div class="space-y-2 rounded-lg p-3 shadow-sm" style="background: {{ $card_bg_color }}; border-radius: {{ $border_radius }}px;">
                                <p class="text-[10px] font-bold" style="color: {{ $text_color }};">بطاقة تجريبية</p>
                                <p class="text-[9px]" style="color: {{ $text_color }}; opacity: 0.7;">هذا النص يوضّح لون النص العام على خلفية البطاقة.</p>
                                <div class="mt-2 inline-block rounded px-3 py-1 text-[9px] font-semibold text-white" style="background: {{ $primary_color }}; border-radius: {{ max(0, (int) $border_radius - 8) }}px;">
                                    زر أساسي
                                </div>
                                <div class="mt-1 inline-block rounded px-3 py-1 text-[9px] font-semibold text-white" style="background: {{ $secondary_color }}; border-radius: {{ max(0, (int) $border_radius - 8) }}px;">
                                    زر ثانوي
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
