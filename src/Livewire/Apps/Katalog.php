<?php

namespace Novay\MiniOS\Livewire\Apps;

use Composer\InstalledVersions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Novay\MiniOS\Concerns\HasTranslations;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Facades\MiniOS;
use Novay\MiniOS\Services\AppCatalogService;
use ReflectionClass;
use ZipArchive;

class Katalog extends Component
{
    use HasTranslations;
    use WithFileUploads;

    public string $activeTab = 'all'; // 'all', 'system', 'custom', 'catalog'

    public string $catalogCategory = 'all';

    public string $catalogSearch = '';

    public string $search = '';

    public mixed $uploadFile = null;

    public bool $showUploadModal = false;

    public bool $showAboutModal = false;

    public ?string $appToUninstall = null;

    public ?array $appToUninstallDetails = null;

    public ?string $statusMessage = null;

    public string $statusType = 'success'; // 'success' or 'error'

    public ?string $expandedApp = null;

    public bool $showComposerModal = false;

    public ?string $composerAppId = null;

    public ?string $composerAppName = null;

    public ?string $composerPackage = null;

    public string $composerCommand = '';

    public string $composerStatus = 'idle'; // 'idle', 'running', 'success', 'error'

    public string $composerOutput = '';

    public string $themeCategory = 'all';

    public function mount(?string $path = null): void
    {
        $rawPath = $path ?? request()->route('desktopPath') ?? request()->path();
        $target = trim(parse_url((string) $rawPath, PHP_URL_PATH) ?? '', '/');

        if (str_ends_with($target, '/apps') || $target === 'apps') {
            $this->activeTab = 'apps';
        } elseif (str_ends_with($target, '/themes') || $target === 'themes') {
            $this->activeTab = 'themes';
        } elseif (str_ends_with($target, '/installed') || $target === 'installed') {
            $this->activeTab = 'installed';
        } elseif (str_ends_with($target, '/explore') || $target === 'explore' || str_ends_with($target, '/katalog') || $target === 'katalog') {
            $this->activeTab = 'explore';
        }
    }

    #[On('desktop-route-changed')]
    public function onDesktopRouteChanged(?string $path = null, ?string $url = null): void
    {
        $target = $path ?? $url ?? request()->path();
        if (! $target) {
            return;
        }

        $target = trim(parse_url($target, PHP_URL_PATH) ?? '', '/');

        if (str_ends_with($target, '/apps') || $target === 'apps') {
            $this->activeTab = 'apps';
        } elseif (str_ends_with($target, '/themes') || $target === 'themes') {
            $this->activeTab = 'themes';
        } elseif (str_ends_with($target, '/installed') || $target === 'installed') {
            $this->activeTab = 'installed';
        } elseif (str_ends_with($target, '/katalog') || $target === 'katalog' || str_ends_with($target, '/explore') || $target === 'explore') {
            $this->activeTab = 'explore';
        }
    }

    public function refresh(): void
    {
        $this->search = '';
        $this->catalogSearch = '';
        $this->catalogCategory = 'all';
        $this->expandedApp = null;
        $this->statusMessage = null;
        $this->showUploadModal = false;
        $this->showAboutModal = false;
        $this->showComposerModal = false;
    }

