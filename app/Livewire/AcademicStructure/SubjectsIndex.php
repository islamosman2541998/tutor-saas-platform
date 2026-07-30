<?php

namespace App\Livewire\AcademicStructure;

use App\Actions\AcademicStructure\DeleteSubjectAction;
use App\Exceptions\CannotDeleteException;
use App\Livewire\Concerns\Notifies;
use App\Models\Subject;
use App\Support\Files\TenantUploads;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class SubjectsIndex extends Component
{
    use Notifies, WithFileUploads;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public string $icon = '';

    public bool $is_active = true;

    public $image = null;

    public ?string $existingImagePath = null;

    public function mount(): void
    {
        $this->authorize('settings.manage');
    }

    public function create(): void
    {
        $this->authorize('settings.manage');

        $this->reset(['editingId', 'name', 'description', 'icon', 'image', 'existingImagePath']);
        $this->is_active = true;
        $this->showForm = true;
    }

    public function edit(int $subjectId): void
    {
        $this->authorize('settings.manage');

        $subject = Subject::query()->findOrFail($subjectId);
        $this->editingId = $subject->id;
        $this->name = $subject->name;
        $this->description = (string) $subject->description;
        $this->icon = (string) $subject->icon;
        $this->is_active = $subject->is_active;
        $this->existingImagePath = $subject->image;
        $this->image = null;
        $this->showForm = true;
    }

    public function save(TenantUploads $uploads): void
    {
        $this->authorize('settings.manage');

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:10'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [], [
            'name' => 'اسم المادة',
            'image' => 'الصورة',
        ]);

        unset($data['image']);
        $data['is_active'] = $this->is_active;

        if ($this->editingId) {
            $subject = Subject::query()->findOrFail($this->editingId);

            if ($this->image) {
                $data['image'] = $uploads->replace($subject->image, $this->image, 'subjects');
            }

            $subject->update($data);
            $this->toast('تم تحديث المادة.');
        } else {
            if ($this->image) {
                $data['image'] = $uploads->store($this->image, 'subjects');
            }

            $data['slug'] = $this->uniqueSlug($data['name']);
            Subject::query()->create($data);
            $this->toast('تم إنشاء المادة.');
        }

        $this->showForm = false;
    }

    public function askDelete(int $subjectId): void
    {
        $subject = Subject::query()->findOrFail($subjectId);

        $this->confirmThen(
            confirmEvent: 'subject-delete-confirmed',
            title: 'حذف المادة؟',
            text: "سيتم حذف \"{$subject->name}\" نهائيًا.",
            params: ['subjectId' => $subjectId],
            confirmButtonText: 'حذف',
        );
    }

    #[On('subject-delete-confirmed')]
    public function delete(int $subjectId, DeleteSubjectAction $action): void
    {
        $this->authorize('settings.manage');

        try {
            $action->execute(Subject::query()->findOrFail($subjectId));
            $this->toast('تم حذف المادة.');
        } catch (CannotDeleteException $e) {
            $this->toast($e->getMessage(), 'error');
        }
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'subject';
        $slug = $base;
        $suffix = 1;

        while (Subject::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function render()
    {
        return view('livewire.academic-structure.subjects-index', [
            'subjects' => Subject::query()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }
}
