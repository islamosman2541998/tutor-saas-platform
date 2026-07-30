<?php

namespace App\Livewire\AcademicStructure;

use App\Actions\AcademicStructure\DeleteStageAction;
use App\Exceptions\CannotDeleteException;
use App\Livewire\Concerns\Notifies;
use App\Models\Stage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StagesIndex extends Component
{
    use Notifies;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorize('settings.manage');
    }

    public function create(): void
    {
        $this->authorize('settings.manage');

        $this->reset(['editingId', 'name', 'description']);
        $this->is_active = true;
        $this->showForm = true;
    }

    public function edit(int $stageId): void
    {
        $this->authorize('settings.manage');

        $stage = Stage::query()->findOrFail($stageId);
        $this->editingId = $stage->id;
        $this->name = $stage->name;
        $this->description = (string) $stage->description;
        $this->is_active = $stage->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize('settings.manage');

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ], [], ['name' => 'اسم المرحلة']);

        $data['is_active'] = $this->is_active;

        if ($this->editingId) {
            Stage::query()->findOrFail($this->editingId)->update($data);
            $this->toast('تم تحديث المرحلة.');
        } else {
            $data['slug'] = $this->uniqueSlug($data['name']);
            $data['sort_order'] = (Stage::query()->max('sort_order') ?? 0) + 1;
            Stage::query()->create($data);
            $this->toast('تم إنشاء المرحلة.');
        }

        $this->showForm = false;
    }

    public function askDelete(int $stageId): void
    {
        $stage = Stage::query()->findOrFail($stageId);

        $this->confirmThen(
            confirmEvent: 'stage-delete-confirmed',
            title: 'حذف المرحلة؟',
            text: "سيتم حذف \"{$stage->name}\" نهائيًا إذا لم تكن مرتبطة بأي صفوف.",
            params: ['stageId' => $stageId],
            confirmButtonText: 'حذف',
        );
    }

    #[On('stage-delete-confirmed')]
    public function delete(int $stageId, DeleteStageAction $action): void
    {
        $this->authorize('settings.manage');

        try {
            $action->execute(Stage::query()->findOrFail($stageId));
            $this->toast('تم حذف المرحلة.');
        } catch (CannotDeleteException $e) {
            $this->toast($e->getMessage(), 'error');
        }
    }

    public function moveUp(int $stageId): void
    {
        $this->swapSortOrder($stageId, 'up');
    }

    public function moveDown(int $stageId): void
    {
        $this->swapSortOrder($stageId, 'down');
    }

    protected function swapSortOrder(int $stageId, string $direction): void
    {
        $this->authorize('settings.manage');

        $stages = Stage::query()->orderBy('sort_order')->get();
        $index = $stages->search(fn ($s) => $s->id === $stageId);

        $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index === false || ! $stages->has($targetIndex)) {
            return;
        }

        $current = $stages[$index];
        $target = $stages[$targetIndex];

        [$currentOrder, $targetOrder] = [$current->sort_order, $target->sort_order];
        $current->update(['sort_order' => $targetOrder]);
        $target->update(['sort_order' => $currentOrder]);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'stage';
        $slug = $base;
        $suffix = 1;

        while (Stage::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function render()
    {
        return view('livewire.academic-structure.stages-index', [
            'stages' => Stage::query()->withCount('grades')->orderBy('sort_order')->get(),
        ]);
    }
}
