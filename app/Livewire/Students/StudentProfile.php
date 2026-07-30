<?php

namespace App\Livewire\Students;

use App\Actions\Enrollments\TransferEnrollmentAction;
use App\Livewire\Concerns\Notifies;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Student;
use App\Support\Validation\BelongsToCurrentTenant;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StudentProfile extends Component
{
    use Notifies;

    public Student $student;

    public bool $showTransferForm = false;

    public ?int $transferringEnrollmentId = null;

    public string $to_group_id = '';

    public function mount(Student $student): void
    {
        $this->authorize('students.view');
        $this->student = $student;
    }

    public function openTransferForm(int $enrollmentId): void
    {
        $this->authorize('students.update');

        $this->transferringEnrollmentId = $enrollmentId;
        $this->to_group_id = '';
        $this->showTransferForm = true;
    }

    public function transfer(TransferEnrollmentAction $action): void
    {
        $this->authorize('students.update');

        $data = $this->validate([
            'to_group_id' => ['required', new BelongsToCurrentTenant(Group::class, 'المجموعة الجديدة')],
        ], [], ['to_group_id' => 'المجموعة الجديدة']);

        $enrollment = $this->student->enrollments()->findOrFail($this->transferringEnrollmentId);
        $toGroup = Group::query()->findOrFail($data['to_group_id']);

        if ($toGroup->id === $enrollment->group_id) {
            $this->addError('to_group_id', 'اختر مجموعة مختلفة عن المجموعة الحالية.');

            return;
        }

        try {
            $result = $action->execute($enrollment, $toGroup, []);
        } catch (ValidationException $e) {
            // Livewire only serialises memo.errors for keys matching an
            // actual public property — the Action's error key ("group_id")
            // isn't one here, so setErrorBag() would silently vanish.
            $this->toast(collect($e->errors())->flatten()->first(), 'error');

            return;
        }

        $this->toast(
            $result['type'] === 'enrolled'
                ? "تم نقل الطالب إلى \"{$toGroup->name}\"."
                : "تم نقل الطالب لقائمة انتظار \"{$toGroup->name}\" (المجموعة مكتملة)."
        );

        $this->showTransferForm = false;
    }

    public function render()
    {
        return view('livewire.students.student-profile', [
            'enrollments' => $this->student->enrollments()->with('group')->latest()->get(),
            'groups' => Group::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }
}
