<?php

namespace App\Livewire\AcademicStructure;

use App\Actions\AcademicStructure\DeleteGradeAction;
use App\Exceptions\CannotDeleteException;
use App\Livewire\Concerns\Notifies;
use App\Models\Grade;
use App\Models\Stage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class GradesIndex extends Component
{
    use Notifies;

    public Stage $stage;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public bool $is_active = true;

    public function mount(Stage $stage): void
    {
        $this->authorize('settings.manage');
        $this->stage = $stage;
    }

    public function create(): void
    {
        $this->authorize('settings.manage');

        $this->reset(['editingId', 'name']);
        $this->is_active = true;
        $this->showForm = true;
    }

    public function edit(int $gradeId): void
    {
        $this->authorize('settings.manage');

        $grade = $this->stage->grades()->findOrFail($gradeId);
        $this->editingId = $grade->id;
        $this->name = $grade->name;
        $this->is_active = $grade->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize('settings.manage');

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [], ['name' => 'اسم الصف']);

        $data['is_active'] = $this->is_active;

        if ($this->editingId) {
            $this->stage->grades()->findOrFail($this->editingId)->update($data);
            $this->toast('تم تحديث الصف.');
        } else {
            $data['slug'] = $this->uniqueSlug($data['name']);
            $data['sort_order'] = ($this->stage->grades()->max('sort_order') ?? 0) + 1;
            $this->stage->grades()->create($data);
            $this->toast('تم إنشاء الصف.');
        }

        $this->showForm = false;
    }

    public function askDelete(int $gradeId): void
    {
        $grade = $this->stage->grades()->findOrFail($gradeId);

        $this->confirmThen(
            confirmEvent: 'grade-delete-confirmed',
            title: 'حذف الصف؟',
            text: "سيتم حذف \"{$grade->name}\" نهائيًا.",
            params: ['gradeId' => $gradeId],
            confirmButtonText: 'حذف',
        );
    }

    #[On('grade-delete-confirmed')]
    public function delete(int $gradeId, DeleteGradeAction $action): void
    {
        $this->authorize('settings.manage');

        try {
            $action->execute($this->stage->grades()->findOrFail($gradeId));
            $this->toast('تم حذف الصف.');
        } catch (CannotDeleteException $e) {
            $this->toast($e->getMessage(), 'error');
        }
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'grade';
        $slug = $base;
        $suffix = 1;

        while (Grade::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function render()
    {
        return view('livewire.academic-structure.grades-index', [
            'grades' => $this->stage->grades()->orderBy('sort_order')->get(),
        ]);
    }
}
