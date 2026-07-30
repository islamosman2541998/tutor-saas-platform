<?php

namespace App\Livewire\Groups;

use App\Actions\Groups\DeleteGroupScheduleAction;
use App\Actions\Groups\SaveGroupScheduleAction;
use App\Livewire\Concerns\Notifies;
use App\Models\Group;
use App\Models\Location;
use App\Support\Validation\BelongsToCurrentTenant;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class GroupSchedulesIndex extends Component
{
    use Notifies;

    public Group $group;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $day_of_week = '0';

    public string $start_time = '';

    public string $end_time = '';

    public string $location_id = '';

    public function mount(Group $group): void
    {
        $this->authorize('groups.update');
        $this->group = $group;
    }

    public function create(): void
    {
        $this->authorize('groups.update');

        $this->reset(['editingId', 'start_time', 'end_time', 'location_id']);
        $this->day_of_week = '0';
        $this->showForm = true;
    }

    public function edit(int $scheduleId): void
    {
        $this->authorize('groups.update');

        $schedule = $this->group->schedules()->findOrFail($scheduleId);
        $this->editingId = $schedule->id;
        $this->day_of_week = (string) $schedule->day_of_week;
        $this->start_time = substr($schedule->start_time, 0, 5);
        $this->end_time = substr($schedule->end_time, 0, 5);
        $this->location_id = (string) $schedule->location_id;
        $this->showForm = true;
    }

    public function save(SaveGroupScheduleAction $action): void
    {
        $this->authorize('groups.update');

        $data = $this->validate([
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'location_id' => ['nullable', new BelongsToCurrentTenant(Location::class, 'المكان')],
        ], [
            'end_time.after' => 'وقت النهاية يجب أن يكون بعد وقت البداية.',
        ], [
            'day_of_week' => 'اليوم',
            'start_time' => 'وقت البداية',
            'end_time' => 'وقت النهاية',
            'location_id' => 'المكان',
        ]);

        $data['location_id'] = $data['location_id'] ?: null;

        $existing = $this->editingId ? $this->group->schedules()->findOrFail($this->editingId) : null;

        $result = $action->execute($this->group, $data, $existing);

        if ($result['conflict_warning']) {
            $this->toast($result['conflict_warning'], 'warning');
        } else {
            $this->toast($this->editingId ? 'تم تحديث الموعد.' : 'تم إضافة الموعد.');
        }

        $this->showForm = false;
    }

    public function askDelete(int $scheduleId): void
    {
        $this->confirmThen(
            confirmEvent: 'schedule-delete-confirmed',
            title: 'حذف الموعد؟',
            text: 'سيتم حذف هذا الموعد من جدول المجموعة.',
            params: ['scheduleId' => $scheduleId],
            confirmButtonText: 'حذف',
        );
    }

    #[On('schedule-delete-confirmed')]
    public function delete(int $scheduleId, DeleteGroupScheduleAction $action): void
    {
        $this->authorize('groups.update');

        $action->execute($this->group->schedules()->findOrFail($scheduleId));
        $this->toast('تم حذف الموعد.');
    }

    public function render()
    {
        return view('livewire.groups.group-schedules-index', [
            'schedules' => $this->group->schedules()->orderBy('day_of_week')->orderBy('start_time')->get(),
            'locations' => Location::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
