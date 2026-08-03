<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">مظهر صفحة تسجيل الدخول</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">الخلفية والبطاقة والألوان — المعاينة أدناه تتحدث فورًا مع كل تغيير</p>
        </div>
        <a href="{{ route('tenant.login', ['tenant' => $currentTenant->slug]) }}" target="_blank" class="rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
            فتح صفحة الدخول ↗
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <form wire:submit="save" class="space-y-6 lg:col-span-3">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <h2 class="mb-4 text-sm font-semibold text-slate-600 dark:text-slate-400">الخلفية</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700 dark:text-slate-300">لون الخلفية</label>
                        <div class="flex items-center gap-2">
                            <input type="color" wire:model.live="background_color" class="h-9 w-9 shrink-0 cursor-pointer rounded border border-slate-300 dark:border-slate-600">
                            <input type="text" wire:model.live="background_color" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 px-2 py-1.5 text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">صورة الخلفية (اختياري)</label>
                        <label
                            for="background-image"
                            class="group relative flex h-24 cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 transition hover:border-indigo-400 hover:bg-indigo-50/50 dark:border-slate-600 dark:bg-slate-900/30 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/10"
                        >
                            @if ($background_image)
                                <img src="{{ $background_image->temporaryUrl() }}" class="absolute inset-0 h-full w-full object-cover">
                            @elseif ($existingBackgroundImage)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingBackgroundImage) }}" class="absolute inset-0 h-full w-full object-cover">
                            @endif

                            @if ($background_image || $existingBackgroundImage)
                                <div class="absolute inset-0 flex items-center justify-center bg-slate-900/0 opacity-0 transition group-hover:bg-slate-900/50 group-hover:opacity-100">
                                    <span class="rounded-lg bg-white/90 px-3 py-1.5 text-xs font-medium text-slate-700">تغيير الصورة</span>
                                </div>
                            @else
                                <div class="flex flex-col items-center gap-1 text-slate-400 dark:text-slate-500">
                                    <x-ui.icon name="image" class="h-6 w-6" />
                                    <span class="text-xs font-medium">اضغط لاختيار صورة</span>
                                </div>
                            @endif

                            <input id="background-image" type="file" wire:model="background_image" accept="image/*" class="sr-only">
                        </label>
                        <div wire:loading wire:target="background_image" class="mt-1 text-xs text-slate-400 dark:text-slate-500">جارٍ الرفع...</div>
                        @error('background_image') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                        <input type="checkbox" wire:model.live="overlay_enabled" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                        تفعيل طبقة تعتيم فوق صورة الخلفية
                    </label>

                    @if ($overlay_enabled)
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700 dark:text-slate-300">شفافية الطبقة (0 — 1)</label>
                            <input type="range" min="0" max="1" step="0.05" wire:model.live="overlay_opacity" class="w-full">
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <h2 class="mb-4 text-sm font-semibold text-slate-600 dark:text-slate-400">البطاقة</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">موضع البطاقة</label>
                        <select wire:model.live="card_position" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm">
                            <option value="center">وسط</option>
                            <option value="left">يسار</option>
                            <option value="right">يمين</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700 dark:text-slate-300">خلفية البطاقة</label>
                        <div class="flex items-center gap-2">
                            <input type="color" wire:model.live="card_bg_color" class="h-9 w-9 shrink-0 cursor-pointer rounded border border-slate-300 dark:border-slate-600">
                            <input type="text" wire:model.live="card_bg_color" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 px-2 py-1.5 text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700 dark:text-slate-300">شفافية البطاقة (0 — 1)</label>
                        <input type="range" min="0.1" max="1" step="0.05" wire:model.live="card_opacity" class="mt-2.5 w-full">
                    </div>

                    <x-ui.input name="border_radius" type="number" label="استدارة الحواف (px)" wire:model.live="border_radius" />

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">الظل</label>
                        <select wire:model.live="shadow_style" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm">
                            <option value="none">بدون</option>
                            <option value="soft">خفيف</option>
                            <option value="medium">متوسط</option>
                            <option value="strong">قوي</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">وضع الألوان</label>
                        <select wire:model.live="theme_mode" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm">
                            <option value="light">فاتح</option>
                            <option value="dark">داكن</option>
                            <option value="user_choice">حسب جهاز الزائر</option>
                        </select>
                    </div>

                    <label class="mt-6 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                        <input type="checkbox" wire:model.live="card_blur" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                        تأثير ضبابي خلف البطاقة
                    </label>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <h2 class="mb-4 text-sm font-semibold text-slate-600 dark:text-slate-400">الألوان</h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach ([
                        'brand_name_color' => 'اسم المستأجر (الشعار)', 'heading_color' => 'عنوان الصفحة', 'title_color' => 'عنوان الترحيب',
                        'text_color' => 'النص', 'label_color' => 'تسميات الحقول',
                        'input_bg_color' => 'خلفية الحقول', 'input_text_color' => 'نص الحقول', 'input_border_color' => 'حدود الحقول',
                        'input_focus_color' => 'تركيز الحقل', 'button_color' => 'الزر', 'button_text_color' => 'نص الزر', 'button_hover_color' => 'الزر (تمرير)',
                    ] as $field => $label)
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700 dark:text-slate-300">{{ $label }}</label>
                            <div class="flex items-center gap-2">
                                <input type="color" wire:model.live="{{ $field }}" class="h-9 w-9 shrink-0 cursor-pointer rounded border border-slate-300 dark:border-slate-600">
                                <input type="text" wire:model.live="{{ $field }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 px-2 py-1.5 text-xs">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <h2 class="mb-4 text-sm font-semibold text-slate-600 dark:text-slate-400">المحتوى والخيارات</h2>
                <div class="space-y-4">
                    <x-ui.input name="welcome_title" label="عنوان ترحيبي (اختياري)" wire:model.live="welcome_title" placeholder="أهلًا بعودتك" />
                    <x-ui.input name="welcome_description" label="وصف ترحيبي (اختياري)" wire:model.live="welcome_description" />

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">صورة جانبية (اختياري — تظهر عند اختيار موضع يسار/يمين)</label>
                        <label
                            for="side-image"
                            class="group relative flex h-24 cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 transition hover:border-indigo-400 hover:bg-indigo-50/50 dark:border-slate-600 dark:bg-slate-900/30 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/10"
                        >
                            @if ($side_image)
                                <img src="{{ $side_image->temporaryUrl() }}" class="absolute inset-0 h-full w-full object-cover">
                            @elseif ($existingSideImage)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingSideImage) }}" class="absolute inset-0 h-full w-full object-cover">
                            @endif

                            @if ($side_image || $existingSideImage)
                                <div class="absolute inset-0 flex items-center justify-center bg-slate-900/0 opacity-0 transition group-hover:bg-slate-900/50 group-hover:opacity-100">
                                    <span class="rounded-lg bg-white/90 px-3 py-1.5 text-xs font-medium text-slate-700">تغيير الصورة</span>
                                </div>
                            @else
                                <div class="flex flex-col items-center gap-1 text-slate-400 dark:text-slate-500">
                                    <x-ui.icon name="image" class="h-6 w-6" />
                                    <span class="text-xs font-medium">اضغط لاختيار صورة</span>
                                </div>
                            @endif

                            <input id="side-image" type="file" wire:model="side_image" accept="image/*" class="sr-only">
                        </label>
                        <div wire:loading wire:target="side_image" class="mt-1 text-xs text-slate-400 dark:text-slate-500">جارٍ الرفع...</div>
                        @error('side_image') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                        <input type="checkbox" wire:model.live="show_logo" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                        إظهار شعار المدرّس أعلى البطاقة
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                        <input type="checkbox" wire:model="remember_me_enabled" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                        السماح بخيار "تذكرني"
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                        <input type="checkbox" wire:model="forgot_password_enabled" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                        إظهار رابط "نسيت كلمة المرور؟"
                    </label>
                </div>
            </div>

            <x-ui.button type="submit" class="w-auto px-6">حفظ المظهر</x-ui.button>
        </form>

        <div class="lg:col-span-2">
            <div class="sticky top-6">
                <p class="mb-3 text-sm font-semibold text-slate-600 dark:text-slate-400">معاينة حيّة</p>

                <div
                    class="relative flex h-80 items-center overflow-hidden rounded-2xl border border-slate-300 dark:border-slate-600 p-4"
                    style="background: {{ $background_color }}; justify-content: {{ $card_position === 'left' ? 'flex-start' : ($card_position === 'right' ? 'flex-end' : 'center') }};"
                >
                    @php
                        $previewBackgroundUrl = $background_image
                            ? $background_image->temporaryUrl()
                            : ($existingBackgroundImage ? \Illuminate\Support\Facades\Storage::disk('public')->url($existingBackgroundImage) : null);
                        $previewSideUrl = $side_image
                            ? $side_image->temporaryUrl()
                            : ($existingSideImage ? \Illuminate\Support\Facades\Storage::disk('public')->url($existingSideImage) : null);
                    @endphp

                    @if ($previewBackgroundUrl)
                        <div class="absolute inset-0" style="background: url('{{ $previewBackgroundUrl }}') center/cover;"></div>
                        @if ($overlay_enabled)
                            <div class="absolute inset-0 bg-black" style="opacity: {{ $overlay_opacity }};"></div>
                        @endif
                    @endif

                    @if ($previewSideUrl && $card_position !== 'center')
                        <img
                            src="{{ $previewSideUrl }}"
                            class="relative z-10 h-full max-h-64 w-20 object-cover {{ $card_position === 'left' ? 'order-2 ms-3' : 'me-3' }}"
                            style="border-radius: {{ (int) $border_radius }}px;"
                        >
                    @endif

                    <div
                        class="relative z-10 w-full max-w-[220px] p-4"
                        style="
                            background: {{ $card_bg_color }};
                            opacity: {{ $card_opacity }};
                            border-radius: {{ (int) $border_radius }}px;
                            box-shadow: {{ match($shadow_style) { 'none' => 'none', 'soft' => '0 2px 8px rgba(15,23,42,.06)', 'strong' => '0 20px 50px rgba(15,23,42,.25)', default => '0 10px 25px rgba(15,23,42,.12)' } }};
                            {{ $card_blur ? 'backdrop-filter: blur(12px);' : '' }}
                        "
                    >
                        @if ($show_logo)
                            <p class="mb-2 text-xs font-bold" style="color: {{ $brand_name_color }};">{{ $currentTenant->teacher_name ?? config('app.name') }}</p>
                        @endif

                        @if ($welcome_title)
                            <p class="text-xs font-bold" style="color: {{ $title_color }};">{{ $welcome_title }}</p>
                        @endif
                        @if ($welcome_description)
                            <p class="mt-0.5 text-[9px]" style="color: {{ $text_color }};">{{ $welcome_description }}</p>
                        @endif

                        <p class="mt-2 text-xs font-bold" style="color: {{ $heading_color }};">تسجيل الدخول</p>

                        <div class="mt-3 space-y-2">
                            <div>
                                <p class="mb-1 text-[8px] font-medium" style="color: {{ $label_color }};">البريد الإلكتروني</p>
                                <div class="h-5 rounded border" style="background: {{ $input_bg_color }}; border-color: {{ $input_border_color }};"></div>
                            </div>
                            <div>
                                <p class="mb-1 text-[8px] font-medium" style="color: {{ $label_color }};">كلمة المرور</p>
                                <div class="h-5 rounded border" style="background: {{ $input_bg_color }}; border-color: {{ $input_focus_color }}; border-width: 2px;"></div>
                            </div>
                        </div>

                        @if ($remember_me_enabled || $forgot_password_enabled)
                            <div class="mt-2 flex items-center justify-between text-[7px]" style="color: {{ $text_color }};">
                                @if ($remember_me_enabled)<span>☐ تذكرني</span>@endif
                                @if ($forgot_password_enabled)<span>نسيت كلمة المرور؟</span>@endif
                            </div>
                        @endif

                        <div class="mt-3 rounded px-3 py-1.5 text-center text-[9px] font-semibold" style="background: {{ $button_color }}; color: {{ $button_text_color }};">
                            دخول
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
