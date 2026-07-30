<div>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold">الطلاب</h1>
            <p class="text-sm text-slate-500">إدارة بيانات الطلاب المسجلين</p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="ابحث بالاسم أو الكود أو الهاتف..."
                class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            >
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">كل الحالات</option>
                <option value="active">نشط</option>
                <option value="paused">موقوف</option>
                <option value="graduated">متخرج</option>
                <option value="withdrawn">منسحب</option>
                <option value="archived">مؤرشف</option>
            </select>
            <x-ui.button wire:click="create" class="w-auto px-4">+ طالب جديد</x-ui.button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[860px] text-right text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">الطالب</th>
                    <th class="px-4 py-3 font-medium">التواصل</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">تاريخ الانضمام</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($students as $student)
                    <tr wire:key="student-{{ $student->id }}">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($student->imageUrl())
                                    <img src="{{ $student->imageUrl() }}" class="h-9 w-9 rounded-full object-cover" alt="">
                                @else
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-50 text-sm font-medium text-indigo-600">
                                        {{ mb_substr($student->name, 0, 1) }}
                                    </span>
                                @endif
                                <div>
                                    <a href="{{ route('tenant.students.show', ['tenant' => $currentTenant->slug, 'student' => $student->id]) }}" class="font-medium text-slate-900 hover:text-indigo-600 hover:underline">
                                        {{ $student->name }}
                                    </a>
                                    <div class="text-xs text-slate-500">{{ $student->student_code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $student->primaryContactPhone() ?: $student->email ?: '—' }}
                            @if ($student->whatsappUrl())
                                <a href="{{ $student->whatsappUrl() }}" target="_blank" class="ms-1 text-emerald-600 hover:underline">واتساب</a>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $statusColors = ['active' => 'green', 'paused' => 'amber', 'graduated' => 'indigo', 'withdrawn' => 'red', 'archived' => 'slate'];
                            @endphp
                            <x-ui.badge :color="$statusColors[$student->status]">{{ $student->statusLabel() }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $student->joined_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="edit({{ $student->id }})" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                    تعديل
                                </button>
                                @if ($student->status === 'active')
                                    <button wire:click="askChangeStatus({{ $student->id }}, 'paused')" class="rounded-lg border border-amber-200 px-2.5 py-1.5 text-xs font-medium text-amber-600 hover:bg-amber-50">
                                        إيقاف
                                    </button>
                                @else
                                    <button wire:click="askChangeStatus({{ $student->id }}, 'active')" class="rounded-lg border border-emerald-200 px-2.5 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50">
                                        تفعيل
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-400">
                            لا يوجد طلاب بعد. أضف أول طالب لتبدأ.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $students->links() }}
    </div>

    <x-ui.modal :show="$showForm" :title="$editingId ? 'تعديل بيانات الطالب' : 'طالب جديد'" on-close="$set('showForm', false)">
        <form wire:submit="save" class="max-h-[70vh] space-y-4 overflow-y-auto pe-1">
            @if ($duplicateWarning)
                <div class="rounded-lg bg-amber-50 px-3.5 py-2.5 text-sm text-amber-800">
                    {{ $duplicateWarning }}
                </div>
            @endif

            <x-ui.input name="name" label="اسم الطالب" wire:model="name" :error="$errors->first('name')" autofocus />

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="phone" label="رقم هاتف الطالب" wire:model="phone" :error="$errors->first('phone')" />
                <x-ui.input name="email" type="email" label="البريد الإلكتروني" wire:model="email" :error="$errors->first('email')" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="guardian_name" label="اسم ولي الأمر" wire:model="guardian_name" />
                <x-ui.input name="guardian_phone" label="رقم هاتف ولي الأمر" wire:model="guardian_phone" :error="$errors->first('guardian_phone')" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="date_of_birth" type="date" label="تاريخ الميلاد (اختياري)" wire:model="date_of_birth" />
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">النوع (اختياري)</label>
                    <select wire:model="gender" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="">غير محدد</option>
                        <option value="male">ذكر</option>
                        <option value="female">أنثى</option>
                    </select>
                </div>
            </div>

            <x-ui.input name="school_name" label="اسم المدرسة (اختياري)" wire:model="school_name" />

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="governorate" label="المحافظة" wire:model="governorate" />
                <x-ui.input name="city" label="المدينة" wire:model="city" />
            </div>

            <x-ui.input name="address" label="العنوان (اختياري)" wire:model="address" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">صورة الطالب (اختياري)</label>
                <input type="file" wire:model="image" accept="image/*" class="block w-full text-sm text-slate-600">
                @error('image') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                <div wire:loading wire:target="image" class="mt-1 text-xs text-slate-400">جارٍ الرفع...</div>
                @if ($image)
                    <img src="{{ $image->temporaryUrl() }}" class="mt-2 h-16 w-16 rounded-full object-cover">
                @elseif ($existingImagePath)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingImagePath) }}" class="mt-2 h-16 w-16 rounded-full object-cover">
                @endif
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">ملاحظات (اختياري)</label>
                <textarea wire:model="notes" rows="2" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">{{ $duplicateWarning ? 'حفظ رغم ذلك' : 'حفظ' }}</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
