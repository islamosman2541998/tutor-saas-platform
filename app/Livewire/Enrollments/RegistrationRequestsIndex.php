<?php

namespace App\Livewire\Enrollments;

use App\Actions\Enrollments\ApproveRegistrationRequestAction;
use App\Actions\Enrollments\RejectRegistrationRequestAction;
use App\Livewire\Concerns\Notifies;
use App\Models\RegistrationRequest;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class RegistrationRequestsIndex extends Component
{
    use Notifies, WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public function mount(): void
    {
        $this->authorize('groups.update');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function askApprove(int $requestId): void
    {
        $request = RegistrationRequest::query()->findOrFail($requestId);

        $this->confirmThen(
            confirmEvent: 'registration-request-approve-confirmed',
            title: 'قبول طلب التسجيل؟',
            text: "سيتم تسجيل \"{$request->student_name}\" كطالب واشتراكه في \"{$request->group->name}\".",
            params: ['requestId' => $requestId],
            confirmButtonText: 'قبول',
        );
    }

    #[On('registration-request-approve-confirmed')]
    public function approve(int $requestId, ApproveRegistrationRequestAction $action): void
    {
        $this->authorize('groups.update');

        $request = RegistrationRequest::query()->findOrFail($requestId);

        try {
            $result = $action->execute($request);
        } catch (ValidationException $e) {
            $this->toast(collect($e->errors())->flatten()->first(), 'error');

            return;
        }

        $this->toast(
            $result['type'] === 'enrolled'
                ? "تم قبول الطلب وتسجيل {$request->student_name} في المجموعة."
                : "المجموعة مكتملة العدد — تم قبول الطلب وإضافة {$request->student_name} لقائمة الانتظار.",
            $result['type'] === 'enrolled' ? 'success' : 'warning',
        );
    }

    public function askReject(int $requestId): void
    {
        $request = RegistrationRequest::query()->findOrFail($requestId);

        $this->confirmThen(
            confirmEvent: 'registration-request-reject-confirmed',
            title: 'رفض طلب التسجيل؟',
            text: "سيتم رفض طلب \"{$request->student_name}\".",
            params: ['requestId' => $requestId],
            confirmButtonText: 'رفض',
        );
    }

    #[On('registration-request-reject-confirmed')]
    public function reject(int $requestId, RejectRegistrationRequestAction $action): void
    {
        $this->authorize('groups.update');

        $action->execute(RegistrationRequest::query()->findOrFail($requestId));
        $this->toast('تم رفض الطلب.');
    }

    public function render()
    {
        $requests = RegistrationRequest::query()
            ->with(['group'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('student_name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhere('guardian_phone', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.enrollments.registration-requests-index', [
            'requests' => $requests,
        ]);
    }
}
