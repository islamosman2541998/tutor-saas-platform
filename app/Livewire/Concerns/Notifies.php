<?php

namespace App\Livewire\Concerns;

/**
 * Thin wrapper around the browser-event contract resources/js/app.js listens
 * for (see `Livewire.on('toast', ...)` / `Livewire.on('confirm', ...)`),
 * so components don't hand-roll dispatch() calls with the exact payload
 * shape each time.
 */
trait Notifies
{
    protected function toast(string $message, string $type = 'success'): void
    {
        $this->dispatch('toast', message: $message, type: $type);
    }

    protected function confirmThen(string $confirmEvent, string $title, string $text = '', array $params = [], string $confirmButtonText = 'تأكيد'): void
    {
        $this->dispatch(
            'confirm',
            title: $title,
            text: $text,
            confirmButtonText: $confirmButtonText,
            confirmEvent: $confirmEvent,
            params: $params,
        );
    }
}
