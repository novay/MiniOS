<?php

namespace Novay\MiniOS\Livewire\Apps;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Files extends Component
{
    use WithFileUploads;

    public string $currentPath = '';

    public string $viewMode = 'grid';

    // Cloud Storage Modal & Info State
    public bool $showCloudStorageModal = false;

    public ?string $cloudStorageTestStatus = null;

    public ?string $cloudStorageTestError = null;

    public string $searchQuery = '';

    public array $history = [''];

    public int $historyIndex = 0;

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    public ?string $selectedPath = null;

    public ?string $statusMessage = null;

    public string $statusType = 'success';

    // New Folder Modal State
    public bool $showNewFolderModal = false;

    public string $newFolderName = '';

    // New File Modal State
    public bool $showNewFileModal = false;

    public string $newFileName = '';

    // Upload Files Modal State
    public bool $showUploadModal = false;

    public array $uploadedFiles = [];

    // Rename Modal State
    public bool $showRenameModal = false;

    public string $renameTargetPath = '';

    public string $renameTargetName = '';

    public string $renameNewName = '';

    // Delete Confirmation Modal State
    public bool $showDeleteModal = false;

    public string $deleteTargetPath = '';

    public string $deleteTargetName = '';

    public bool $deleteIsDirectory = false;

    // Preview / Text Editor State
    public ?array $previewItem = null;

    public ?string $previewContent = null;

    public function mount(string $path = ''): void
    {
        $this->currentPath = $this->sanitizePath($path);
        $this->history = [$this->currentPath];
        $this->historyIndex = 0;
    }

    public function navigate(string $path): void
    {
        $sanitized = $this->sanitizePath($path);
        if ($sanitized !== $this->currentPath) {
            $this->currentPath = $sanitized;
            $this->history = array_slice($this->history, 0, $this->historyIndex + 1);
            $this->history[] = $this->currentPath;
            $this->historyIndex = count($this->history) - 1;
        }
        $this->searchQuery = '';
    }

    public function navigateUp(): void
    {
        if (empty($this->currentPath)) {
            return;
        }

        $parts = explode('/', str_replace('\\', '/', $this->currentPath));
        array_pop($parts);
        $newPath = implode('/', array_filter($parts));
        $this->navigate($newPath);
    }

    public function goBack(): void
    {
        if ($this->historyIndex > 0) {
            $this->historyIndex--;
            $this->currentPath = $this->history[$this->historyIndex];
            $this->searchQuery = '';
        }
    }

    public function goForward(): void
    {
        if ($this->historyIndex < count($this->history) - 1) {
            $this->historyIndex++;
            $this->currentPath = $this->history[$this->historyIndex];
            $this->searchQuery = '';
        }
    }

    public function getCanGoBackProperty(): bool
    {
        return $this->historyIndex > 0;
    }