    public function toggleAppDetails(string $id): void
    {
        $this->expandedApp = ($this->expandedApp === $id) ? null : $id;
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['all', 'system', 'custom', 'catalog', 'explore', 'apps', 'themes', 'installed'], true)) {
            $this->activeTab = $tab;

            $routePath = match ($this->effectiveNav) {
                'apps' => '/desktop/katalog/apps',
                'themes' => '/desktop/katalog/themes',
                'installed' => '/desktop/katalog/installed',
                default => '/desktop/katalog',
            };

            $this->dispatch('update-window-url', id: 'katalog', url: $routePath);
        }
    }

    public function setThemeCategory(string $category): void
    {
        $this->themeCategory = $category;
    }

    public function getEffectiveNavProperty(): string
    {
        if (in_array($this->activeTab, ['explore', 'jelajah'], true)) {
            return 'explore';
        }

        if (in_array($this->activeTab, ['catalog', 'apps', 'aplikasi'], true)) {
            return 'apps';
        }

        if ($this->activeTab === 'themes') {
            return 'themes';
        }

        return 'installed';
    }

    public function getTopChartsProperty(): array
    {
        $apps = $this->catalogApps;
        $charts = [];
        $rank = 1;

        foreach ($apps as $app) {
            $charts[] = [
                'rank' => $rank++,
                'id' => $app['id'],
                'name' => $app['name'],
                'category_label' => $app['category_label'] ?? 'Aplikasi',
                'icon' => $app['icon'] ?? 'cube',
                'rating' => $app['rating'] ?? 4.8,
                'downloads' => $app['downloads'] ?? '1.5k',
                'is_installed' => $app['is_installed'] ?? false,
                'is_catalog' => true,
            ];
            if ($rank > 6) {
                break;
            }
        }

        return $charts;
    }

    public function getThemesProperty(): array
    {
        $themes = [
            [
                'id' => 'fluent-mica',
                'name' => 'Fluent Mica Dark',
                'category' => 'dark',
                'category_label' => 'Dark Mode',
                'author' => 'MiniOS Core',
                'rating' => 4.9,
                'downloads' => '4.2k',
                'badge' => 'Default OS',
                'badge_color' => 'indigo',
                'description' => 'Tema gelap modern dengan material kaca Mica, bayangan halus, dan kontras tajam khas Windows 11.',
                'preview_bg' => 'linear-gradient(135deg, #18181b 0%, #09090b 100%)',
                'colors' => ['#6366f1', '#18181b', '#27272a', '#e4e4e7'],
                'accent' => '#6366f1',
                'is_active' => true,
            ],
            [
                'id' => 'cyberpunk-neon',
                'name' => 'Cyberpunk Neon 2077',
                'category' => 'neon',
                'category_label' => 'Neon & Vibrant',
                'author' => 'NightCity Labs',
                'rating' => 4.85,
                'downloads' => '2.9k',
                'badge' => 'Populer',
                'badge_color' => 'fuchsia',
                'description' => 'Aksen futuristik bernuansa neon cyan dan magenta elektrik dengan efek pencahayaan dinamis.',
                'preview_bg' => 'linear-gradient(135deg, #090d16 0%, #1e1b4b 50%, #4c0519 100%)',
                'colors' => ['#06b6d4', '#d946ef', '#0284c7', '#f43f5e'],
                'accent' => '#06b6d4',
                'is_active' => false,
            ],
            [
                'id' => 'nordic-frost',
                'name' => 'Nordic Frost',
                'category' => 'minimalist',
                'category_label' => 'Minimalis',
                'author' => 'Arctic Studio',
                'rating' => 4.95,
                'downloads' => '2.1k',
                'badge' => "Editor's Choice",
                'badge_color' => 'sky',
                'description' => 'Palet warna pastel es Skandinavia yang menenangkan mata, cocok untuk fokus dan koding maraton.',
                'preview_bg' => 'linear-gradient(135deg, #2e3440 0%, #3b4252 50%, #434c5e 100%)',
                'colors' => ['#88c0d0', '#81a1c1', '#5e81ac', '#eceff4'],
                'accent' => '#88c0d0',
                'is_active' => false,
            ],
            [
                'id' => 'macos-sonoma',
                'name' => 'macOS Sonoma Glass',
                'category' => 'glass',
                'category_label' => 'Aero Glass',
                'author' => 'Cupertino Team',
                'rating' => 4.75,
                'downloads' => '3.5k',
                'badge' => 'Tren',
                'badge_color' => 'amber',
                'description' => 'Estetika desktop aero blur ultra jernih dengan transisi halus dan dock minimalis elegan.',
                'preview_bg' => 'linear-gradient(135deg, #f59e0b 0%, #ec4899 50%, #8b5cf6 100%)',
                'colors' => ['#f59e0b', '#ec4899', '#8b5cf6', '#ffffff'],
                'accent' => '#f59e0b',
                'is_active' => false,
            ],
            [
                'id' => 'tokyo-night',
                'name' => 'Tokyo Night Storm',
                'category' => 'dark',
                'category_label' => 'Dark Mode',
                'author' => 'Tokyo Devs',
                'rating' => 4.88,
                'downloads' => '2.7k',
                'badge' => 'Pro Developer',
                'badge_color' => 'blue',
                'description' => 'Tema gelap terinspirasi lampu kota Tokyo saat malam hari, kontras seimbang untuk produktivitas.',
                'preview_bg' => 'linear-gradient(135deg, #1a1b26 0%, #24283b 50%, #414868 100%)',
                'colors' => ['#7aa2f7', '#bb9af7', '#7dcfff', '#c0caf5'],
                'accent' => '#7aa2f7',
                'is_active' => false,
            ],
            [
                'id' => 'retro-95',
                'name' => 'Classic Desktop 95',
                'category' => 'retro',
                'category_label' => 'Retro Classic',
                'author' => 'Vintage Pixel',
                'rating' => 4.6,
                'downloads' => '1.4k',
                'badge' => 'Nostalgia',
                'badge_color' => 'emerald',
                'description' => 'Desain nostalgic border beveled abu-abu klasik dan tombol timbul gaya sistem operasi era 90-an.',
                'preview_bg' => 'linear-gradient(135deg, #008080 0%, #004d4d 100%)',
                'colors' => ['#008080', '#c0c0c0', '#ffffff', '#000000'],
                'accent' => '#008080',
                'is_active' => false,
            ],
        ];

        if ($this->themeCategory !== 'all') {
            return array_values(array_filter($themes, fn ($t) => $t['category'] === $this->themeCategory));
        }

        return $themes;
    }

    public function setCatalogCategory(string $category): void
    {
        $this->catalogCategory = $category;
    }

    /**
     * Install an application template from App Catalog with 1-click.
     */
    public function installCatalogApp(string $catalogId): void
    {
        /** @var AppCatalogService $service */
        $service = app(AppCatalogService::class);

        try {
            $result = $service->install($catalogId);
            $this->statusType = 'success';
            $this->statusMessage = $this->trans('msg_catalog_installed', ['name' => $result['name']]);
            $this->dispatch('app-installed', ['id' => $result['id'], 'name' => $result['name']]);
        } catch (\Throwable $e) {
            $this->statusType = 'error';
            $this->statusMessage = $e->getMessage();
        }
    }

    public function dismissStatus(): void
    {
        $this->statusMessage = null;
    }

    public function openUploadModal(): void
    {
        $this->uploadFile = null;
        $this->showUploadModal = true;
    }

    public function closeUploadModal(): void
    {
        $this->uploadFile = null;
        $this->showUploadModal = false;
    }

    public function openAboutModal(): void
    {
        $this->showAboutModal = true;
    }

    public function closeAboutModal(): void
    {
        $this->showAboutModal = false;
    }

    /**
     * Install an application package from uploaded ZIP archive.
     */
    public function installZip(): void
    {
        $this->validate([
            'uploadFile' => 'required|file|mimes:zip|max:51200',
        ], [
            'uploadFile.required' => $this->trans('val_upload_required'),
            'uploadFile.mimes' => $this->trans('val_upload_mimes'),
            'uploadFile.max' => $this->trans('val_upload_max'),
        ]);

        $zipPath = $this->uploadFile->getRealPath();
        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            $this->statusType = 'error';
            $this->statusMessage = $this->trans('err_zip_open');

            return;
        }

        // 1. Zip-Slip Security Check and Manifest Search
        $manifestEntry = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $entryName = $stat['name'];

            if (str_contains($entryName, '..') || str_starts_with($entryName, '/') || str_starts_with($entryName, '\\')) {
                $zip->close();
                $this->statusType = 'error';
                $this->statusMessage = $this->trans('err_zip_slip');

                return;
            }

            if ($manifestEntry === null && preg_match('#(^|/)([A-Za-z0-9_]+App)\.php$#', $entryName)) {
                if (! str_contains($entryName, 'vendor/') && ! str_contains($entryName, '__MACOSX')) {
                    $manifestEntry = $entryName;
                }
            }
        }

        if (! $manifestEntry) {
            $zip->close();
            $this->statusType = 'error';
            $this->statusMessage = $this->trans('err_manifest_missing');

            return;
        }

        // 2. Read manifest content to detect class name & app identity
        $manifestContent = $zip->getFromName($manifestEntry);
        if ($manifestContent === false) {
            $zip->close();
            $this->statusType = 'error';
            $this->statusMessage = $this->trans('err_manifest_read');

            return;
        }

        preg_match('/class\s+([A-Za-z0-9_]+App)/', $manifestContent, $classMatches);
        $manifestClassName = $classMatches[1] ?? basename($manifestEntry, '.php');
        $studlyName = Str::studly(str_replace(['App', 'app'], '', $manifestClassName));
        $kebabName = Str::kebab($studlyName);

        // Prevent overwriting core system apps
        if (MiniOS::isCoreApp($kebabName) || MiniOS::isCoreApp(strtolower($studlyName))) {
            $zip->close();
            $this->statusType = 'error';
            $this->statusMessage = $this->trans('err_core_app_overwrite', ['name' => $studlyName]);

            return;
        }

        // 3. Determine base folder prefix inside ZIP (if any)
        $prefix = '';
        if (str_contains($manifestEntry, '/')) {
            $parts = explode('/', $manifestEntry);
            array_pop($parts); // remove manifest filename
            $prefix = implode('/', $parts).'/';
        }

        // 4. Target directories (prefer app/MiniOS)
        $targetDir = app_path("MiniOS/{$studlyName}");
        File::ensureDirectoryExists($targetDir);
        $hasMigrations = false;

        // 5. Extract files
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $entryName = $stat['name'];

            if (str_contains($entryName, '__MACOSX') || basename($entryName) === '.DS_Store' || str_ends_with($entryName, '/')) {
                continue;
            }

            $relative = $entryName;
            if ($prefix !== '' && str_starts_with($relative, $prefix)) {
                $relative = substr($relative, strlen($prefix));
            }

            $relative = ltrim($relative, '/');
            if (empty($relative)) {
                continue;
            }

            $content = $zip->getFromIndex($i);
            if ($content === false) {
                continue;
            }

            // Only write PHP application classes to app directory if not an external asset
            $isExternalAsset = preg_match('#^(resources/)?views/#i', $relative)
                || preg_match('#^(app/)?Models/#i', $relative)
                || preg_match('#^(database/)?migrations/#i', $relative);

            if (! $isExternalAsset) {
                $destFile = "{$targetDir}/{$relative}";
                File::ensureDirectoryExists(dirname($destFile));
                File::put($destFile, $content);
            }

            // Mirror views to resources/views/apps if packaged under views/
            if (preg_match('#^(resources/)?views/(.+)$#', $relative, $viewMatch)) {
                $viewSub = $viewMatch[2];
                if ($viewSub === "{$kebabName}.blade.php" || $viewSub === "apps/{$kebabName}.blade.php") {
                    $viewDest = resource_path("views/apps/{$kebabName}.blade.php");
                } elseif ($viewSub === 'index.blade.php' && ! File::exists(resource_path("views/apps/{$kebabName}.blade.php"))) {
                    $viewDest = resource_path("views/apps/{$kebabName}.blade.php");
                } elseif (str_starts_with($viewSub, 'tabs/') || str_starts_with($viewSub, 'apps/')) {
                    $viewDest = resource_path('views/'.(str_starts_with($viewSub, 'apps/') ? $viewSub : "apps/{$viewSub}"));
                } else {
                    $viewDest = resource_path("views/apps/{$kebabName}/{$viewSub}");
                }
                File::ensureDirectoryExists(dirname($viewDest));
                File::put($viewDest, $content);
            }

            // Mirror models if packaged under Models/ or app/Models/
            if (preg_match('#^(app/)?Models/(.+)$#i', $relative, $modelMatch)) {
                $modelDest = app_path("Models/{$studlyName}/{$modelMatch[2]}");
                File::ensureDirectoryExists(dirname($modelDest));
                File::put($modelDest, $content);
            }

            // Mirror migrations if packaged under migrations/ or database/migrations/
            if (preg_match('#^(database/)?migrations/(.+)$#', $relative, $migMatch)) {
                $migDest = database_path("migrations/{$migMatch[2]}");
                File::ensureDirectoryExists(dirname($migDest));
                File::put($migDest, $content);
                $hasMigrations = true;
            }

            // Mirror config if packaged under config/
            if (preg_match('#^config/(.+)$#', $relative, $cfgMatch)) {
                $cfgDest = config_path($cfgMatch[1]);
                if (! File::exists($cfgDest)) {
                    File::ensureDirectoryExists(dirname($cfgDest));
                    File::put($cfgDest, $content);
                }
            }
        }

        $zip->close();

        // Run migrations if any were included in the package
        if ($hasMigrations) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
                // Keep installation going even if already migrated
            }
        }

        // 6. Require and register the newly installed app
        $manifestPath = "{$targetDir}/{$manifestClassName}.php";
        if (file_exists($manifestPath)) {
            require_once $manifestPath;
        }

        $miniosClass = "App\\MiniOS\\{$studlyName}\\{$manifestClassName}";
        $appsClass = "App\\Apps\\{$studlyName}\\{$manifestClassName}";

        $fullClass = class_exists($miniosClass) ? $miniosClass : $appsClass;

        if (class_exists($fullClass) && is_subclass_of($fullClass, DesktopApp::class)) {
            MiniOS::register($fullClass);
        }

        // Check for missing dependencies
        $registeredApp = MiniOS::getApplication($kebabName);
        $missing = [];
        if ($registeredApp) {
            $pkgs = $this->inspectAppPackages($registeredApp, $targetDir);
            foreach ($pkgs as $p) {
                if (! $p['installed']) {
                    $missing[] = $p['name'];
                }
            }
        }

        $this->uploadFile = null;
        $this->showUploadModal = false;
        $this->statusType = 'success';

        if (! empty($missing)) {
            $this->statusMessage = $this->trans('msg_install_success_missing', [
                'name' => $studlyName,
                'packages' => implode(', ', $missing),
            ]);
            $this->expandedApp = $kebabName;
        } else {
            $this->statusMessage = $this->trans('msg_install_success', ['name' => $studlyName]);
        }

        $this->dispatch('app-installed', [
            'id' => $kebabName,
            'name' => $studlyName,
            'missing_packages' => $missing,
        ]);
    }

    /**
     * Open composer terminal runner modal for a specific application and package.
     */
    public function openComposerModal(string $appId, string $package): void
    {
        $app = MiniOS::getApplication($appId);
        $appName = $app ? $app->name() : $appId;

        $this->composerAppId = $appId;
        $this->composerAppName = $appName;
        $this->composerPackage = $package;
        $this->composerCommand = "composer require {$package} --no-interaction";
        $this->composerStatus = 'idle';
        $this->composerOutput = "$ {$this->composerCommand}\nSiap untuk menjalankan instalasi paket Composer.";
        $this->showComposerModal = true;
    }

    /**
     * Close the composer installation modal.
     */
    public function closeComposerModal(): void
    {
        $this->showComposerModal = false;
        $this->composerAppId = null;
        $this->composerAppName = null;
        $this->composerPackage = null;
        $this->composerCommand = '';
        $this->composerStatus = 'idle';
        $this->composerOutput = '';
    }

    /**
     * Run composer require for the selected package and capture console output.
     */
    public function runComposerInstall(): void
    {
        if (! $this->composerPackage) {
            return;
        }

        $this->composerStatus = 'running';
        $composerBin = $this->findComposerBinary();
        $package = escapeshellarg($this->composerPackage);

        $this->composerOutput = "$ {$composerBin} require {$this->composerPackage} --no-interaction\n";
        $this->composerOutput .= "Menyiapkan environment dan menjalankan composer...\n\n";

        $cmd = "{$composerBin} require {$package} --no-interaction 2>&1";

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $env = array_merge($_SERVER, [
            'COMPOSER_HOME' => getenv('COMPOSER_HOME') ?: (getenv('HOME') ? getenv('HOME').'/.composer' : sys_get_temp_dir()),
            'HOME' => getenv('HOME') ?: sys_get_temp_dir(),
            'PATH' => getenv('PATH') ?: '/opt/homebrew/bin:/usr/local/bin:/usr/bin:/bin',
        ]);

        $process = proc_open($cmd, $descriptors, $pipes, base_path(), $env);

        if (is_resource($process)) {
            fclose($pipes[0]);
            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            $exitCode = proc_close($process);

            $output = trim($stdout."\n".$stderr);
            $this->composerOutput .= $output;

            if ($exitCode === 0) {
                $this->composerStatus = 'success';
                $this->composerOutput .= "\n\n✓ Sukses: Paket [{$this->composerPackage}] berhasil dipasang ke proyek Laravel!";
                $this->statusType = 'success';
                $this->statusMessage = "Paket {$this->composerPackage} berhasil dipasang.";
                $this->dispatch('app-installed');
            } else {
                $this->composerStatus = 'error';
                $this->composerOutput .= "\n\n✗ Gagal: Perintah keluar dengan kode status [{$exitCode}].";
            }
        } else {
            $this->composerStatus = 'error';
            $this->composerOutput .= "\n✗ Gagal memulai proses proc_open untuk binary composer.";
        }
    }

    /**
     * Locate the composer executable binary.
     */
    protected function findComposerBinary(): string
    {
        $candidates = [
            '/opt/homebrew/bin/composer',
            '/usr/local/bin/composer',
            '/usr/bin/composer',
            base_path('composer.phar'),
        ];

        foreach ($candidates as $cand) {
            if (file_exists($cand) && is_executable($cand)) {
                return $cand;
            }
        }

        $which = trim(@shell_exec('which composer 2>/dev/null') ?: '');
        if ($which && file_exists($which)) {
            return $which;
        }

        return 'composer';
    }

    /**
     * Show confirmation modal for uninstalling a custom application.
     */
    public function confirmUninstall(string $id): void
    {
        if (MiniOS::isCoreApp($id)) {
            $this->statusType = 'error';
            $this->statusMessage = $this->trans('err_core_app_uninstall');

            return;
        }

        $app = MiniOS::getApplication($id);
        if (! $app) {
            $this->statusType = 'error';
            $this->statusMessage = $this->trans('err_app_not_found');

            return;
        }

        $this->appToUninstall = $id;
        $this->appToUninstallDetails = [
            'id' => $id,
            'name' => $app->name(),
            'icon' => $app->icon(),
            'entry' => $app->entry(),
        ];
    }

    public function cancelUninstall(): void
    {
        $this->appToUninstall = null;
        $this->appToUninstallDetails = null;
    }

    /**
     * Perform uninstallation of the selected custom application.
     */
    public function uninstallApp(): void
    {
        $id = $this->appToUninstall;
        if (! $id || MiniOS::isCoreApp($id)) {
            $this->statusType = 'error';
            $this->statusMessage = $this->trans('err_core_app_uninstall');
            $this->cancelUninstall();

            return;
        }

        $app = MiniOS::getApplication($id);
        if (! $app) {
            $this->statusType = 'error';
            $this->statusMessage = $this->trans('err_app_not_found');
            $this->cancelUninstall();

            return;
        }

        $appName = $app->name();
        $reflection = new ReflectionClass($app);
        $manifestFile = $reflection->getFileName() ?: '';
        $appDir = $manifestFile ? dirname($manifestFile) : '';

        // Verify folder is within app/MiniOS or app/Apps directory
        $miniosRoot = realpath(app_path('MiniOS'));
        $appsRoot = realpath(app_path('Apps'));
        $realAppDir = realpath($appDir);

        $isSafe = ($realAppDir && $miniosRoot && str_starts_with($realAppDir, $miniosRoot))
            || ($realAppDir && $appsRoot && str_starts_with($realAppDir, $appsRoot));

        if (! $isSafe) {
            $this->statusType = 'error';
            $this->statusMessage = $this->trans('err_unsafe_app_dir');
            $this->cancelUninstall();

            return;
        }

        // 1. Delete app directory
        File::deleteDirectory($realAppDir);

        // 2. Delete associated views if any
        $kebab = Str::kebab($appName);
        $viewPaths = [
            resource_path("views/apps/{$kebab}.blade.php"),
            resource_path("views/apps/{$id}.blade.php"),
        ];
        foreach ($viewPaths as $vp) {
            if (File::exists($vp)) {
                File::delete($vp);
            }
        }

        $viewDirs = [
            resource_path("views/apps/{$kebab}"),
            resource_path("views/apps/{$id}"),
        ];
        foreach ($viewDirs as $vd) {
            if (File::isDirectory($vd)) {
                File::deleteDirectory($vd);
            }
        }

        // 3. Rollback & delete associated migrations if any
        $studly = Str::studly(str_replace(['App', 'app'], '', $appName));
        $this->rollbackAndCleanMigrations($realAppDir, $studly, $kebab);

        // 4. Delete associated models if any
        $modelsDir = app_path("Models/{$studly}");
        if (File::isDirectory($modelsDir)) {
            File::deleteDirectory($modelsDir);
        }

        // 5. Remove from Dock pinned settings if pinned
        $pinned = os_setting()->get('dock.pinned_apps', []);
        if (is_array($pinned) && in_array($id, $pinned, true)) {
            os_setting()->set('dock.pinned_apps', array_values(array_diff($pinned, [$id])));
        }

        // 5. Clean up AppServiceProvider if hardcoded
        $this->cleanAppServiceProvider($appName, get_class($app));

        // 6. Unregister from runtime
        MiniOS::unregister($id);

        $this->cancelUninstall();
        $this->statusType = 'success';
        $this->statusMessage = $this->trans('msg_uninstall_success', ['name' => $appName]);

        $this->dispatch('app-uninstalled', ['id' => $id]);
    }

    /**
     * Remove explicit MiniOS::register calls from AppServiceProvider if present.
     */
    protected function cleanAppServiceProvider(string $appName, string $appClass): void
    {
        $providerPath = app_path('Providers/AppServiceProvider.php');
        if (! File::exists($providerPath)) {
            return;
        }

        $content = File::get($providerPath);
        $shortClass = class_basename($appClass);

        $content = preg_replace('/^use\s+App\\\\(MiniOS|Apps)\\\\'.preg_quote($appName, '/').'\\\\'.preg_quote($shortClass, '/').';\r?\n/m', '', $content);
        $content = preg_replace('/^\s*MiniOS::register\('.preg_quote($shortClass, '/').'::class\);\r?\n/m', '', $content);

        File::put($providerPath, $content);
    }

    /**
     * Rollback migrations belonging to the application and remove their files from database/migrations.
     */
    protected function rollbackAndCleanMigrations(string $appDir, string $studlyName, string $kebabName): void
    {
        $migrationFiles = [];

        // Check inside app folder
        $migrationDirs = array_filter([
            $appDir.'/migrations',
            $appDir.'/database/migrations',
        ], 'is_dir');

        foreach ($migrationDirs as $dir) {
            foreach (glob($dir.'/*.php') ?: [] as $file) {
                $migrationFiles[] = $file;
            }
        }

        // Also identify matching files in database/migrations
        $dbMigrations = glob(database_path("migrations/*_{$kebabName}_*.php")) ?: [];
        $dbMigrations = array_merge($dbMigrations, glob(database_path("migrations/*_{$studlyName}_*.php")) ?: []);
        $dbMigrations = array_merge($dbMigrations, glob(database_path("migrations/*_{$kebabName}.php")) ?: []);
        $dbMigrations = array_merge($dbMigrations, glob(database_path("migrations/*_{$studlyName}.php")) ?: []);

        foreach ($migrationFiles as $mf) {
            $baseName = basename($mf);
            $targetInDb = database_path("migrations/{$baseName}");
            if (File::exists($targetInDb)) {
                $dbMigrations[] = $targetInDb;
            }
        }

        $uniqueMigrations = array_unique(array_filter($dbMigrations, 'file_exists'));

        // Rollback each migration using its down() method and remove from migrations table
        foreach ($uniqueMigrations as $migFile) {
            $migName = basename($migFile, '.php');

            try {
                // If recorded as applied in migrations table, call down()
                $isApplied = Schema::hasTable('migrations')
                    && DB::table('migrations')->where('migration', $migName)->exists();

                if ($isApplied) {
                    $migrationInstance = require $migFile;
                    if (is_object($migrationInstance) && method_exists($migrationInstance, 'down')) {
                        $migrationInstance->down();
                    }

                    // Remove entry from migrations table
                    DB::table('migrations')->where('migration', $migName)->delete();
                }
            } catch (\Throwable $e) {
                // Continue cleaning up remaining files even if error occurs
            }

            // Delete the migration file from database/migrations/
            if (File::exists($migFile)) {
                File::delete($migFile);
            }
        }
    }

    /**
     * Applications listing with rich metadata and filters.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getApplicationsProperty(): array
    {
        $apps = [];
        $registryApps = MiniOS::getApplications();
        $miniosRoot = realpath(app_path('MiniOS'));
        $appsRoot = realpath(app_path('Apps'));

        foreach ($registryApps as $id => $app) {
            $isCore = MiniOS::isCoreApp($id);
            $reflection = new ReflectionClass($app);
            $filePath = $reflection->getFileName() ?: '';
            $folderPath = $filePath ? dirname($filePath) : '';
            $realFolder = realpath($folderPath);

            $isUserApp = ! $isCore && $realFolder && (
                ($miniosRoot && str_starts_with($realFolder, $miniosRoot))
                || ($appsRoot && str_starts_with($realFolder, $appsRoot))
            );

            $size = 'System Built-in';
            if ($isUserApp && is_dir($folderPath)) {
                $bytes = $this->calculateDirSize($folderPath);
                $size = $this->formatBytes($bytes);
            }

            $installedAt = ($filePath && file_exists($filePath))
                ? date('d M Y, H:i', filemtime($filePath))
                : '-';

            $dbMeta = $this->inspectAppDatabase($folderPath, $app->name(), $isCore);
            $packages = $this->inspectAppPackages($app, $folderPath);

            $apps[$id] = [
                'id' => $id,
                'name' => $app->name(),
                'icon' => $app->icon(),
                'entry' => $app->entry(),
                'routes' => $app->routes(),
                'isPinned' => $app->isPinned(),
                'component' => $app->component(),
                'isCore' => $isCore,
                'isUserApp' => $isUserApp,
                'canUninstall' => $isUserApp,
                'class' => get_class($app),
                'filePath' => $filePath,
                'folderPath' => $folderPath,
                'size' => $size,
                'installedAt' => $installedAt,
                'version' => method_exists($app, 'version') ? $app->version() : '1.0.0',
                'models' => $dbMeta['models'],
                'migrations' => $dbMeta['migrations'],
                'packages' => $packages,
            ];
        }

        // Apply Tab Filter
        if ($this->activeTab === 'system') {
            $apps = array_filter($apps, fn ($a) => $a['isCore']);
        } elseif ($this->activeTab === 'custom') {
            $apps = array_filter($apps, fn ($a) => ! $a['isCore']);
        }

        // Apply Search Filter
        if (! empty(trim($this->search))) {
            $query = strtolower(trim($this->search));
            $apps = array_filter($apps, function ($a) use ($query) {
                return str_contains(strtolower($a['name']), $query)
                    || str_contains(strtolower($a['id']), $query)
                    || str_contains(strtolower($a['entry']), $query);
            });
        }

        return $apps;
    }

    /**
     * Statistics for summary cards.
     *
     * @return array<string, mixed>
     */
    public function getStatsProperty(): array
    {
        $allApps = MiniOS::getApplications();
        $total = count($allApps);
        $systemCount = 0;
        $customCount = 0;
        $customTotalBytes = 0;
        $miniosRoot = realpath(app_path('MiniOS'));
        $appsRoot = realpath(app_path('Apps'));

        foreach ($allApps as $id => $app) {
            if (MiniOS::isCoreApp($id)) {
                $systemCount++;
            } else {
                $customCount++;
                $ref = new ReflectionClass($app);
                $folder = dirname($ref->getFileName() ?: '');
                $realFolder = realpath($folder);
                $isCustom = ($realFolder && $miniosRoot && str_starts_with($realFolder, $miniosRoot))
                    || ($realFolder && $appsRoot && str_starts_with($realFolder, $appsRoot));

                if ($isCustom && is_dir($folder)) {
                    $customTotalBytes += $this->calculateDirSize($folder);
                }
            }
        }

        $catalogCount = count(app(AppCatalogService::class)->getCatalog());

        return [
            'total' => $total,
            'system' => $systemCount,
            'custom' => $customCount,
            'catalog' => $catalogCount,
            'storage' => $this->formatBytes($customTotalBytes),
        ];
    }

    /**
     * Catalog items from discovery service with category and search filters.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCatalogAppsProperty(): array
    {
        /** @var AppCatalogService $service */
        $service = app(AppCatalogService::class);
        $apps = $service->getCatalog();

        if ($this->catalogCategory !== 'all') {
            $apps = array_values(array_filter($apps, fn ($a) => $a['category'] === $this->catalogCategory));
        }

        if (! empty(trim($this->catalogSearch))) {
            $q = strtolower(trim($this->catalogSearch));
            $apps = array_values(array_filter($apps, function ($a) use ($q) {
                return str_contains(strtolower($a['name']), $q)
                    || str_contains(strtolower($a['description']), $q)
                    || in_array($q, array_map('strtolower', $a['tags'] ?? []), true)
                    || str_contains(strtolower($a['category_label']), $q);
            }));
        }

        return $apps;
    }

    protected function calculateDirSize(string $path): int
    {
        $size = 0;
        if (! is_dir($path)) {
            return $size;
        }

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $file) {
            $size += $file->getSize();
        }

        return $size;
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
     * Inspect database models and migrations belonging to an application.
     *
     * @return array{models: array<int, array<string, mixed>>, migrations: array<int, array<string, mixed>>}
     */
    protected function inspectAppDatabase(string $folderPath, string $appName, bool $isCore): array
    {
        if ($isCore || empty($folderPath) || ! is_dir($folderPath)) {
            return [
                'models' => [],
                'migrations' => [],
            ];
        }

        $studly = Str::studly(str_replace(['App', 'app'], '', $appName));
        $kebab = Str::kebab($studly);

        // 1. Detect Models
        $models = [];
        $modelDirs = array_filter([
            $folderPath.'/Models',
            app_path("Models/{$studly}"),
        ], 'is_dir');

        $foundModelNames = [];
        foreach ($modelDirs as $dir) {
            $files = glob($dir.'/*.php') ?: [];
            foreach ($files as $file) {
                $name = basename($file, '.php');
                if (in_array($name, $foundModelNames, true)) {
                    continue;
                }
                $foundModelNames[] = $name;

                $content = @file_get_contents($file) ?: '';
                $class = null;
                if (preg_match('/namespace\s+([^;]+);/', $content, $nsMatches)) {
                    $class = trim($nsMatches[1]).'\\'.$name;
                }

                $table = null;
                $count = null;
                if ($file && file_exists($file)) {
                    try {
                        @include_once $file;
                    } catch (\Throwable) {
                        // ignore include issues
                    }
                }

                if ($class && (class_exists($class, false) || (file_exists($file) && class_exists($class)))) {
                    try {
                        $inst = new $class;
                        if ($inst instanceof Model) {
                            $table = $inst->getTable();
                            if (Schema::hasTable($table)) {
                                $count = $class::count();
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore reflection/db errors
                    }
                }

                $models[] = [
                    'name' => $name,
                    'class' => $class ?: $name,
                    'table' => $table,
                    'count' => $count,
                    'file' => $file,
                ];
            }
        }

        // 2. Detect Migrations
        $migrations = [];
        $migrationDirs = array_filter([
            $folderPath.'/migrations',
            $folderPath.'/database/migrations',
        ], 'is_dir');

        $appliedMigrations = [];
        try {
            if (Schema::hasTable('migrations')) {
                $appliedMigrations = DB::table('migrations')->pluck('migration')->toArray();
            }
        } catch (\Throwable $e) {
            // ignore db connection issues
        }

        $foundMigrationNames = [];
        foreach ($migrationDirs as $dir) {
            $files = glob($dir.'/*.php') ?: [];
            foreach ($files as $file) {
                $base = basename($file, '.php');
                if (in_array($base, $foundMigrationNames, true)) {
                    continue;
                }
                $foundMigrationNames[] = $base;
                $isApplied = in_array($base, $appliedMigrations, true);
                $migrations[] = [
                    'name' => $base,
                    'file' => $file,
                    'applied' => $isApplied,
                ];
            }
        }

        // Check in standard database/migrations if none found in app folder
        if (empty($migrations)) {
            $dbMigrations = glob(database_path("migrations/*_{$kebab}_*.php")) ?: [];
            $dbMigrations = array_merge($dbMigrations, glob(database_path("migrations/*_{$studly}_*.php")) ?: []);
            foreach (array_unique($dbMigrations) as $file) {
                $base = basename($file, '.php');
                if (in_array($base, $foundMigrationNames, true)) {
                    continue;
                }
                $foundMigrationNames[] = $base;
                $migrations[] = [
                    'name' => $base,
                    'file' => $file,
                    'applied' => in_array($base, $appliedMigrations, true),
                ];
            }
        }

        return [
            'models' => $models,
            'migrations' => $migrations,
        ];
    }

    /**
     * Inspect required external composer packages / dependencies for an application.
     *
     * @return array<int, array{name: string, installed: bool, version: ?string, command: string}>
     */
    protected function inspectAppPackages(object $app, string $folderPath): array
    {
        $packages = [];

        // 1. From manifest method packages() or dependencies()
        if (method_exists($app, 'packages')) {
            $packages = array_merge($packages, (array) $app->packages());
        } elseif (method_exists($app, 'dependencies')) {
            $packages = array_merge($packages, (array) $app->dependencies());
        }

        // 2. From composer.json inside app directory if exists
        $appComposerFile = $folderPath.'/composer.json';
        if (File::exists($appComposerFile)) {
            try {
                $decoded = json_decode(File::get($appComposerFile), true);
                if (isset($decoded['require']) && is_array($decoded['require'])) {
                    foreach (array_keys($decoded['require']) as $pkg) {
                        if ($pkg !== 'php' && ! str_starts_with($pkg, 'ext-')) {
                            $packages[] = $pkg;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore json decode errors
            }
        }

        // 3. Fallback: Parse comments in manifest (*App.php) like "@require spatie/laravel-backup" or "@package spatie/laravel-backup"
        $reflection = new ReflectionClass($app);
        $manifestPath = $reflection->getFileName() ?: '';
        if ($manifestPath && File::exists($manifestPath)) {
            $content = File::get($manifestPath);
            if (preg_match_all('/@(?:require|package|dependency)\s+([A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+)/i', $content, $matches)) {
                $packages = array_merge($packages, $matches[1]);
            }
        }

        $unique = array_unique(array_filter(array_map('trim', $packages)));
        $result = [];

        foreach ($unique as $pkgName) {
            $isInstalled = InstalledVersions::isInstalled($pkgName);
            $version = null;
            if ($isInstalled) {
                try {
                    $version = InstalledVersions::getPrettyVersion($pkgName);
                } catch (\Throwable $e) {
                    $version = 'installed';
                }
            }

            $result[] = [
                'name' => $pkgName,
                'installed' => $isInstalled,
                'version' => $version,
                'command' => "composer require {$pkgName}",
            ];
        }

        return $result;
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
                'bg' => 'bg-rose-600',
                'bg_hover' => 'hover:bg-rose-700',
                'badge' => 'bg-rose-600 text-white shadow-rose-500/30',
                'active_tab' => 'bg-rose-600 text-white shadow-sm font-medium',
                'ring' => 'focus:ring-rose-500 focus:border-rose-500',
                'text' => 'text-rose-600 dark:text-rose-400',
                'hex' => '#f43f5e',
            ],
            'violet' => [
                'name' => 'violet',
                'bg' => 'bg-violet-600',
                'bg_hover' => 'hover:bg-violet-700',
                'badge' => 'bg-violet-600 text-white shadow-violet-500/30',
                'active_tab' => 'bg-violet-600 text-white shadow-sm font-medium',
                'ring' => 'focus:ring-violet-500 focus:border-violet-500',
                'text' => 'text-violet-600 dark:text-violet-400',
                'hex' => '#8b5cf6',
            ],
            default => [
                'name' => 'indigo',
                'bg' => 'bg-indigo-600',
                'bg_hover' => 'hover:bg-indigo-700',
                'badge' => 'bg-indigo-600 text-white shadow-indigo-500/30',
                'active_tab' => 'bg-indigo-600 text-white shadow-sm font-medium',
                'ring' => 'focus:ring-indigo-500 focus:border-indigo-500',
                'text' => 'text-indigo-600 dark:text-indigo-400',
                'hex' => '#6366f1',
            ],
        };
    }

    public function render()
    {
        return view('minios::apps.katalog.index', [
            'applications' => $this->applications,
            'catalogApps' => $this->catalogApps,
            'stats' => $this->stats,
            'accent' => $this->accent,
            'themes' => $this->themes,
            'topCharts' => $this->topCharts,
            'effectiveNav' => $this->effectiveNav,
        ]);
    }
}
