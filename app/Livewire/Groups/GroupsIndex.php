<?php

namespace App\Livewire\Groups;

use App\Actions\Groups\DeleteGroupAction;
use App\Actions\Groups\SaveGroupAction;
use App\Livewire\Concerns\Notifies;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Location;
use App\Models\Stage;
use App\Models\Subject;
use App\Support\Validation\BelongsToCurrentTenant;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class GroupsIndex extends Component
{
    use Notifies, WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $academic_year_id = '';

    public string $stage_id = '';

    public string $grade_id = '';

    public string $subject_id = '';

    public string $location_id = '';

    public string $monthly_price = '';

    public string $capacity = '';

    public string $start_date = '';

    public string $end_date = '';

    public string $enrollment_status = 'open';

    public string $status = 'draft';

    public bool $allow_online_registration = true;

    public string $notes = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStageId(): void
    {
        $this->grade_id = '';
    }

    public function create(): void
    {
        $this->authorize('groups.create');

        $this->reset([
            'editingId', 'name', 'academic_year_id', 'stage_id', 'grade_id', 'subject_id', 'location_id',
            'monthly_price', 'capacity', 'start_date', 'end_date', 'notes',
        ]);
        $this->enrollment_status = 'open';
        $this->status = 'draft';
        $this->allow_online_registration = true;
        $this->showForm = true;
    }

    public function edit(int $groupId): void
    {
        $this->authorize('groups.update');

        $group = Group::query()->findOrFail($groupId);
        $this->editingId = $group->id;
        $this->name = $group->name;
        $this->academic_year_id = (string) $group->academic_year_id;
        $this->stage_id = (string) $group->stage_id;
        $this->grade_id = (string) $group->grade_id;
        $this->subject_id = (string) $group->subject_id;
        $this->location_id = (string) $group->location_id;
        $this->monthly_price = (string) $group->monthly_price;
        $this->capacity = (string) $group->capacity;
        $this->start_date = (string) $group->start_date?->toDateString();
        $this->end_date = (string) $group->end_date?->toDateString();
        $this->enrollment_status = $group->enrollment_status;
        $this->status = $group->status;
        $this->allow_online_registration = $group->allow_online_registration;
        $this->notes = (string) $group->notes;
        $this->showForm = true;
    }

    public function save(SaveGroupAction $action): void
    {
        $this->authorize($this->editingId ? 'groups.update' : 'groups.create');

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'academic_year_id' => ['required', new BelongsToCurrentTenant(AcademicYear::class, 'العام الدراسي')],
            'stage_id' => ['required', new BelongsToCurrentTenant(Stage::class, 'المرحلة')],
            'grade_id' => ['required', new BelongsToCurrentTenant(Grade::class, 'الصف')],
            'subject_id' => ['required', new BelongsToCurrentTenant(Subject::class, 'المادة')],
            'location_id' => ['required', new BelongsToCurrentTenant(Location::class, 'المكان')],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'enrollment_status' => ['required', 'in:open,waiting_list,closed'],
            'status' => ['required', 'in:draft,active,paused,completed,archived'],
            'notes' => ['nullable', 'string'],
        ], [], [
            'name' => 'اسم المجموعة',
            'academic_year_id' => 'العام الدراسي',
            'stage_id' => 'المرحلة',
            'grade_id' => 'الصف',
            'subject_id' => 'المادة',
            'location_id' => 'المكان',
            'monthly_price' => 'السعر الشهري',
            'capacity' => 'السعة',
            'start_date' => 'تاريخ البداية',
            'end_date' => 'تاريخ النهاية',
        ]);

        $data['capacity'] = $data['capacity'] ?: null;
        $data['start_date'] = $data['start_date'] ?: null;
        $data['end_date'] = $data['end_date'] ?: null;
        $data['notes'] = $data['notes'] ?: null;
        $data['allow_online_registration'] = $this->allow_online_registration;

        try {
            $group = $action->execute($data, $this->editingId ? Group::query()->findOrFail($this->editingId) : null);
        } catch (ValidationException $e) {
            $this->setErrorBag($e->errors());

            return;
        }

        $this->toast($this->editingId ? 'تم تحديث المجموعة.' : "تم إنشاء المجموعة برمز {$group->code}.");
        $this->showForm = false;
    }

    public function askDelete(int $groupId): void
    {
        $group = Group::query()->findOrFail($groupId);

        $this->confirmThen(
            confirmEvent: 'group-delete-confirmed',
            title: 'حذف المجموعة؟',
            text: "سيتم حذف \"{$group->name}\" ومواعيدها نهائيًا.",
            params: ['groupId' => $groupId],
            confirmButtonText: 'حذف',
        );
    }

    #[On('group-delete-confirmed')]
    public function delete(int $groupId, DeleteGroupAction $action): void
    {
        $this->authorize('groups.delete');

        $action->execute(Group::query()->findOrFail($groupId));
        $this->toast('تم حذف المجموعة.');
    }

    public function render()
    {
        $groups = Group::query()
            ->with(['stage', 'grade', 'subject', 'location', 'academicYear'])
            ->withCount('activeEnrollments')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.groups.groups-index', [
            'groups' => $groups,
            'academicYears' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'stages' => Stage::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'grades' => $this->stage_id
                ? Grade::query()->where('stage_id', $this->stage_id)->where('is_active', true)->orderBy('sort_order')->get()
                : collect(),
            'subjects' => Subject::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'locations' => Location::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
