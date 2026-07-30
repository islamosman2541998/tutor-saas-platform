<?php

namespace App\Livewire\Enrollments;

use App\Actions\Enrollments\ConvertWaitlistEntryAction;
use App\Actions\Enrollments\EnrollStudentAction;
use App\Actions\Enrollments\WithdrawEnrollmentAction;
use App\Livewire\Concerns\Notifies;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Student;
use App\Models\WaitingListEntry;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class GroupEnrollmentsIndex extends Component
{
    use Notifies;

    public Group $group;

    public string $studentSearch = '';

    public bool $showEnrollForm = false;

    public ?int $selectedStudentId = null;

    public string $custom_monthly_price = '';

    public string $discount_type = 'none';

    public string $discount_value = '0';

    public function mount(Group $group): void
    {
        $this->authorize('groups.update');
        $this->group = $group;
    }

    public function updatedStudentSearch(): void
    {
        $this->selectedStudentId = null;
    }

    public function pickStudent(int $studentId): void
    {
        $this->selectedStudentId = $studentId;
        $this->studentSearch = Student::query()->findOrFail($studentId)->name;
    }

    public function openEnrollForm(): void
    {
        $this->authorize('groups.update');

        $this->reset(['studentSearch', 'selectedStudentId', 'custom_monthly_price', 'discount_value']);
        $this->discount_type = 'none';
        $this->showEnrollForm = true;
    }

    public function enroll(EnrollStudentAction $action): void
    {
        $this->authorize('groups.update');

        $this->validate([
            'selectedStudentId' => ['required'],
            'custom_monthly_price' => ['nullable', 'numeric', 'min:0'],
            'discount_type' => ['required', 'in:none,fixed,percentage'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
        ], [
            'selectedStudentId.required' => 'اختر طالبًا من نتائج البحث أولًا.',
        ]);

        $student = Student::query()->findOrFail($this->selectedStudentId);

        try {
            $result = $action->execute($student, $this->group, [
                'custom_monthly_price' => $this->custom_monthly_price ?: null,
                'discount_type' => $this->discount_type,
                'discount_value' => $this->discount_value ?: 0,
            ]);
        } catch (ValidationException $e) {
            // Livewire only serialises memo.errors for keys that match an
            // actual public property (SupportValidation::dehydrate) — the
            // Action's errors (e.g. "group_id") aren't real properties on
            // this component, so setErrorBag() would silently vanish. A
            // toast sidesteps that entirely.
            $this->toast(collect($e->errors())->flatten()->first(), 'error');

            return;
        }

        $this->toast(
            $result['type'] === 'enrolled'
                ? "تم اشتراك {$student->name} في المجموعة."
                : "المجموعة مكتملة العدد — تمت إضافة {$student->name} لقائمة الانتظار.",
            $result['type'] === 'enrolled' ? 'success' : 'warning',
        );

        $this->showEnrollForm = false;
    }

    public function askWithdraw(int $enrollmentId): void
    {
        $enrollment = $this->group->enrollments()->findOrFail($enrollmentId);

        $this->confirmThen(
            confirmEvent: 'enrollment-withdraw-confirmed',
            title: 'إنهاء اشتراك الطالب؟',
            text: "سيصبح اشتراك \"{$enrollment->student->name}\" في هذه المجموعة منسحبًا.",
            params: ['enrollmentId' => $enrollmentId],
            confirmButtonText: 'تأكيد الانسحاب',
        );
    }

    #[On('enrollment-withdraw-confirmed')]
    public function withdraw(int $enrollmentId, WithdrawEnrollmentAction $action): void
    {
        $this->authorize('groups.update');

        $action->execute($this->group->enrollments()->findOrFail($enrollmentId));
        $this->toast('تم إنهاء اشتراك الطالب.');
    }

    public function convertFromWaitlist(int $waitingEntryId, ConvertWaitlistEntryAction $action): void
    {
        $this->authorize('groups.update');

        $entry = WaitingListEntry::query()->findOrFail($waitingEntryId);

        try {
            $result = $action->execute($entry, []);
        } catch (ValidationException $e) {
            $this->toast(collect($e->errors())->flatten()->first(), 'error');

            return;
        }

        if ($result['type'] === 'enrolled') {
            $this->toast("تم تحويل {$entry->student->name} من قائمة الانتظار للمجموعة.");
        } else {
            $this->toast('المجموعة لا تزال ممتلئة.', 'warning');
        }
    }

    public function render()
    {
        return view('livewire.enrollments.group-enrollments-index', [
            'enrollments' => $this->group->activeEnrollments()->with('student')->latest()->get(),
            'waitingList' => $this->group->waitingListEntries()->where('status', 'waiting')->with('student')->oldest('requested_at')->get(),
            'searchResults' => $this->studentSearch && ! $this->selectedStudentId
                ? Student::query()
                    ->where(function ($q) {
                        $q->where('name', 'like', "%{$this->studentSearch}%")
                            ->orWhere('student_code', 'like', "%{$this->studentSearch}%");
                    })
                    ->limit(8)
                    ->get()
                : collect(),
        ]);
    }
}
