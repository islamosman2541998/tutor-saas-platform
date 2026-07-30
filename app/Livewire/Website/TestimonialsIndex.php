<?php

namespace App\Livewire\Website;

use App\Actions\Website\DeleteTestimonialAction;
use App\Actions\Website\SaveTestimonialAction;
use App\Livewire\Concerns\Notifies;
use App\Models\Testimonial;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class TestimonialsIndex extends Component
{
    use Notifies, WithFileUploads;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $student_name = '';

    public string $content = '';

    public string $rating = '5';

    public string $grade_or_group = '';

    public bool $is_featured = false;

    public bool $is_active = true;

    public $student_image = null;

    public ?string $existingImage = null;

    public function mount(): void
    {
        $this->authorize('website.manage');
    }

    public function create(): void
    {
        $this->authorize('website.manage');

        $this->reset(['editingId', 'student_name', 'content', 'grade_or_group', 'is_featured', 'student_image', 'existingImage']);
        $this->rating = '5';
        $this->is_active = true;
        $this->showForm = true;
    }

    public function edit(int $testimonialId): void
    {
        $this->authorize('website.manage');

        $testimonial = Testimonial::query()->findOrFail($testimonialId);
        $this->editingId = $testimonial->id;
        $this->student_name = $testimonial->student_name;
        $this->content = $testimonial->content;
        $this->rating = (string) ($testimonial->rating ?? 5);
        $this->grade_or_group = (string) $testimonial->grade_or_group;
        $this->is_featured = $testimonial->is_featured;
        $this->is_active = $testimonial->is_active;
        $this->existingImage = $testimonial->student_image;
        $this->student_image = null;
        $this->showForm = true;
    }

    public function save(SaveTestimonialAction $action): void
    {
        $this->authorize('website.manage');

        $testimonial = $this->editingId ? Testimonial::query()->findOrFail($this->editingId) : null;

        $data = $this->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:1000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'grade_or_group' => ['nullable', 'string', 'max:255'],
            'student_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [], [
            'student_name' => 'اسم الطالب',
            'content' => 'نص الرأي',
        ]);

        $image = $this->student_image;
        unset($data['student_image']);

        $data['grade_or_group'] = $data['grade_or_group'] ?: null;
        $data['is_featured'] = $this->is_featured;
        $data['is_active'] = $this->is_active;

        $action->execute($data, $testimonial, $image);

        $this->toast($testimonial ? 'تم تحديث الرأي.' : 'تم إضافة الرأي.');
        $this->showForm = false;
    }

    public function askDelete(int $testimonialId): void
    {
        $testimonial = Testimonial::query()->findOrFail($testimonialId);

        $this->confirmThen(
            confirmEvent: 'testimonial-delete-confirmed',
            title: 'حذف رأي الطالب؟',
            text: "سيتم حذف رأي \"{$testimonial->student_name}\" نهائيًا.",
            params: ['testimonialId' => $testimonialId],
            confirmButtonText: 'حذف',
        );
    }

    #[On('testimonial-delete-confirmed')]
    public function delete(int $testimonialId, DeleteTestimonialAction $action): void
    {
        $this->authorize('website.manage');

        $action->execute(Testimonial::query()->findOrFail($testimonialId));
        $this->toast('تم حذف الرأي.');
    }

    public function moveUp(int $testimonialId): void
    {
        $this->swapSortOrder($testimonialId, 'up');
    }

    public function moveDown(int $testimonialId): void
    {
        $this->swapSortOrder($testimonialId, 'down');
    }

    protected function swapSortOrder(int $testimonialId, string $direction): void
    {
        $this->authorize('website.manage');

        $testimonials = Testimonial::query()->orderBy('sort_order')->get();
        $index = $testimonials->search(fn ($t) => $t->id === $testimonialId);

        $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index === false || ! $testimonials->has($targetIndex)) {
            return;
        }

        $current = $testimonials[$index];
        $target = $testimonials[$targetIndex];

        [$currentOrder, $targetOrder] = [$current->sort_order, $target->sort_order];
        $current->update(['sort_order' => $targetOrder]);
        $target->update(['sort_order' => $currentOrder]);
    }

    public function render()
    {
        return view('livewire.website.testimonials-index', [
            'testimonials' => Testimonial::query()->orderBy('sort_order')->get(),
        ]);
    }
}
