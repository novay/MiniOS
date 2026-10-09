<?php

namespace Novay\MiniOS\Concerns;

trait HasNotifications
{
    /**
     * Dispatch a success notification to Toast Hub and Notification Center.
     */
    public function success(string $message, ?string $heading = null): void
    {
        $this->notify($message, $heading, 'success');
    }

    /**
     * Dispatch an error notification to Toast Hub and Notification Center.
     */
    public function error(string $message, ?string $heading = null): void
    {
        $this->notify($message, $heading, 'danger');
    }

    /**
     * Dispatch a warning notification to Toast Hub and Notification Center.
     */
    public function warning(string $message, ?string $heading = null): void
    {
        $this->notify($message, $heading, 'warning');
    }

    /**
     * Dispatch an info notification to Toast Hub and Notification Center.
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
     * Dispatch notification with MiniOS global event to Windows 11 Toast Hub & Notification Center.
     */
    public function notify(string $message, ?string $heading = null, string $variant = 'success', int $duration = 5000): void
    {
        // Support shorthand parameter order: notify($message, 'error'|'warning'|'info'|'success')
        if (in_array($heading, ['success', 'danger', 'error', 'warning', 'info'], true) && $variant === 'success') {
            $variant = $heading;
            $heading = null;
        }

        $normalizedVariant = match ($variant) {
            'error', 'danger' => 'danger',
            'warning' => 'warning',
            'info' => 'info',
            default => 'success',
        };

        $type = match ($normalizedVariant) {
            'danger' => 'error',
            default => $normalizedVariant,
        };

        $appInfo = $this->resolveNotificationApp();

        $this->dispatch('os-notify', [
            'id' => uniqid('notif_', true),
            'title' => $heading ?: '',
            'message' => $message,
            'text' => $message,
            'type' => $type,
            'variant' => $normalizedVariant,
            'app' => $appInfo['name'] ?? 'MiniOS',
            'app_id' => $appInfo['id'] ?? null,
            'icon' => $appInfo['icon'] ?? null,
            'icon_url' => $appInfo['icon_url'] ?? null,
            'duration' => $duration,
            'time' => now()->format('H:i'),
            'show_toast' => true,
        ]);
    }

    /**
     * Resolve application metadata (id, name, icon) from current component.
     *
     * @return array{id: string, name: string, icon: string, icon_url?: string|null}
     */
    protected function resolveNotificationApp(): array
    {
        $id = null;

        // 1. If component explicitly defines $appId or appId()
        if (property_exists($this, 'appId') && is_string($this->appId) && ! empty($this->appId)) {
            $id = $this->appId;
        } elseif (method_exists($this, 'appId')) {
            $id = $this->appId();
        } else {
            // 2. Match registered apps by component class
            $currentClass = static::class;
            $allApps = config('desktop.applications', []);

            foreach ($allApps as $appKey => $appConfig) {
                if (($appConfig['component'] ?? null) === $currentClass) {
                    $id = $appKey;
                    break;
                }
            }

            // 3. Fallback: match by class basename
            if (! $id) {
                $base = class_basename($currentClass);
                $kebab = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $base));
                if (isset($allApps[$kebab])) {
                    $id = $kebab;
                }
            }
        }

        if ($id && isset(config('desktop.applications')[$id])) {
            $app = config('desktop.applications')[$id];

            return [
                'id' => $id,
                'name' => $app['name'] ?? ucfirst($id),
                'icon' => $app['icon'] ?? $id,
                'icon_url' => $app['icon_url'] ?? null,
            ];
        }

        return [
            'id' => $id ?? 'minios',
            'name' => $id ? ucfirst($id) : 'MiniOS',
            'icon' => $id ?? 'minios',
            'icon_url' => null,
        ];
    }
}