    public function getCanGoForwardProperty(): bool
    {
        return $this->historyIndex < count($this->history) - 1;
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['grid', 'list'])) {
            $this->viewMode = $mode;
        }
    }

    protected function sanitizePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path), '/');
        $parts = array_filter(explode('/', $path), function ($part) {
            return $part !== '' && $part !== '.' && $part !== '..';
        });

        return implode('/', $parts);
    }

    public function getAbsolutePathProperty(): string
    {
        $base = storage_path();
        if (empty($this->currentPath)) {
            return $base;
        }

        $fullPath = $base.'/'.$this->currentPath;
        $realPath = realpath($fullPath) ?: $fullPath;
        if (! str_starts_with($realPath, $base)) {
            return $base;
        }

        return $fullPath;
    }

    public function getBreadcrumbsProperty(): array
    {
        $breadcrumbs = [
            ['name' => 'storage', 'path' => ''],
        ];

        if (empty($this->currentPath)) {
            return $breadcrumbs;
        }

        $accumulated = '';
        foreach (explode('/', $this->currentPath) as $part) {
            if ($part === '') {
                continue;
            }
            $accumulated = $accumulated === '' ? $part : $accumulated.'/'.$part;
            $breadcrumbs[] = [
                'name' => $part,
                'path' => $accumulated,
            ];
        }

        return $breadcrumbs;
    }

    public function getIsLockedProperty(): bool
    {
        return ! auth()->check();
    }

    public function getItemsProperty(): array
    {
        if ($this->isLocked) {
            return [];
        }

        $absPath = $this->absolutePath;

        if (! File::exists($absPath) || ! File::isDirectory($absPath)) {
            return [];
        }

        $dirs = [];
        $files = [];

        if ($this->searchQuery !== '') {
            $query = strtolower(trim($this->searchQuery));
            $basePath = storage_path();

            try {
                // Recursive search in all subdirectories
                $allDirs = File::allDirectories($basePath);
                foreach ($allDirs as $dir) {
                    $basename = basename($dir);
                    if (str_contains(strtolower($basename), $query)) {
                        $rel = ltrim(substr($dir, strlen($basePath)), '/\\');
                        $relPath = str_replace('\\', '/', $rel);
                        $childCount = count(File::directories($dir)) + count(File::files($dir));
                        $dirs[] = [
                            'name' => $basename,
                            'path' => $relPath,
                            'location' => dirname($relPath) === '.' ? 'storage' : dirname($relPath),
                            'is_dir' => true,
                            'size' => "{$childCount} item".($childCount !== 1 ? 's' : ''),
                            'bytes' => 0,
                            'extension' => 'folder',
                            'updated_at' => date('d M Y, H:i', File::lastModified($dir)),
                            'raw_mtime' => File::lastModified($dir),
                        ];
                    }
                    if (count($dirs) >= 100) {
                        break;
                    }
                }

                // Recursive search in all files
                $allFiles = File::allFiles($basePath);
                foreach ($allFiles as $file) {
                    $basename = $file->getFilename();
                    if (str_contains(strtolower($basename), $query)) {
                        $rel = ltrim(substr($file->getPathname(), strlen($basePath)), '/\\');
                        $relPath = str_replace('\\', '/', $rel);
                        $extension = strtolower($file->getExtension());
                        $sizeBytes = $file->getSize();
                        $files[] = [
                            'name' => $basename,
                            'path' => $relPath,
                            'location' => dirname($relPath) === '.' ? 'storage' : dirname($relPath),
                            'is_dir' => false,
                            'size' => $this->formatBytes($sizeBytes),
                            'bytes' => $sizeBytes,
                            'extension' => $extension ?: 'file',
                            'updated_at' => date('d M Y, H:i', $file->getMTime()),
                            'raw_mtime' => $file->getMTime(),
                        ];
                    }
                    if (count($files) >= 150) {
                        break;
                    }
                }
            } catch (\Exception $e) {
                return [];
            }
        } else {
            try {
                $contents = File::directories($absPath);
                foreach ($contents as $dir) {
                    $basename = basename($dir);
                    $relativePath = empty($this->currentPath) ? $basename : $this->currentPath.'/'.$basename;
                    $childCount = count(File::directories($dir)) + count(File::files($dir));
                    $dirs[] = [
                        'name' => $basename,
                        'path' => $relativePath,
                        'location' => $this->currentPath ?: 'storage',
                        'is_dir' => true,
                        'size' => "{$childCount} item".($childCount !== 1 ? 's' : ''),
                        'bytes' => 0,
                        'extension' => 'folder',
                        'updated_at' => date('d M Y, H:i', File::lastModified($dir)),
                        'raw_mtime' => File::lastModified($dir),
                    ];
                }

                $fileContents = File::files($absPath);
                foreach ($fileContents as $file) {
                    $basename = $file->getFilename();
                    $relativePath = empty($this->currentPath) ? $basename : $this->currentPath.'/'.$basename;
                    $extension = strtolower($file->getExtension());
                    $sizeBytes = $file->getSize();
                    $files[] = [
                        'name' => $basename,
                        'path' => $relativePath,
                        'location' => $this->currentPath ?: 'storage',
                        'is_dir' => false,
                        'size' => $this->formatBytes($sizeBytes),
                        'bytes' => $sizeBytes,
                        'extension' => $extension ?: 'file',
                        'updated_at' => date('d M Y, H:i', $file->getMTime()),
                        'raw_mtime' => $file->getMTime(),
                    ];
                }
            } catch (\Exception $e) {
                return [];
            }
        }

        $sorter = function ($a, $b) {
            $valA = match ($this->sortBy) {
                'size' => $a['bytes'] ?? 0,
                'updated_at' => $a['raw_mtime'] ?? 0,
                default => strtolower($a['name']),
            };
            $valB = match ($this->sortBy) {
                'size' => $b['bytes'] ?? 0,
                'updated_at' => $b['raw_mtime'] ?? 0,
                default => strtolower($b['name']),
            };

            if ($valA == $valB) {
                return 0;
            }

            $res = ($valA < $valB) ? -1 : 1;

            return $this->sortDirection === 'desc' ? -$res : $res;
        };

        usort($dirs, $sorter);
        usort($files, $sorter);

        return array_merge($dirs, $files);
    }

    public function selectItem(?string $path): void
    {
        $this->selectedPath = $this->selectedPath === $path ? null : $path;
    }

    public function notify(string $message, string $type = 'success'): void
    {
        $this->statusMessage = $message;
        $this->statusType = $type;
    }

    public function clearNotification(): void
    {
        $this->statusMessage = null;
    }

    public function openNewFolderModal(): void
    {
        $this->newFolderName = '';
        $this->showNewFolderModal = true;
    }

    public function closeNewFolderModal(): void
    {
        $this->showNewFolderModal = false;
        $this->newFolderName = '';
    }

    public function createFolder(): void
    {
        if ($this->isLocked) {
            return;
        }

        $name = trim($this->newFolderName);
        if ($name === '' || str_contains($name, '/') || str_contains($name, '\\') || $name === '.' || $name === '..') {
            $this->notify('Nama folder tidak valid.', 'error');

            return;
        }

        $targetDir = $this->absolutePath.'/'.$name;
        if (File::exists($targetDir)) {
            $this->notify('Folder dengan nama tersebut sudah ada.', 'error');

            return;
        }

        try {
            File::makeDirectory($targetDir, 0755, true);
            $this->notify("Folder '{$name}' berhasil dibuat.");
            $this->closeNewFolderModal();
        } catch (\Exception $e) {
            $this->notify('Gagal membuat folder: '.$e->getMessage(), 'error');
        }
    }

    public function openNewFileModal(): void
    {
        $this->newFileName = '';
        $this->showNewFileModal = true;
    }

    public function closeNewFileModal(): void
    {
        $this->showNewFileModal = false;
        $this->newFileName = '';
    }

    public function createFile(): void
    {
        if ($this->isLocked) {
            return;
        }

        $name = trim($this->newFileName);
        if ($name === '' || str_contains($name, '/') || str_contains($name, '\\') || $name === '.' || $name === '..') {
            $this->notify('Nama berkas tidak valid.', 'error');

            return;
        }

        $targetFile = $this->absolutePath.'/'.$name;
        if (File::exists($targetFile)) {
            $this->notify('Berkas dengan nama tersebut sudah ada.', 'error');

            return;
        }

        try {
            File::put($targetFile, '');
            $this->notify("Berkas '{$name}' berhasil dibuat.");
            $this->closeNewFileModal();
        } catch (\Exception $e) {
            $this->notify('Gagal membuat berkas: '.$e->getMessage(), 'error');
        }
    }

    public function openUploadModal(): void
    {
        $this->uploadedFiles = [];
        $this->showUploadModal = true;
    }

    public function closeUploadModal(): void
    {
        $this->showUploadModal = false;
        $this->uploadedFiles = [];
    }

    public function uploadFiles(): void
    {
        if ($this->isLocked) {
            return;
        }

        if (empty($this->uploadedFiles)) {
            $this->notify('Pilih minimal satu berkas untuk diunggah.', 'error');

            return;
        }

        $targetDir = $this->absolutePath;
        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $uploadedCount = 0;
        foreach ($this->uploadedFiles as $file) {
            $originalName = $file->getClientOriginalName();
            $safeName = basename(str_replace(['/', '\\'], '_', $originalName));
            $destination = $targetDir.'/'.$safeName;

            try {
                File::put($destination, File::get($file->getRealPath()));
                $uploadedCount++;
            } catch (\Exception $e) {
                // Continue with next
            }
        }

        $this->notify("{$uploadedCount} berkas berhasil diunggah.");
        $this->closeUploadModal();
    }

    public function openRenameModal(string $path): void
    {
        $this->renameTargetPath = $this->sanitizePath($path);
        $this->renameTargetName = basename($this->renameTargetPath);
        $this->renameNewName = $this->renameTargetName;
        $this->showRenameModal = true;
    }

    public function closeRenameModal(): void
    {
        $this->showRenameModal = false;
        $this->renameTargetPath = '';
        $this->renameTargetName = '';
        $this->renameNewName = '';
    }

    public function rename(): void
    {
        if ($this->isLocked) {
            return;
        }

        $newName = trim($this->renameNewName);
        if ($newName === '' || str_contains($newName, '/') || str_contains($newName, '\\') || $newName === '.' || $newName === '..') {
            $this->notify('Nama baru tidak valid.', 'error');

            return;
        }

        $oldFullPath = storage_path($this->renameTargetPath);
        if (! File::exists($oldFullPath)) {
            $this->notify('Item yang akan diubah namanya tidak ditemukan.', 'error');

            return;
        }

        $parentDir = dirname($oldFullPath);
        $newFullPath = $parentDir.'/'.$newName;

        if (File::exists($newFullPath) && $newFullPath !== $oldFullPath) {
            $this->notify('Nama tersebut sudah digunakan oleh item lain.', 'error');

            return;
        }

        try {
            File::move($oldFullPath, $newFullPath);
            $this->notify("Nama berhasil diubah menjadi '{$newName}'.");
            $this->closeRenameModal();
        } catch (\Exception $e) {
            $this->notify('Gagal mengubah nama: '.$e->getMessage(), 'error');
        }
    }

    public function openDeleteModal(string $path, bool $isDir = false): void
    {
        $this->deleteTargetPath = $this->sanitizePath($path);
        $this->deleteTargetName = basename($this->deleteTargetPath);
        $this->deleteIsDirectory = $isDir;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTargetPath = '';
        $this->deleteTargetName = '';
        $this->deleteIsDirectory = false;
    }

    public function delete(): void
    {
        if ($this->isLocked) {
            return;
        }

        $fullPath = storage_path($this->deleteTargetPath);
        if (! File::exists($fullPath)) {
            $this->notify('Item tidak ditemukan.', 'error');
            $this->closeDeleteModal();

            return;
        }

        try {
            if (File::isDirectory($fullPath)) {
                File::deleteDirectory($fullPath);
            } else {
                File::delete($fullPath);
            }
            $this->notify("'{$this->deleteTargetName}' berhasil dihapus.");
            $this->closeDeleteModal();
            if ($this->selectedPath === $this->deleteTargetPath) {
                $this->selectedPath = null;
            }
        } catch (\Exception $e) {
            $this->notify('Gagal menghapus: '.$e->getMessage(), 'error');
        }
    }

    public function downloadFile(string $path)
    {
        if ($this->isLocked) {
            return null;
        }

        $sanitized = $this->sanitizePath($path);
        $fullPath = storage_path($sanitized);

        if (! File::exists($fullPath) || File::isDirectory($fullPath)) {
            $this->notify('Berkas tidak ditemukan untuk diunduh.', 'error');

            return null;
        }

        return response()->download($fullPath);
    }

    public function saveTextFile(): void
    {
        if ($this->isLocked || ! $this->previewItem) {
            return;
        }

        $fullPath = storage_path($this->previewItem['path']);
        if (! File::exists($fullPath) || File::isDirectory($fullPath)) {
            $this->notify('Berkas tidak ditemukan.', 'error');

            return;
        }

        try {
            File::put($fullPath, $this->previewContent ?? '');
            $sizeBytes = File::size($fullPath);
            $this->previewItem['size'] = $this->formatBytes($sizeBytes);
            $this->previewItem['data'] = $this->previewContent;
            $this->notify('Perubahan berkas berhasil disimpan.');
        } catch (\Exception $e) {
            $this->notify('Gagal menyimpan berkas: '.$e->getMessage(), 'error');
        }
    }

    public function openPreview(string $path): void
    {
        if ($this->isLocked) {
            return;
        }

        $sanitized = $this->sanitizePath($path);
        $fullPath = storage_path($sanitized);
        $realPath = realpath($fullPath) ?: $fullPath;

        if (! File::exists($realPath) || File::isDirectory($realPath)) {
            return;
        }

        $basename = basename($fullPath);
        $extension = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
        $sizeBytes = File::size($realPath);

        $imageExts = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];
        $textExts = ['txt', 'log', 'json', 'md', 'php', 'js', 'css', 'html', 'yaml', 'yml', 'gitignore'];

        $type = 'unknown';
        $data = null;
        $this->previewContent = null;

        if (in_array($extension, $imageExts)) {
            $type = 'image';
            $mime = $extension === 'svg' ? 'image/svg+xml' : 'image/'.$extension;
            $data = 'data:'.$mime.';base64,'.base64_encode(File::get($realPath));
        } elseif ($extension === 'pdf') {
            $type = 'pdf';
            $data = 'data:application/pdf;base64,'.base64_encode(File::get($realPath));
        } elseif (in_array($extension, $textExts) || $sizeBytes < 100000) {
            $type = 'text';
            $data = File::get($realPath);
            $this->previewContent = $data;
        }

        $this->previewItem = [
            'name' => $basename,
            'path' => $sanitized,
            'extension' => $extension,
            'size' => $this->formatBytes($sizeBytes),
            'updated_at' => date('d M Y, H:i', File::lastModified($realPath)),
            'type' => $type,
            'data' => $data,
        ];
    }

    public function closePreview(): void
    {
        $this->previewItem = null;
        $this->previewContent = null;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }
        if ($bytes < 1073741824) {
            return round($bytes / 1048576, 1).' MB';
        }

        return round($bytes / 1073741824, 1).' GB';
    }

    /**
     * Get active Cloud Storage details from MiniOS settings.
     *
     * @return array{driver: string, name: string, target: string, region: string, endpoint: string, is_active: bool}|null
     */
    public function getCloudStorageInfoProperty(): ?array
    {
        $driver = os_setting('services.storage_driver', 'local');

        if ($driver === 's3') {
            $bucket = os_setting('services.s3_bucket', '');

            return [
                'driver' => 's3',
                'name' => 'S3 Cloud Storage',
                'target' => $bucket ?: 'Default Bucket',
                'region' => os_setting('services.s3_region', 'us-east-1'),
                'endpoint' => os_setting('services.s3_endpoint', ''),
                'is_active' => true,
            ];
        }

        if ($driver === 'bunny') {
            $zone = os_setting('services.bunny_storage_zone', '');

            return [
                'driver' => 'bunny',
                'name' => 'BunnyCDN Storage',
                'target' => $zone ?: 'Default Zone',
                'region' => os_setting('services.bunny_region', 'de'),
                'endpoint' => os_setting('services.bunny_pull_zone', ''),
                'is_active' => true,
            ];
        }

        // Check if user has configured S3 or Bunny even if default driver is currently local
        $s3Bucket = os_setting('services.s3_bucket');
        if (! empty($s3Bucket)) {
            return [
                'driver' => 's3',
                'name' => 'S3 Cloud Storage',
                'target' => $s3Bucket,
                'region' => os_setting('services.s3_region', 'us-east-1'),
                'endpoint' => os_setting('services.s3_endpoint', ''),
                'is_active' => false,
            ];
        }

        $bunnyZone = os_setting('services.bunny_storage_zone');
        if (! empty($bunnyZone)) {
            return [
                'driver' => 'bunny',
                'name' => 'BunnyCDN Storage',
                'target' => $bunnyZone,
                'region' => os_setting('services.bunny_region', 'de'),
                'endpoint' => os_setting('services.bunny_pull_zone', ''),
                'is_active' => false,
            ];
        }

        return null;
    }

    public function openCloudStorageModal(): void
    {
        $this->cloudStorageTestStatus = null;
        $this->cloudStorageTestError = null;
        $this->showCloudStorageModal = true;
    }

    public function closeCloudStorageModal(): void
    {
        $this->showCloudStorageModal = false;
        $this->cloudStorageTestStatus = null;
        $this->cloudStorageTestError = null;
    }

    public function testCloudStorageConnection(): void
    {
        $this->cloudStorageTestStatus = null;
        $this->cloudStorageTestError = null;

        $info = $this->cloudStorageInfo;
        if (! $info) {
            $this->cloudStorageTestError = 'Belum ada konfigurasi Cloud Storage.';

            return;
        }

        try {
            $driver = $info['driver'];
            $disk = Storage::disk($driver);
            $testFileName = 'minios_cloud_probe_'.time().'.txt';
            $testContent = 'MiniOS Cloud Probe from Files App at '.now()->toIso8601String();

            $disk->put($testFileName, $testContent);
            $disk->delete($testFileName);

            $this->cloudStorageTestStatus = "Koneksi ke {$info['name']} ('{$info['target']}') berhasil diverifikasi!";
        } catch (\Throwable $e) {
            $this->cloudStorageTestError = 'Gagal terhubung ke Cloud Storage: '.$e->getMessage();
        }
    }

    /**
     * Get dynamic accent color configuration based on appearance settings.
     *
     * @return array<string, string>
     */
    public function getAccentProperty(): array
    {
        $color = os_setting()->get('appearance.accent_color', 'indigo');

        return match ($color) {
            'zinc' => [
                'name' => 'zinc',
                'bg' => 'bg-zinc-700',
                'bg_hover' => 'hover:bg-zinc-800',
                'badge' => 'bg-zinc-800 text-white shadow-zinc-500/30',
                'active_tab' => 'bg-zinc-700 text-white shadow-sm font-medium',
                'ring' => 'focus:ring-zinc-500 focus:border-zinc-500',
                'text' => 'text-zinc-600 dark:text-zinc-400',
                'hex' => '#27272a',
            ],
            'emerald' => [
                'name' => 'emerald',
                'bg' => 'bg-emerald-600',
                'bg_hover' => 'hover:bg-emerald-700',
                'badge' => 'bg-emerald-600 text-white shadow-emerald-500/30',
                'active_tab' => 'bg-emerald-600 text-white shadow-sm font-medium',
                'ring' => 'focus:ring-emerald-500 focus:border-emerald-500',
                'text' => 'text-emerald-600 dark:text-emerald-400',
                'hex' => '#10b981',
            ],
            'sky' => [
                'name' => 'sky',
                'bg' => 'bg-sky-500',
                'bg_hover' => 'hover:bg-sky-600',
                'badge' => 'bg-sky-500 text-white shadow-sky-500/30',
                'active_tab' => 'bg-sky-600 text-white shadow-sm font-medium',
                'ring' => 'focus:ring-sky-500 focus:border-sky-500',
                'text' => 'text-sky-600 dark:text-sky-400',
                'hex' => '#0ea5e9',
            ],
            'amber' => [
                'name' => 'amber',
                'bg' => 'bg-amber-500',
                'bg_hover' => 'hover:bg-amber-600',
                'badge' => 'bg-amber-500 text-white shadow-amber-500/30',
                'active_tab' => 'bg-amber-600 text-white shadow-sm font-medium',
                'ring' => 'focus:ring-amber-500 focus:border-amber-500',
                'text' => 'text-amber-600 dark:text-amber-400',
                'hex' => '#f59e0b',
            ],
            'rose' => [
                'name' => 'rose',
                'bg' => 'bg-rose-500',
                'bg_hover' => 'hover:bg-rose-600',
                'badge' => 'bg-rose-500 text-white shadow-rose-500/30',
                'active_tab' => 'bg-rose-600 text-white shadow-sm font-medium',
                'ring' => 'focus:ring-rose-500 focus:border-rose-500',
                'text' => 'text-rose-600 dark:text-rose-400',
                'hex' => '#f43f5e',
            ],
            default => [ // indigo
                'name' => 'indigo',
                'bg' => 'bg-indigo-600',
                'bg_hover' => 'hover:bg-indigo-700',
                'badge' => 'bg-indigo-600 text-white shadow-indigo-500/30',
                'active_tab' => 'bg-indigo-600 text-white shadow-sm font-medium',
                'ring' => 'focus:ring-indigo-500 focus:border-indigo-500',
                'text' => 'text-indigo-600 dark:text-indigo-400',
                'hex' => '#4f46e5',
            ],
        };
    }

    public function render()
    {
        $view = view()->exists('pages.minios.apps.files')
            ? 'pages.minios.apps.files'
            : 'minios::apps.files';

        return view($view, [
            'accent' => $this->accent,
        ]);
    }
}
