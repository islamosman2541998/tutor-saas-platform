<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">مظهر صفحة تسجيل الدخول</h1>
            <p class="text-sm text-slate-500">الخلفية والبطاقة والألوان — المعاينة أدناه تتحدث فورًا مع كل تغيير</p>
        </div>
        <a href="{{ route('tenant.login', ['tenant' => $currentTenant->slug]) }}" target="_blank" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            فتح صفحة الدخول ↗
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <form wire:submit="save" class="space-y-6 lg:col-span-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="mb-4 text-sm font-semibold text-slate-600">الخلفية</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">لون الخلفية</label>
                        <div class="flex items-center gap-2">
                            <input type="color" wire:model.live="background_color" class="h-9 w-9 shrink-0 cursor-pointer rounded border border-slate-300">
                            <input type="text" wire:model.live="background_color" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">صورة الخلفية (اختياري)</label>
                        <input type="file" wire:model="background_image" class="block w-full text-sm">
                        @if ($existingBackgroundImage && ! $background_image)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingBackgroundImage) }}" class="mt-2 h-10 rounded border border-slate-200 object-cover">
                        @endif
                        @error('background_image') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" wire:model.live="overlay_enabled" class="rounded border-slate-300 text-indigo-600">
                        تفعيل طبقة تعتيم فوق صورة الخلفية
                    </label>

                    @if ($overlay_enabled)
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">شفافية الطبقة (0 — 1)</label>
                            <input type="range" min="0" max="1" step="0.05" wire:model.live="overlay_opacity" class="w-full">
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="mb-4 text-sm font-semibold text-slate-600">البطاقة</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">موضع البطاقة</label>
                        <select wire:model.live="card_position" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm">
                            <option value="center">وسط</option>
                            <option value="left">يسار</option>
                            <option value="right">يمين</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">خلفية البطاقة</label>
                        <div class="flex items-center gap-2">
                            <input type="color" wire:model.live="card_bg_color" class="h-9 w-9 shrink-0 cursor-pointer rounded border border-slate-300">
                            <input type="text" wire:model.live="card_bg_color" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
                        </div>
                    </div>

                    <x-ui.input name="border_radius" type="number" label="استدارة الحواف (px)" wire:model.live="border_radius" />

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">الظل</label>
                        <select wire:model.live="shadow_style" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm">
                            <option value="none">بدون</option>
                            <option value="soft">خفيف</option>
                            <option value="medium">متوسط</option>
                            <option value="strong">قوي</option>
                        </select>
                    </div>

                    <label class="mt-6 flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" wire:model.live="card_blur" class="rounded border-slate-300 text-indigo-600">
                        تأثير ضبابي خلف البطاقة
                    </label>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="mb-4 text-sm font-semibold text-slate-600">الألوان</h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach ([
                        'title_color' => 'العنوان', 'text_color' => 'النص', 'label_color' => 'تسميات الحقول',
                        'input_bg_color' => 'خلفية الحقول', 'input_text_color' => 'نص الحقول', 'input_border_color' => 'حدود الحقول',
                        'input_focus_color' => 'تركيز الحقل', 'button_color' => 'الزر', 'button_text_color' => 'نص الزر', 'button_hover_color' => 'الزر (تمرير)',
                    ] as $field => $label)
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">{{ $label }}</label>
                            <div class="flex items-center gap-2">
                                <input type="color" wire:model.live="{{ $field }}" class="h-9 w-9 shrink-0 cursor-pointer rounded border border-slate-300">
                                <input type="text" wire:model.live="{{ $field }}" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="mb-4 text-sm font-semibold text-slate-600">المحتوى والخيارات</h2>
                <div class="space-y-4">
                    <x-ui.input name="welcome_title" label="عنوان ترحيبي (اختياري)" wire:model.live="welcome_title" placeholder="أهلًا بعودتك" />
                    <x-ui.input name="welcome_description" label="وصف ترحيبي (اختياري)" wire:model.live="welcome_description" />

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">صورة جانبية (اختياري — تظهر عند اختيار موضع يسار/يمين)</label>
                        <input type="file" wire:model="side_image" class="block w-full text-sm">
                        @if ($existingSideImage && ! $side_image)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingSideImage) }}" class="mt-2 h-10 rounded border border-slate-200 object-cover">
                        @endif
                        @error('side_image') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" wire:model.live="show_logo" class="rounded border-slate-300 text-indigo-600">
                        إظهار شعار المدرّس أعلى البطاقة
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" wire:model="remember_me_enabled" class="rounded border-slate-300 text-indigo-600">
                        السماح بخيار "تذكرني"
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" wire:model="forgot_password_enabled" class="rounded border-slate-300 text-indigo-600">
                        إظهار رابط "نسيت كلمة المرور؟"
                    </label>
                </div>
            </div>

            <x-ui.button type="submit" class="w-auto px-6">حفظ المظهر</x-ui.button>
        </form>

        <div class="lg:col-span-2">
            <div class="sticky top-6">
                <p class="mb-3 text-sm font-semibold text-slate-600">معاينة حيّة</p>

                <div
                    class="flex h-80 items-center overflow-hidden rounded-2xl border border-slate-300 p-4"
                    style="background: {{ $background_color }}; justify-content: {{ $card_position === 'left' ? 'flex-start' : ($card_position === 'right' ? 'flex-end' : 'center') }};"
                >
                    <div
                        class="w-full max-w-[220px] p-4"
                        style="
                            background: {{ $card_bg_color }};
                            opacity: {{ $card_opacity }};
                            border-radius: {{ (int) $border_radius }}px;
                            box-shadow: {{ match($shadow_style) { 'none' => 'none', 'soft' => '0 2px 8px rgba(15,23,42,.06)', 'strong' => '0 20px 50px rgba(15,23,42,.25)', default => '0 10px 25px rgba(15,23,42,.12)' } }};
                        "
                    >
                        @if ($show_logo)
                            <div class="mb-2 h-6 w-6 rounded-full" style="background: {{ $button_color }};"></div>
                        @endif

                        <p class="text-xs font-bold" style="color: {{ $title_color }};">{{ $welcome_title ?: 'تسجيل الدخول' }}</p>
                        @if ($welcome_description)
                            <p class="mt-0.5 text-[9px]" style="color: {{ $text_color }};">{{ $welcome_description }}</p>
                        @endif

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
