<?php

namespace App\Livewire\Students;

use App\Actions\Students\CreateStudentAction;
use App\Actions\Students\SetStudentStatusAction;
use App\Actions\Students\UpdateStudentAction;
use App\Livewire\Concerns\Notifies;
use App\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class StudentsIndex extends Component
{
    use Notifies, WithFileUploads, WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $guardian_name = '';

    public string $guardian_phone = '';

    public string $guardian_email = '';

    public string $date_of_birth = '';

    public string $gender = '';

    public string $school_name = '';

    public string $governorate = '';

    public string $city = '';

    public string $address = '';

    public string $notes = '';

    public $image = null;

    public ?string $existingImagePath = null;

    public ?string $duplicateWarning = null;

    public bool $confirmDuplicate = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorize('students.create');

        $this->reset([
            'editingId', 'name', 'phone', 'email', 'guardian_name', 'guardian_phone', 'guardian_email',
            'date_of_birth', 'gender', 'school_name', 'governorate', 'city', 'address', 'notes',
            'image', 'existingImagePath', 'duplicateWarning', 'confirmDuplicate',
        ]);
        $this->showForm = true;
    }

    public function edit(int $studentId): void
    {
        $this->authorize('students.update');

        $student = Student::query()->findOrFail($studentId);
        $this->editingId = $student->id;
        $this->name = $student->name;
        $this->phone = (string) $student->phone;
        $this->email = (string) $student->email;
        $this->guardian_name = (string) $student->guardian_name;
        $this->guardian_phone = (string) $student->guardian_phone;
        $this->guardian_email = (string) $student->guardian_email;
        $this->date_of_birth = (string) $student->date_of_birth?->toDateString();
        $this->gender = (string) $student->gender;
        $this->school_name = (string) $student->school_name;
        $this->governorate = (string) $student->governorate;
        $this->city = (string) $student->city;
        $this->address = (string) $student->address;
        $this->notes = (string) $student->notes;
        $this->existingImagePath = $student->image;
        $this->image = null;
        $this->duplicateWarning = null;
        $this->confirmDuplicate = false;
        $this->showForm = true;
    }

    public function save(CreateStudentAction $createAction, UpdateStudentAction $updateAction): void
    {
        $this->authorize($this->editingId ? 'students.update' : 'students.create');

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'governorate' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [], [
            'name' => 'اسم الطالب',
            'phone' => 'رقم الهاتف',
            'email' => 'البريد الإلكتروني',
            'guardian_phone' => 'رقم ولي الأمر',
        ]);

        if (! $this->phone && ! $this->guardian_phone && ! $this->email) {
            $this->addError('phone', 'أدخل رقم هاتف الطالب أو ولي الأمر أو بريدًا إلكترونيًا للتواصل.');

            return;
        }

        $image = $this->image;
        unset($data['image']);

        // Livewire's update requests skip Laravel's ConvertEmptyStringsToNull
        // middleware, so untouched optional fields arrive as '' rather than
        // null. That's harmless for plain VARCHAR columns but MySQL rejects
        // '' outright for date_of_birth (DATE) and gender (ENUM).
        foreach (['date_of_birth', 'gender'] as $nullableField) {
            $data[$nullableField] = $data[$nullableField] ?: null;
        }

        if (! $this->editingId && ! $this->confirmDuplicate) {
            $duplicate = $this->findDuplicate();

            if ($duplicate) {
                $this->duplicateWarning = "يوجد طالب مشابه بالفعل: \"{$duplicate->name}\" ({$duplicate->student_code}). اضغط \"حفظ رغم ذلك\" إذا كنت متأكدًا أنه طالب مختلف.";
                // The form's submit button is the same wire:submit="save" —
                // clicking "حفظ رغم ذلك" re-runs this method, so the *next*
                // call must skip this check instead of showing the warning
                // forever.
                $this->confirmDuplicate = true;

                return;
            }
        }

        if ($this->editingId) {
            $updateAction->execute(Student::query()->findOrFail($this->editingId), $data, $image);
            $this->toast('تم تحديث بيانات الطالب.');
        } else {
            $student = $createAction->execute($data, $image);
            $this->toast("تم إضافة الطالب برمز {$student->student_code}.");
        }

        $this->showForm = false;
    }

    protected function findDuplicate(): ?Student
    {
        return Student::query()
            ->where(function ($q) {
                if ($this->phone) {
                    $q->orWhere('phone', $this->phone);
                }
                if ($this->guardian_phone) {
                    $q->orWhere('guardian_phone', $this->guardian_phone);
                }
                if ($this->phone || $this->guardian_phone) {
                    $q->orWhere(function ($q2) {
                        $q2->where('name', $this->name)
                            ->where(function ($q3) {
                                $q3->where('phone', $this->phone)->orWhere('guardian_phone', $this->guardian_phone);
                            });
                    });
                }
            })
            ->first();
    }

    public function askChangeStatus(int $studentId, string $newStatus): void
    {
        $student = Student::query()->findOrFail($studentId);

        $this->confirmThen(
            confirmEvent: 'student-status-confirmed',
            title: 'تغيير حالة الطالب؟',
            text: "سيتم تغيير حالة \"{$student->name}\" إلى \"".Student::statusLabelFor($newStatus)."\".",
            params: ['studentId' => $studentId, 'newStatus' => $newStatus],
            confirmButtonText: 'تأكيد',
        );
    }

    #[On('student-status-confirmed')]
    public function changeStatus(int $studentId, string $newStatus, SetStudentStatusAction $action): void
    {
        $this->authorize('students.update');

        $action->execute(Student::query()->findOrFail($studentId), $newStatus);
        $this->toast('تم تحديث حالة الطالب.');
    }

    public function render()
    {
        $students = Student::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('student_code', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhere('guardian_phone', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.students.students-index', ['students' => $students]);
    }
}
