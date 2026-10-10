<?php

namespace Novay\MiniOS\Livewire\Apps;

use Illuminate\Support\Facades\File;
use Livewire\Attributes\On;
use Livewire\Component;
use Novay\MiniOS\Concerns\HasNotifications;
use Novay\MiniOS\Concerns\HasTranslations;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Editor extends Component
{
    use HasNotifications;
    use HasTranslations;

    public ?string $filePath = null;

    public string $fileName = 'Tanpa Judul.txt';

    public string $content = '';

    public string $originalContent = '';

    public bool $isDirty = false;

    public bool $wordWrap = true;

    public string $encoding = 'UTF-8';

    public ?string $fileSize = null;

    public function mount(?string $path = null): void
    {
        $this->fileName = $this->trans('untitled_file');
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

        if (in_array($targetApp, ['editor', 'textedit'], true) && is_string($targetPath) && ! empty($targetPath)) {
            $this->loadFile($targetPath);
        }
    }

    #[On('open-editor')]
    #[On('open-textedit')]
    public function onOpenEditor(?string $path = null, mixed $payload = null): void
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

        $content = File::get($realPath);
        $sizeBytes = File::size($realPath);

        $this->filePath = $sanitized;
        $this->fileName = basename($realPath);
        $this->content = $content;
        $this->originalContent = $content;
        $this->isDirty = false;
        $this->fileSize = $this->formatBytes($sizeBytes);

        $this->dispatch('editor-file-loaded', content: $content, fileName: $this->fileName);
    }

    public function newFile(): void
    {
        $this->filePath = null;
        $this->fileName = $this->trans('untitled_file');
        $this->content = '';
        $this->originalContent = '';
        $this->isDirty = false;
        $this->fileSize = null;

        $this->dispatch('editor-content-reset', fileName: $this->fileName);
    }

    public function updatedContent(): void
    {
        $this->isDirty = ($this->content !== $this->originalContent);
    }

    public function saveFile(): void
    {
        if (! $this->filePath) {
            $defaultName = $this->fileName ?: 'catatan_'.date('Ymd_His').'.txt';
            $this->filePath = $defaultName;
            $this->fileName = $defaultName;
        }

        $sanitized = ltrim(str_replace(['../', '..\\'], '', $this->filePath), '/');
        $fullPath = storage_path($sanitized);

        try {
            $dir = dirname($fullPath);
            if (! File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            File::put($fullPath, $this->content);
            $this->originalContent = $this->content;
            $this->isDirty = false;
            $this->fileSize = $this->formatBytes(File::size($fullPath));

            $this->notify($this->trans('success_file_saved', ['name' => $this->fileName]));
        } catch (\Exception $e) {
            $this->notify($this->trans('error_save_failed', ['error' => $e->getMessage()]), 'error');
        }
    }

    public function toggleWordWrap(): void
    {
        $this->wordWrap = ! $this->wordWrap;
    }

    public function closeFile(): void
    {
        $this->newFile();
    }

    public function download(): ?BinaryFileResponse
    {
        if ($this->filePath) {
            $fullPath = storage_path($this->filePath);
            if (File::exists($fullPath)) {
                return response()->download($fullPath, $this->fileName);
            }
        }

        // Download in-memory content
        $tempPath = tempnam(sys_get_temp_dir(), 'txt_');
        File::put($tempPath, $this->content);

        return response()->download($tempPath, $this->fileName)->deleteFileAfterSend(true);
    }

    public function getLinesCountProperty(): int
    {
        if ($this->content === '') {
            return 1;
        }

        return substr_count($this->content, "\n") + 1;
    }

    public function getCharsCountProperty(): int
    {
        return mb_strlen($this->content);
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
            'zinc' => ['hex' => '#71717a', 'text' => 'text-zinc-600 dark:text-zinc-400', 'ring' => 'focus:ring-zinc-500'],
            'emerald' => ['hex' => '#10b981', 'text' => 'text-emerald-600 dark:text-emerald-400', 'ring' => 'focus:ring-emerald-500'],
            'sky' => ['hex' => '#0ea5e9', 'text' => 'text-sky-600 dark:text-sky-400', 'ring' => 'focus:ring-sky-500'],
            'amber' => ['hex' => '#f59e0b', 'text' => 'text-amber-600 dark:text-amber-400', 'ring' => 'focus:ring-amber-500'],
            'rose' => ['hex' => '#f43f5e', 'text' => 'text-rose-600 dark:text-rose-400', 'ring' => 'focus:ring-rose-500'],
            'violet' => ['hex' => '#8b5cf6', 'text' => 'text-violet-600 dark:text-violet-400', 'ring' => 'focus:ring-violet-500'],
            default => ['hex' => '#4f46e5', 'text' => 'text-indigo-600 dark:text-indigo-400', 'ring' => 'focus:ring-indigo-500'],
        };
    }

    public function render()
    {
        return view('minios::apps.editor.index', [
            'accent' => $this->accent,
        ]);
    }
}
