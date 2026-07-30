<?php

namespace App\Livewire\ClassSessions;

use App\Actions\ClassSessions\CancelSessionAction;
use App\Actions\ClassSessions\CreateManualSessionAction;
use App\Actions\ClassSessions\GenerateSessionsFromScheduleAction;
use App\Actions\ClassSessions\UpdateSessionAction;
use App\Exceptions\CannotPerformActionException;
use App\Livewire\Concerns\Notifies;
use App\Models\ClassSession;
use App\Models\Group;
use Carbon\CarbonImmutable;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ClassSessionsIndex extends Component
{
    use Notifies;

    public Group $group;

    public string $weeksAhead = '4';

    public bool $showManualForm = false;

    public ?int $editingId = null;

    public string $scheduled_date = '';

    public string $expected_start_time = '';

    public string $expected_end_time = '';

    public string $title = '';

    public string $lesson_topic = '';

    public string $notes = '';

    public bool $showCancelForm = false;

    public ?int $cancellingSessionId = null;

    public string $cancellation_reason = '';

    public function mount(Group $group): void
    {
        $this->authorize('groups.update');
        $this->group = $group;
    }

    public function generate(GenerateSessionsFromScheduleAction $action): void
    {
        $this->authorize('groups.update');

        $from = CarbonImmutable::today();
        $to = $from->addWeeks((int) $this->weeksAhead);

        $created = $action->execute($this->group, $from, $to);

        $this->toast($created > 0 ? "تم إنشاء {$created} حصة جديدة." : 'كل الحصص في هذه الفترة موجودة بالفعل.');
    }

    public function openManualForm(): void
    {
        $this->authorize('groups.update');

        $this->reset(['editingId', 'scheduled_date', 'expected_start_time', 'expected_end_time', 'title', 'lesson_topic', 'notes']);
        $this->showManualForm = true;
    }

    public function edit(int $sessionId): void
    {
        $this->authorize('groups.update');

        $session = $this->group->classSessions()->findOrFail($sessionId);
        $this->editingId = $session->id;
        $this->scheduled_date = $session->scheduled_date->toDateString();
        $this->expected_start_time = substr($session->expected_start_time, 0, 5);
        $this->expected_end_time = substr($session->expected_end_time, 0, 5);
        $this->title = (string) $session->title;
        $this->lesson_topic = (string) $session->lesson_topic;
        $this->notes = (string) $session->notes;
        $this->showManualForm = true;
    }

    public function save(CreateManualSessionAction $createAction, UpdateSessionAction $updateAction): void
    {
        $this->authorize('groups.update');

        $data = $this->validate([
            'scheduled_date' => ['required', 'date'],
            'expected_start_time' => ['required', 'date_format:H:i'],
            'expected_end_time' => ['required', 'date_format:H:i', 'after:expected_start_time'],
            'title' => ['nullable', 'string', 'max:255'],
            'lesson_topic' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ], [
            'expected_end_time.after' => 'وقت النهاية يجب أن يكون بعد وقت البداية.',
        ], [
            'scheduled_date' => 'تاريخ الحصة',
            'expected_start_time' => 'وقت البداية',
            'expected_end_time' => 'وقت النهاية',
        ]);

        try {
            if ($this->editingId) {
                $updateAction->execute($this->group->classSessions()->findOrFail($this->editingId), $data);
                $this->toast('تم تحديث الحصة.');
            } else {
                $createAction->execute($this->group, $data);
                $this->toast('تم إنشاء الحصة.');
            }
        } catch (CannotPerformActionException $e) {
            $this->toast($e->getMessage(), 'error');

            return;
        }

        $this->showManualForm = false;
    }

    public function openCancelForm(int $sessionId): void
    {
        $this->authorize('groups.update');

        $this->cancellingSessionId = $sessionId;
        $this->cancellation_reason = '';
        $this->showCancelForm = true;
    }

    public function cancel(CancelSessionAction $action): void
    {
        $this->authorize('groups.update');

        $data = $this->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ], [], ['cancellation_reason' => 'سبب الإلغاء']);

        try {
            $action->execute($this->group->classSessions()->findOrFail($this->cancellingSessionId), $data['cancellation_reason']);
            $this->toast('تم إلغاء الحصة.');
        } catch (CannotPerformActionException $e) {
            $this->toast($e->getMessage(), 'error');

            return;
        }

        $this->showCancelForm = false;
    }

    public function render()
    {
        return view('livewire.class-sessions.class-sessions-index', [
            'sessions' => $this->group->classSessions()->orderBy('scheduled_date')->orderBy('expected_start_time')->get(),
        ]);
    }
}
