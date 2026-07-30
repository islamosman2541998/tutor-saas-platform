<?php

namespace App\Livewire\AcademicYears;

use App\Actions\AcademicYears\CloseAcademicYearAction;
use App\Actions\AcademicYears\CreateAcademicYearAction;
use App\Actions\AcademicYears\SetCurrentAcademicYearAction;
use App\Livewire\Concerns\Notifies;
use App\Models\AcademicYear;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AcademicYearsIndex extends Component
{
    use Notifies;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $start_date = '';

    public string $end_date = '';

    public string $notes = '';

    public bool $make_current = false;

    public function mount(): void
    {
        $this->authorize('settings.manage');
    }

    public function create(): void
    {
        $this->authorize('settings.manage');

        $this->reset(['editingId', 'name', 'start_date', 'end_date', 'notes', 'make_current']);
        $this->showForm = true;
    }

    public function save(CreateAcademicYearAction $action): void
    {
        $this->authorize('settings.manage');

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'notes' => ['nullable', 'string'],
        ], [], [
            'name' => 'اسم العام الدراسي',
            'start_date' => 'تاريخ البداية',
            'end_date' => 'تاريخ النهاية',
        ]);

        $data['make_current'] = $this->make_current;

        $action->execute($data);

        $this->toast('تم إنشاء العام الدراسي.');
        $this->showForm = false;
    }

    public function askSetCurrent(int $yearId): void
    {
        $year = AcademicYear::query()->findOrFail($yearId);

        $this->confirmThen(
            confirmEvent: 'year-set-current-confirmed',
            title: 'تحديد كعام دراسي حالي؟',
            text: "سيصبح \"{$year->name}\" هو العام الدراسي الحالي، وستُنشأ عليه المجموعات والاشتراكات الجديدة.",
            params: ['yearId' => $yearId],
            confirmButtonText: 'تحديد',
        );
    }

    #[On('year-set-current-confirmed')]
    public function setCurrent(int $yearId, SetCurrentAcademicYearAction $action): void
    {
        $this->authorize('settings.manage');

        $action->execute(AcademicYear::query()->findOrFail($yearId));
        $this->toast('تم تحديد العام الدراسي الحالي.');
    }

    public function askClose(int $yearId): void
    {
        $year = AcademicYear::query()->findOrFail($yearId);

        $this->confirmThen(
            confirmEvent: 'year-close-confirmed',
            title: 'إغلاق العام الدراسي؟',
            text: 'تظل كل البيانات (المجموعات، الطلاب، المدفوعات) متاحة للقراءة والتقارير، لكن لن يمكن إضافة بيانات جديدة عليه.',
            params: ['yearId' => $yearId],
            confirmButtonText: 'إغلاق',
        );
    }

    #[On('year-close-confirmed')]
    public function close(int $yearId, CloseAcademicYearAction $action): void
    {
        $this->authorize('settings.manage');

        $action->execute(AcademicYear::query()->findOrFail($yearId));
        $this->toast('تم إغلاق العام الدراسي.');
    }

    public function render()
    {
        return view('livewire.academic-years.index', [
            'years' => AcademicYear::query()->orderByDesc('start_date')->get(),
        ]);
    }
}
