<?php

namespace Novay\MiniOS\Livewire\Apps;

use Illuminate\Support\Facades\File;
use Livewire\Attributes\On;
use Livewire\Component;
use Novay\MiniOS\Concerns\HasNotifications;
use Novay\MiniOS\Concerns\HasTranslations;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Preview extends Component
{
    use HasNotifications;
    use HasTranslations;

    public ?string $filePath = null;

    public ?string $fileName = null;

    public ?string $fileType = 'empty'; // 'image' | 'pdf' | 'empty'

    public ?string $fileData = null; // Data URI for base64 or public path

    public ?string $fileSize = null;

    public ?string $extension = null;

    public int $zoom = 100; // 25% to 400%

    public int $rotation = 0; // 0, 90, 180, 270

    public bool $fitToWindow = true;

    public function mount(?string $path = null): void
    {
        if ($path) {
            $this->loadFile($path);
        }
    }

    #[On('open-file')]
    public function onOpenFile(mixed $app = null, ?string $path = null, ?string $name = null, ?string $id = null, mixed $payload = null): void
    {
        $targetApp = null;
        $targetPath = null;

        if (is_array($app)) {
            $targetApp = $app['app'] ?? $app['id'] ?? null;
            $targetPath = $app['path'] ?? null;
        } elseif (is_string($app)) {
            $targetApp = $app;
            $targetPath = $path;
        }

        if (is_array($payload)) {
            $targetApp = $targetApp ?: ($payload['app'] ?? $payload['id'] ?? null);
            $targetPath = $targetPath ?: ($payload['path'] ?? null);
        } elseif (is_string($payload)) {
            $targetPath = $targetPath ?: $payload;
        }

        if (! $targetApp && $id) {
            $targetApp = $id;
        }

        if ($targetApp === 'preview' && is_string($targetPath) && ! empty($targetPath)) {
            $this->loadFile($targetPath);
        }
    }

    #[On('open-preview')]
    public function onOpenPreview(mixed $payload = null, ?string $path = null): void
    {
        $targetPath = $path;
        if (is_array($payload)) {
            $targetPath = $targetPath ?: ($payload['path'] ?? null);
        } elseif (is_string($payload)) {
            $targetPath = $targetPath ?: $payload;
        }

        if (is_string($targetPath) && ! empty($targetPath)) {
            $this->loadFile($targetPath);
        }
    }

    public function loadFile(string $path): void
    {
        $sanitized = ltrim(str_replace(['../', '..\\'], '', $path), '/');
        $fullPath = storage_path($sanitized);
        $realPath = realpath($fullPath) ?: $fullPath;

        if (! File::exists($realPath) || File::isDirectory($realPath)) {
            $this->notify($this->trans('error_file_not_found'), 'error');

            return;
        }

        $extension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
        $basename = basename($realPath);
        $sizeBytes = File::size($realPath);

        $imageExts = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'bmp', 'ico'];

        if (in_array($extension, $imageExts, true)) {
            $this->fileType = 'image';
            $mime = $extension === 'svg' ? 'image/svg+xml' : 'image/'.$extension;
            $this->fileData = 'data:'.$mime.';base64,'.base64_encode(File::get($realPath));
        } elseif ($extension === 'pdf') {
            $this->fileType = 'pdf';
            $this->fileData = 'data:application/pdf;base64,'.base64_encode(File::get($realPath));
        } else {
            $this->fileType = 'empty';
            $this->fileData = null;
        }

        $this->filePath = $sanitized;
        $this->fileName = $basename;
        $this->extension = $extension;
        $this->fileSize = $this->formatBytes($sizeBytes);
        $this->zoom = 100;
        $this->rotation = 0;
        $this->fitToWindow = true;
    }

    public function zoomIn(): void
    {
        if ($this->zoom < 300) {
            $this->zoom = min(300, $this->zoom + 25);
            $this->fitToWindow = false;
        }
    }

    public function zoomOut(): void
    {
        if ($this->zoom > 25) {
            $this->zoom = max(25, $this->zoom - 25);
            $this->fitToWindow = false;
        }
    }

    public function resetZoom(): void
    {
        $this->zoom = 100;
        $this->fitToWindow = false;
    }

    public function toggleFitToWindow(): void
    {
        $this->fitToWindow = ! $this->fitToWindow;
        if ($this->fitToWindow) {
            $this->zoom = 100;
        }
    }

    public function rotateRight(): void
    {
        $this->rotation = ($this->rotation + 90) % 360;
    }

    public function rotateLeft(): void
    {
        $this->rotation = ($this->rotation - 90 + 360) % 360;
    }

    public function closeFile(): void
    {
        $this->filePath = null;
        $this->fileName = null;
        $this->fileType = 'empty';
        $this->fileData = null;
        $this->fileSize = null;
        $this->extension = null;
        $this->zoom = 100;
        $this->rotation = 0;
    }

    public function download(): ?BinaryFileResponse
    {
        if (! $this->filePath) {
            return null;
        }

        $fullPath = storage_path($this->filePath);
        if (! File::exists($fullPath)) {
            return null;
        }

        return response()->download($fullPath, $this->fileName);
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / 1048576, 2).' MB';
    }

    public function getAccentProperty(): array
    {
        $color = os_setting()->get('appearance.accent_color', 'indigo');

        return match ($color) {
            'zinc' => ['hex' => '#71717a', 'text' => 'text-zinc-600 dark:text-zinc-400'],
            'emerald' => ['hex' => '#10b981', 'text' => 'text-emerald-600 dark:text-emerald-400'],
            'sky' => ['hex' => '#0ea5e9', 'text' => 'text-sky-600 dark:text-sky-400'],
            'amber' => ['hex' => '#f59e0b', 'text' => 'text-amber-600 dark:text-amber-400'],
            'rose' => ['hex' => '#f43f5e', 'text' => 'text-rose-600 dark:text-rose-400'],
            'violet' => ['hex' => '#8b5cf6', 'text' => 'text-violet-600 dark:text-violet-400'],
            default => ['hex' => '#4f46e5', 'text' => 'text-indigo-600 dark:text-indigo-400'],
        };
    }

    public function render()
    {
        return view('minios::apps.preview.index', [
            'accent' => $this->accent,
        ]);
    }
}
