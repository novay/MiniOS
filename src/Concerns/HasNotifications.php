<?php

namespace Novay\MiniOS\Concerns;

use Flux\Flux;

trait HasNotifications
{
    /**
     * Dispatch a success notification to Toast and Notification Center.
     */
    public function success(string $message, ?string $heading = null): void
    {
        $this->notify($message, $heading, 'success');
    }

    /**
     * Dispatch an error notification to Toast and Notification Center.
     */
    public function error(string $message, ?string $heading = null): void
    {
        $this->notify($message, $heading, 'danger');
    }

    /**
     * Dispatch a warning notification to Toast and Notification Center.
     */
    public function warning(string $message, ?string $heading = null): void
    {
        $this->notify($message, $heading, 'warning');
    }

    /**
     * Dispatch an info notification to Toast and Notification Center.
     */
    public function info(string $message, ?string $heading = null): void
    {
        $this->notify($message, $heading, 'info');
    }

    /**
     * Shorthand alias for notify().
     */
    public function toast(string $message, ?string $heading = null, string $variant = 'success', int $duration = 5000): void
    {
        $this->notify($message, $heading, $variant, $duration);
    }

    /**
     * Dispatch notification with Flux UI Toast and MiniOS global event.
     */
    public function notify(string $message, ?string $heading = null, string $variant = 'success', int $duration = 5000): void
    {
        $normalizedVariant = match ($variant) {
            'error', 'danger' => 'danger',
            'warning' => 'warning',
            'info' => 'info',
            default => 'success',
        };

        // 1. Trigger Flux Toast UI if installed
        if (class_exists(Flux::class)) {
            $position = function_exists('os_setting')
                ? os_setting('notifications.position', 'bottom end')
                : 'bottom end';

            Flux::toast(
                text: $message,
                heading: $heading,
                duration: $duration,
                variant: $normalizedVariant,
                position: $position
            );
        }

        // 2. Dispatch global Livewire browser event for Notification Center & Sound Chime
        $type = match ($normalizedVariant) {
            'danger' => 'error',
            default => $normalizedVariant,
        };

        $this->dispatch('os-notify', [
            'id' => uniqid('notif_', true),
            'title' => $heading ?: match ($type) {
                'error' => 'Error',
                'warning' => 'Warning',
                'info' => 'Info',
                default => 'Success',
            },
            'message' => $message,
            'text' => $message,
            'type' => $type,
            'variant' => $normalizedVariant,
            'time' => now()->format('H:i'),
        ]);
    }
}
