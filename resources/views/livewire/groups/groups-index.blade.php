<div>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold">المجموعات</h1>
            <p class="text-sm text-slate-500">مجموعات الدروس الخصوصية ومواعيدها</p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="ابحث بالاسم أو الكود..."
                class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            >
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">كل الحالات</option>
                <option value="draft">مسودة</option>
                <option value="active">نشطة</option>
                <option value="paused">متوقفة</option>
                <option value="completed">مكتملة</option>
                <option value="archived">مؤرشفة</option>
            </select>
            <x-ui.button wire:click="create" class="w-auto px-4">+ مجموعة جديدة</x-ui.button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[900px] text-right text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">المجموعة</th>
                    <th class="px-4 py-3 font-medium">المادة / الصف</th>
                    <th class="px-4 py-3 font-medium">المكان</th>
                    <th class="px-4 py-3 font-medium">الطلاب</th>
                    <th class="px-4 py-3 font-medium">السعر</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($groups as $group)
                    <tr wire:key="group-{{ $group->id }}">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $group->name }}</div>
                            <div class="text-xs text-slate-500">{{ $group->code }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $group->subject->name }} — {{ $group->grade->name }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $group->location->name }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $group->active_enrollments_count }}{{ $group->capacity ? ' / '.$group->capacity : '' }}
                            @if ($group->capacity && $group->active_enrollments_count >= $group->capacity)
                                <x-ui.badge color="amber">مكتملة</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $group->monthly_price }}</td>
                        <td class="px-4 py-3">
                            @php
                                $statusColors = ['draft' => 'slate', 'active' => 'green', 'paused' => 'amber', 'completed' => 'indigo', 'archived' => 'slate'];
                                $statusLabels = ['draft' => 'مسودة', 'active' => 'نشطة', 'paused' => 'متوقفة', 'completed' => 'مكتملة', 'archived' => 'مؤرشفة'];
                            @endphp
                            <x-ui.badge :color="$statusColors[$group->status]">{{ $statusLabels[$group->status] }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('tenant.groups.students', ['tenant' => $currentTenant->slug, 'group' => $group->id]) }}" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                    الطلاب
                                </a>
                                <a href="{{ route('tenant.groups.schedules', ['tenant' => $currentTenant->slug, 'group' => $group->id]) }}" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                    المواعيد
                                </a>
                                <a href="{{ route('tenant.groups.sessions', ['tenant' => $currentTenant->slug, 'group' => $group->id]) }}" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                    الحصص
                                </a>
                                <button wire:click="edit({{ $group->id }})" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                    تعديل
                                </button>
                                <button wire:click="askDelete({{ $group->id }})" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                    حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-400">
                            لم تُنشئ أي مجموعة بعد. أنشئ مجموعتك الأولى وحدد المادة والصف والمواعيد.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $groups->links() }}
    </div>

    <x-ui.modal :show="$showForm" :title="$editingId ? 'تعديل المجموعة' : 'مجموعة جديدة'" on-close="$set('showForm', false)">
        <form wire:submit="save" class="max-h-[70vh] space-y-4 overflow-y-auto pe-1">
            <x-ui.input name="name" label="اسم المجموعة" wire:model="name" :error="$errors->first('name')" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">العام الدراسي</label>
                <select wire:model="academic_year_id" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">اختر عام دراسي</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
                @error('academic_year_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">المرحلة</label>
                    <select wire:model.live="stage_id" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="">اختر مرحلة</option>
                        @foreach ($stages as $stage)
                            <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                        @endforeach
                    </select>
                    @error('stage_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">الصف</label>
                    <select wire:model="grade_id" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" @if(!$stage_id) disabled @endif>
                        <option value="">{{ $stage_id ? 'اختر صفًا' : 'اختر مرحلة أولًا' }}</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                        @endforeach
                    </select>
                    @error('grade_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">المادة</label>
                    <select wire:model="subject_id" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="">اختر مادة</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    @error('subject_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">المكان</label>
                    <select wire:model="location_id" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="">اختر مكانًا</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                    @error('location_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="monthly_price" type="number" label="السعر الشهري" wire:model="monthly_price" :error="$errors->first('monthly_price')" step="0.01" />
                <x-ui.input name="capacity" type="number" label="السعة (اختياري)" wire:model="capacity" :error="$errors->first('capacity')" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="start_date" type="date" label="تاريخ البداية (اختياري)" wire:model="start_date" />
                <x-ui.input name="end_date" type="date" label="تاريخ النهاية (اختياري)" wire:model="end_date" :error="$errors->first('end_date')" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">حالة الاشتراك بالمجموعة</label>
                    <select wire:model="enrollment_status" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="open">مفتوح</option>
                        <option value="waiting_list">قائمة انتظار</option>
                        <option value="closed">مغلق</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">حالة المجموعة</label>
                    <select wire:model="status" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="draft">مسودة</option>
                        <option value="active">نشطة</option>
                        <option value="paused">متوقفة</option>
                        <option value="completed">مكتملة</option>
                        <option value="archived">مؤرشفة</option>
                    </select>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" wire:model="allow_online_registration" class="rounded border-slate-300 text-indigo-600">
                السماح بالتسجيل أونلاين من الموقع التعريفي
            </label>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">ملاحظات (اختياري)</label>
                <textarea wire:model="notes" rows="2" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
