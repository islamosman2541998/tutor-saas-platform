<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">مظهر لوحة التحكم</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">ألوان وشكل لوحة التحكم الخاصة بك وبفريقك — المعاينة أدناه تتحدث فورًا مع كل تغيير</p>
        </div>
        <button wire:click="resetToDefaults" type="button" class="rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
            استعادة الافتراضي
        </button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <form wire:submit="save" class="space-y-6 lg:col-span-3">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <h2 class="mb-4 text-sm font-semibold text-slate-600 dark:text-slate-400">الألوان الأساسية</h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach ([
                        'primary_color' => 'الأساسي', 'secondary_color' => 'الثانوي',
                        'sidebar_bg_color' => 'خلفية الشريط الجانبي', 'sidebar_text_color' => 'نص الشريط الجانبي', 'sidebar_active_color' => 'العنصر النشط',
                        'sidebar_label_color' => 'تسميات مجموعات القائمة الجانبية',
                        'topbar_color' => 'الشريط العلوي (موبايل)', 'page_bg_color' => 'خلفية الصفحة', 'card_bg_color' => 'خلفية البطاقات', 'text_color' => 'النص العام',
                        'dark_mode_icon_color' => 'أيقونة الوضع الليلي/النهاري',
                    ] as $field => $label)
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700 dark:text-slate-300">{{ $label }}</label>
                            <div class="flex items-center gap-2">
                                <input type="color" wire:model.live="{{ $field }}" class="h-9 w-9 shrink-0 cursor-pointer rounded border border-slate-300 dark:border-slate-600">
                                <input type="text" wire:model.live="{{ $field }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 px-2 py-1.5 text-xs focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            </div>
                            @error($field) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <h2 class="mb-4 text-sm font-semibold text-slate-600 dark:text-slate-400">التخطيط</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <x-ui.input name="border_radius" type="number" label="استدارة الحواف (px)" wire:model.live="border_radius" :error="$errors->first('border_radius')" />

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">عرض الشريط الجانبي</label>
                        <select wire:model.live="sidebar_size" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            <option value="compact">ضيّق</option>
                            <option value="normal">عادي</option>
                            <option value="wide">واسع</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">وضع الألوان</label>
                        <select wire:model.live="theme_mode" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            <option value="light">فاتح</option>
                            <option value="dark">داكن</option>
                            <option value="user_choice">حسب جهاز الزائر</option>
                        </select>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">هذا هو الوضع الافتراضي — أي مستخدم يقدر يبدّله لنفسه من زر الوضع الليلي/النهاري أعلى اللوحة.</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <h2 class="mb-4 text-sm font-semibold text-slate-600 dark:text-slate-400">الشعارات</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">الشعار الكامل</label>
                        <label
                            for="logo-full"
                            class="group relative flex h-20 cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 transition hover:border-indigo-400 hover:bg-indigo-50/50 dark:border-slate-600 dark:bg-slate-900/30 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/10"
                        >
                            @if ($logo_full)
                                <img src="{{ $logo_full->temporaryUrl() }}" class="absolute inset-0 h-full w-full object-contain p-2">
                            @elseif ($existingLogoFull)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingLogoFull) }}" class="absolute inset-0 h-full w-full object-contain p-2">
                            @endif

                            @if ($logo_full || $existingLogoFull)
                                <div class="absolute inset-0 flex items-center justify-center bg-slate-900/0 opacity-0 transition group-hover:bg-slate-900/50 group-hover:opacity-100">
                                    <span class="rounded-lg bg-white/90 px-3 py-1.5 text-xs font-medium text-slate-700">تغيير</span>
                                </div>
                            @else
                                <div class="flex flex-col items-center gap-1 text-slate-400 dark:text-slate-500">
                                    <x-ui.icon name="image" class="h-5 w-5" />
                                    <span class="text-xs font-medium">اضغط لاختيار صورة</span>
                                </div>
                            @endif

                            <input id="logo-full" type="file" wire:model="logo_full" accept="image/*" class="sr-only">
                        </label>
                        <div wire:loading wire:target="logo_full" class="mt-1 text-xs text-slate-400 dark:text-slate-500">جارٍ الرفع...</div>
                        @error('logo_full') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">الشعار المصغّر</label>
                        <label
                            for="logo-mini"
                            class="group relative flex h-20 w-20 cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 transition hover:border-indigo-400 hover:bg-indigo-50/50 dark:border-slate-600 dark:bg-slate-900/30 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/10"
                        >
                            @if ($logo_mini)
                                <img src="{{ $logo_mini->temporaryUrl() }}" class="absolute inset-0 h-full w-full object-contain p-2">
                            @elseif ($existingLogoMini)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingLogoMini) }}" class="absolute inset-0 h-full w-full object-contain p-2">
                            @endif

                            @if ($logo_mini || $existingLogoMini)
                                <div class="absolute inset-0 flex items-center justify-center bg-slate-900/0 opacity-0 transition group-hover:bg-slate-900/50 group-hover:opacity-100">
                                    <span class="rounded-lg bg-white/90 px-2 py-1 text-[11px] font-medium text-slate-700">تغيير</span>
                                </div>
                            @else
                                <div class="flex flex-col items-center gap-1 text-slate-400 dark:text-slate-500">
                                    <x-ui.icon name="image" class="h-5 w-5" />
                                </div>
                            @endif

                            <input id="logo-mini" type="file" wire:model="logo_mini" accept="image/*" class="sr-only">
                        </label>
                        <div wire:loading wire:target="logo_mini" class="mt-1 text-xs text-slate-400 dark:text-slate-500">جارٍ الرفع...</div>
                        @error('logo_mini') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">الفافيكون</label>
                        <label
                            for="favicon"
                            class="group relative flex h-20 w-20 cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 transition hover:border-indigo-400 hover:bg-indigo-50/50 dark:border-slate-600 dark:bg-slate-900/30 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/10"
                        >
                            @if ($favicon)
                                <img src="{{ $favicon->temporaryUrl() }}" class="absolute inset-0 h-full w-full object-contain p-2">
                            @elseif ($existingFavicon)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingFavicon) }}" class="absolute inset-0 h-full w-full object-contain p-2">
                            @endif

                            @if ($favicon || $existingFavicon)
                                <div class="absolute inset-0 flex items-center justify-center bg-slate-900/0 opacity-0 transition group-hover:bg-slate-900/50 group-hover:opacity-100">
                                    <span class="rounded-lg bg-white/90 px-2 py-1 text-[11px] font-medium text-slate-700">تغيير</span>
                                </div>
                            @else
                                <div class="flex flex-col items-center gap-1 text-slate-400 dark:text-slate-500">
                                    <x-ui.icon name="image" class="h-5 w-5" />
                                </div>
                            @endif

                            <input id="favicon" type="file" wire:model="favicon" accept="image/*" class="sr-only">
                        </label>
                        <div wire:loading wire:target="favicon" class="mt-1 text-xs text-slate-400 dark:text-slate-500">جارٍ الرفع...</div>
                        @error('favicon') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <x-ui.button type="submit" class="w-auto px-6">حفظ المظهر</x-ui.button>
        </form>

        <div class="lg:col-span-2">
            <div class="sticky top-6">
                <p class="mb-3 text-sm font-semibold text-slate-600 dark:text-slate-400">معاينة حيّة</p>

                <div class="overflow-hidden rounded-2xl border border-slate-300 dark:border-slate-600 shadow-sm" style="border-radius: {{ (int) $border_radius }}px;">
                    <div class="flex" style="background: {{ $page_bg_color }};">
                        {{-- Mini sidebar --}}
                        <div class="w-24 shrink-0 p-2" style="background: {{ $sidebar_bg_color }};">
                            <div class="mb-3 h-4 w-14 rounded" style="background: {{ $primary_color }};"></div>
                            <p class="mb-1 px-1 text-[7px] font-semibold uppercase tracking-wide" style="color: {{ $sidebar_label_color }};">إدارة الدروس</p>
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
                            <div class="mb-3 flex h-6 items-center justify-end gap-1.5 rounded px-1.5" style="background: {{ $topbar_color }}; border-radius: {{ max(0, (int) $border_radius - 8) }}px;">
                                <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $dark_mode_icon_color }};"></span>
                                <span class="h-3.5 w-9 rounded-full" style="background: {{ $page_bg_color }};"></span>
                            </div>

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
