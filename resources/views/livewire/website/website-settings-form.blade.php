<div>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold">إعدادات الموقع التعريفي</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">الهوية والألوان والتخطيط — وحالة نشر الموقع للزوار</p>
        </div>

        @if ($currentTenant->slug)
            <a href="{{ route('tenant.website.home', ['tenant' => $currentTenant->slug]) }}" target="_blank" class="rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                معاينة الموقع ↗
            </a>
        @endif
    </div>

    <div class="mb-6 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
        <h2 class="mb-3 text-sm font-semibold text-slate-600 dark:text-slate-400">حالة النشر</h2>
        <div class="flex flex-wrap items-center gap-3">
            @php
                $statusColors = ['draft' => 'slate', 'published' => 'green', 'maintenance' => 'amber'];
                $statusLabels = ['draft' => 'مسودة (غير مرئي للزوار)', 'published' => 'منشور', 'maintenance' => 'تحت الصيانة'];
            @endphp
            <x-ui.badge :color="$statusColors[$website_status]">{{ $statusLabels[$website_status] }}</x-ui.badge>

            <div class="flex gap-2">
                @if ($website_status !== 'published')
                    <button wire:click="setStatus('published')" class="rounded-lg border border-emerald-300 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-50">نشر الموقع</button>
                @endif
                @if ($website_status !== 'maintenance')
                    <button wire:click="setStatus('maintenance')" class="rounded-lg border border-amber-300 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50">وضع الصيانة</button>
                @endif
                @if ($website_status !== 'draft')
                    <button wire:click="setStatus('draft')" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">إرجاع لمسودة</button>
                @endif
            </div>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
            <h2 class="mb-4 text-sm font-semibold text-slate-600 dark:text-slate-400">الهوية</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.input name="site_name" label="اسم الموقع" wire:model="site_name" :error="$errors->first('site_name')" />

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">الشعار (Logo)</label>
                    <label
                        for="site-logo"
                        class="group relative flex h-20 cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 transition hover:border-indigo-400 hover:bg-indigo-50/50 dark:border-slate-600 dark:bg-slate-900/30 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/10"
                    >
                        @if ($logo)
                            <img src="{{ $logo->temporaryUrl() }}" class="absolute inset-0 h-full w-full object-contain p-2">
                        @elseif ($existingLogo)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingLogo) }}" class="absolute inset-0 h-full w-full object-contain p-2">
                        @endif

                        @if ($logo || $existingLogo)
                            <div class="absolute inset-0 flex items-center justify-center bg-slate-900/0 opacity-0 transition group-hover:bg-slate-900/50 group-hover:opacity-100">
                                <span class="rounded-lg bg-white/90 px-3 py-1.5 text-xs font-medium text-slate-700">تغيير</span>
                            </div>
                        @else
                            <div class="flex flex-col items-center gap-1 text-slate-400 dark:text-slate-500">
                                <x-ui.icon name="image" class="h-5 w-5" />
                                <span class="text-xs font-medium">اضغط لاختيار صورة</span>
                            </div>
                        @endif

                        <input id="site-logo" type="file" wire:model="logo" accept="image/*" class="sr-only">
                    </label>
                    <div wire:loading wire:target="logo" class="mt-1 text-xs text-slate-400 dark:text-slate-500">جارٍ الرفع...</div>
                    @error('logo') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">الفافيكون (Favicon)</label>
                    <label
                        for="site-favicon"
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

                        <input id="site-favicon" type="file" wire:model="favicon" accept="image/*" class="sr-only">
                    </label>
                    <div wire:loading wire:target="favicon" class="mt-1 text-xs text-slate-400 dark:text-slate-500">جارٍ الرفع...</div>
                    @error('favicon') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">صورة المدرّس</label>
                    <label
                        for="teacher-image"
                        class="group relative flex h-20 w-20 cursor-pointer items-center justify-center overflow-hidden rounded-full border-2 border-dashed border-slate-300 bg-slate-50 transition hover:border-indigo-400 hover:bg-indigo-50/50 dark:border-slate-600 dark:bg-slate-900/30 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/10"
                    >
                        @if ($teacher_image)
                            <img src="{{ $teacher_image->temporaryUrl() }}" class="absolute inset-0 h-full w-full object-cover">
                        @elseif ($existingTeacherImage)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingTeacherImage) }}" class="absolute inset-0 h-full w-full object-cover">
                        @endif

                        @if ($teacher_image || $existingTeacherImage)
                            <div class="absolute inset-0 flex items-center justify-center bg-slate-900/0 opacity-0 transition group-hover:bg-slate-900/50 group-hover:opacity-100">
                                <span class="rounded-lg bg-white/90 px-2 py-1 text-[11px] font-medium text-slate-700">تغيير</span>
                            </div>
                        @else
                            <div class="flex flex-col items-center gap-1 text-slate-400 dark:text-slate-500">
                                <x-ui.icon name="image" class="h-5 w-5" />
                            </div>
                        @endif

                        <input id="teacher-image" type="file" wire:model="teacher_image" accept="image/*" class="sr-only">
                    </label>
                    <div wire:loading wire:target="teacher_image" class="mt-1 text-xs text-slate-400 dark:text-slate-500">جارٍ الرفع...</div>
                    @error('teacher_image') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-4">
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">وصف مختصر</label>
                <textarea wire:model="short_description" rows="2" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
                @error('short_description') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
            <h2 class="mb-4 text-sm font-semibold text-slate-600 dark:text-slate-400">الألوان</h2>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                @foreach ([
                    'primary_color' => 'الأساسي', 'secondary_color' => 'الثانوي', 'accent_color' => 'مميز',
                    'background_color' => 'الخلفية', 'text_color' => 'النص', 'heading_color' => 'العناوين',
                    'button_color' => 'الأزرار', 'button_hover_color' => 'الأزرار (تمرير)', 'navbar_color' => 'الشريط العلوي', 'footer_color' => 'الفوتر',
                ] as $field => $label)
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700 dark:text-slate-300">{{ $label }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" wire:model="{{ $field }}" class="h-9 w-9 shrink-0 cursor-pointer rounded border border-slate-300 dark:border-slate-600">
                            <input type="text" wire:model="{{ $field }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 px-2 py-1.5 text-xs focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        </div>
                        @error($field) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
            <h2 class="mb-4 text-sm font-semibold text-slate-600 dark:text-slate-400">التخطيط والخطوط</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-ui.input name="font_family" label="نوع الخط" wire:model="font_family" :error="$errors->first('font_family')" />
                <x-ui.input name="base_font_size" type="number" label="حجم الخط الأساسي (px)" wire:model="base_font_size" :error="$errors->first('base_font_size')" />
                <x-ui.input name="heading_font_size" type="number" label="حجم خط العناوين (px)" wire:model="heading_font_size" :error="$errors->first('heading_font_size')" />
                <x-ui.input name="border_radius" type="number" label="استدارة الحواف (px)" wire:model="border_radius" :error="$errors->first('border_radius')" />

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">شكل الأزرار</label>
                    <select wire:model="button_style" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="rounded">مستديرة الحواف</option>
                        <option value="pill">كبسولة</option>
                        <option value="square">مربعة</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">تباعد الأقسام</label>
                    <select wire:model="section_spacing" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="compact">مضغوط</option>
                        <option value="normal">عادي</option>
                        <option value="relaxed">مريح</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">عرض المحتوى</label>
                    <select wire:model="container_width" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="narrow">ضيق</option>
                        <option value="normal">عادي</option>
                        <option value="wide">واسع</option>
                    </select>
                </div>

                <label class="mt-6 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                    <input type="checkbox" wire:model="card_shadow" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                    ظل خفيف على البطاقات
                </label>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
            <h2 class="mb-4 text-sm font-semibold text-slate-600 dark:text-slate-400">محتوى وضع الصيانة</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">رسالة الصيانة</label>
                    <textarea wire:model="maintenance_message" rows="2" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="الموقع قيد التحديث حاليًا، برجاء المحاولة لاحقًا."></textarea>
                    @error('maintenance_message') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">صورة الصيانة (اختياري)</label>
                    <label
                        for="maintenance-image"
                        class="group relative flex h-24 cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 transition hover:border-indigo-400 hover:bg-indigo-50/50 dark:border-slate-600 dark:bg-slate-900/30 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/10"
                    >
                        @if ($maintenance_image)
                            <img src="{{ $maintenance_image->temporaryUrl() }}" class="absolute inset-0 h-full w-full object-contain p-2">
                        @elseif ($existingMaintenanceImage)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingMaintenanceImage) }}" class="absolute inset-0 h-full w-full object-contain p-2">
                        @endif

                        @if ($maintenance_image || $existingMaintenanceImage)
                            <div class="absolute inset-0 flex items-center justify-center bg-slate-900/0 opacity-0 transition group-hover:bg-slate-900/50 group-hover:opacity-100">
                                <span class="rounded-lg bg-white/90 px-3 py-1.5 text-xs font-medium text-slate-700">تغيير الصورة</span>
                            </div>
                        @else
                            <div class="flex flex-col items-center gap-1.5 text-slate-400 dark:text-slate-500">
                                <x-ui.icon name="image" class="h-6 w-6" />
                                <span class="text-xs font-medium">اضغط لاختيار صورة</span>
                            </div>
                        @endif

                        <input id="maintenance-image" type="file" wire:model="maintenance_image" accept="image/*" class="sr-only">
                    </label>
                    <div wire:loading wire:target="maintenance_image" class="mt-1 text-xs text-slate-400 dark:text-slate-500">جارٍ الرفع...</div>
                    @error('maintenance_image') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <x-ui.button type="submit" class="w-auto px-6">حفظ الإعدادات</x-ui.button>
        </div>
    </form>
</div>
