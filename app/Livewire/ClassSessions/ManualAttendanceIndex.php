<?php

namespace App\Livewire\ClassSessions;

use App\Actions\ClassSessions\SaveManualAttendanceAction;
use App\Exceptions\CannotPerformActionException;
use App\Livewire\Concerns\Notifies;
use App\Models\ClassSession;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ManualAttendanceIndex extends Component
{
    use Notifies;

    public ClassSession $session;

    public string $search = '';

    /** @var array<int, string> student_id => status */
    public array $statuses = [];

    /** @var array<int, string> student_id => existing registration_method, for the QR badge */
    public array $methods = [];

    public function mount(ClassSession $session): void
    {
        $this->authorize('attendance.record');
        $this->session = $session;

        $existing = $session->attendanceRecords()->get()->keyBy('student_id');

        foreach ($session->group->activeEnrollments()->with('student')->get() as $enrollment) {
            $record = $existing->get($enrollment->student_id);
            $this->statuses[$enrollment->student_id] = $record?->status ?? 'unmarked';
            $this->methods[$enrollment->student_id] = $record?->registration_method ?? null;
        }
    }

    public function setStatus(int $studentId, string $status): void
    {
        $this->authorize('attendance.record');
        $this->statuses[$studentId] = $status;
    }

    public function selectAllVisible(): void
    {
        $this->authorize('attendance.record');

        foreach ($this->visibleStudentIds() as $studentId) {
            $this->statuses[$studentId] = 'present';
        }
    }

    public function clearAllVisible(): void
    {
        $this->authorize('attendance.record');

        foreach ($this->visibleStudentIds() as $studentId) {
            $this->statuses[$studentId] = 'unmarked';
        }
    }

    protected function visibleStudentIds(): array
    {
        return $this->session->group->activeEnrollments()
            ->whereHas('student', fn ($q) => $this->search
                ? $q->where('name', 'like', "%{$this->search}%")->orWhere('student_code', 'like', "%{$this->search}%")
                : $q)
            ->with('student')->get()
            ->pluck('student_id')
            ->all();
    }

    public function askSave(): void
    {
        $unmarkedCount = collect($this->statuses)->filter(fn ($s) => $s === 'unmarked')->count();

        if ($unmarkedCount > 0) {
            $this->confirmThen(
                confirmEvent: 'attendance-save-confirmed',
                title: 'اعتماد الحضور؟',
                text: "سيتم اعتبار {$unmarkedCount} طالبًا غير محدد الحالة كـ\"غائب\". هل تريد المتابعة؟",
                confirmButtonText: 'اعتماد وحفظ',
            );

            return;
        }

        $this->save();
    }

    #[On('attendance-save-confirmed')]
    public function save(SaveManualAttendanceAction $action = null): void
    {
        $this->authorize('attendance.record');

        $action ??= app(SaveManualAttendanceAction::class);

        $toSave = collect($this->statuses)
            ->map(fn ($status) => $status === 'unmarked' ? 'absent' : $status)
            ->all();

        try {
            $action->execute($this->session, $toSave);
        } catch (CannotPerformActionException $e) {
            $this->toast($e->getMessage(), 'error');

            return;
        }

        $this->statuses = $toSave;
        $this->toast('تم حفظ الحضور.');
    }

    public function render()
    {
        $enrollments = $this->session->group->activeEnrollments()
            ->with('student')
            ->when($this->search, fn ($q) => $q->whereHas('student', fn ($q2) => $q2
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('student_code', 'like', "%{$this->search}%")))
            ->get();

        return view('livewire.class-sessions.manual-attendance-index', [
            'enrollments' => $enrollments,
        ]);
    }
}
