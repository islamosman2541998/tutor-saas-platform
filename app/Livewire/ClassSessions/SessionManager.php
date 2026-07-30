<?php

namespace App\Livewire\ClassSessions;

use App\Actions\ClassSessions\CloseClassSessionAction;
use App\Actions\ClassSessions\ExtendQrValidityAction;
use App\Actions\ClassSessions\RegenerateQrAction;
use App\Actions\ClassSessions\StartClassSessionAction;
use App\Actions\ClassSessions\StopQrAction;
use App\Exceptions\CannotPerformActionException;
use App\Livewire\Concerns\Notifies;
use App\Models\ClassSession;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class SessionManager extends Component
{
    use Notifies;

    public ClassSession $session;

    public function mount(ClassSession $session): void
    {
        $this->authorize('attendance.record');
        $this->session = $session;
    }

    public function start(StartClassSessionAction $action): void
    {
        $this->authorize('attendance.record');
        $this->runOrToastError(fn () => $action->execute($this->session));
    }

    public function regenerateQr(RegenerateQrAction $action): void
    {
        $this->authorize('attendance.record');
        $this->runOrToastError(fn () => $action->execute($this->session), 'تم توليد رمز جديد — الرمز السابق لم يعد صالحًا.');
    }

    public function extendQr(ExtendQrValidityAction $action): void
    {
        $this->authorize('attendance.record');
        $this->runOrToastError(fn () => $action->execute($this->session, 10), 'تم تمديد صلاحية الرمز 10 دقائق.');
    }

    public function stopQr(StopQrAction $action): void
    {
        $this->authorize('attendance.record');
        $this->session = $action->execute($this->session);
        $this->toast('تم إيقاف رمز الحضور.');
    }

    public function close(CloseClassSessionAction $action): void
    {
        $this->authorize('attendance.unlock');
        $this->runOrToastError(fn () => $action->execute($this->session), 'تم إغلاق الحصة.');
    }

    protected function runOrToastError(\Closure $callback, ?string $successMessage = null): void
    {
        try {
            $this->session = $callback();

            if ($successMessage) {
                $this->toast($successMessage);
            }
        } catch (CannotPerformActionException $e) {
            $this->toast($e->getMessage(), 'error');
        }
    }

    public function render()
    {
        $this->session->refresh();

        return view('livewire.class-sessions.session-manager', [
            'attendanceUrl' => $this->session->qr_token
                ? route('attend', ['token' => $this->session->qr_token])
                : null,
            'records' => $this->session->attendanceRecords()->with('student')->get(),
        ]);
    }
}
