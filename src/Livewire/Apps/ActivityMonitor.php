<?php

namespace Novay\MiniOS\Livewire\Apps;

use Composer\InstalledVersions;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Novay\MiniOS\Concerns\HasTranslations;

class ActivityMonitor extends Component
{
    use HasTranslations;

    public string $activeTab = 'processes';

    public string $searchProcess = '';

    public string $processFilter = 'all';

    public ?int $selectedPid = null;

    public string $searchPackage = '';

    public string $packageTypeFilter = 'all';

    public ?string $feedbackMessage = null;

    public ?string $feedbackType = 'info';

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['processes', 'performance', 'disk', 'system', 'cpu', 'memory'])) {
            if ($tab === 'cpu' || $tab === 'memory') {
                $this->activeTab = 'performance';
            } else {
                $this->activeTab = $tab;
            }
        }
    }

    public function setProcessFilter(string $filter): void
    {
        if (in_array($filter, ['all', 'system', 'minios'])) {
            $this->processFilter = $filter;
            $this->selectedPid = null;
        }
    }

    public function setPackageFilter(string $filter): void
    {
        if (in_array($filter, ['all', 'prod', 'dev'])) {
            $this->packageTypeFilter = $filter;
        }
    }

    public function selectProcess(int $pid): void
    {
        $this->selectedPid = $this->selectedPid === $pid ? null : $pid;
    }

    public function endProcess(?int $pid = null): void
    {
        $targetPid = $pid ?? $this->selectedPid;
        if (! $targetPid) {
            return;
        }

        // Virtual MiniOS process (PID 101 - 107)
        if ($targetPid >= 101 && $targetPid <= 107) {
            $this->feedbackMessage = $this->trans('msg_virtual_refreshed', ['pid' => $targetPid]);
            $this->feedbackType = 'success';
            if ($this->selectedPid === $targetPid) {
                $this->selectedPid = null;
            }

            return;
        }

        // Real OS process
        if ($this->isShellSupported && function_exists('posix_kill')) {
            try {
                $res = @posix_kill($targetPid, 15);
                if ($res) {
                    $this->feedbackMessage = $this->trans('msg_kill_success', ['pid' => $targetPid]);
                    $this->feedbackType = 'success';
                } else {
                    $this->feedbackMessage = $this->trans('msg_kill_denied', ['pid' => $targetPid]);
                    $this->feedbackType = 'error';
                }
            } catch (\Throwable $e) {
                $this->feedbackMessage = $this->trans('msg_kill_error', ['error' => $e->getMessage()]);
                $this->feedbackType = 'error';
            }
        } elseif ($this->isShellSupported) {
            @shell_exec('kill '.(int) $targetPid.' 2>&1');
            $this->feedbackMessage = $this->trans('msg_kill_sent', ['pid' => $targetPid]);
            $this->feedbackType = 'info';
        } else {
            $this->feedbackMessage = $this->trans('msg_shell_restricted');
            $this->feedbackType = 'warning';
        }

        if ($this->selectedPid === $targetPid) {
            $this->selectedPid = null;
        }
    }

    public function getIsShellSupportedProperty(): bool
    {
        if (! function_exists('shell_exec')) {
            return false;
        }

        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        if (in_array('shell_exec', $disabled, true)) {
            return false;
        }

        return true;
    }

    public function getCpuCoresProperty(): int
    {
        if (! $this->isShellSupported) {
            return 1;
        }

        $cores = (int) trim((string) @shell_exec('sysctl -n hw.ncpu 2>/dev/null || nproc 2>/dev/null'));

        return $cores > 0 ? $cores : 1;
    }

    public function getCpuModelProperty(): string
    {
        if (! $this->isShellSupported) {
            return 'Host CPU';
        }

        $model = trim((string) @shell_exec('sysctl -n machdep.cpu.brand_string 2>/dev/null || grep "model name" /proc/cpuinfo 2>/dev/null | head -n 1 | cut -d: -f2'));

        return $model !== '' ? $model : php_uname('m').' Processor';
    }

    public function getTotalPhysicalRamProperty(): string
    {
        if (! $this->isShellSupported) {
            return ini_get('memory_limit') ?: 'N/A';
        }

        $bytes = (int) trim((string) @shell_exec('sysctl -n hw.memsize 2>/dev/null || grep MemTotal /proc/meminfo 2>/dev/null | awk \'{print $2*1024}\''));

        return $bytes > 0 ? $this->formatBytes($bytes) : 'N/A';
    }

    public function getSystemUptimeProperty(): string
    {
        if (! $this->isShellSupported) {
            return 'Sistem Aktif';
        }

        $raw = (string) @shell_exec('uptime 2>/dev/null');
        if (preg_match('/up\s+([^,]+(?:,\s*[^,]+)?),/i', $raw, $matches)) {
            return trim($matches[1]);
        }

        return 'Sistem Aktif';
    }

    public function getSystemStatsProperty(): array
    {
        $memUsage = memory_get_usage(true);
        $memPeak = memory_get_peak_usage(true);
        $memLimit = ini_get('memory_limit');

        $storageTotal = disk_total_space(storage_path());
        $storageFree = disk_free_space(storage_path());
        $storageUsed = $storageTotal - $storageFree;
        $storagePercent = round(($storageUsed / $storageTotal) * 100, 1);

        $dbStatus = 'Connected';
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbStatus = 'Disconnected';
        }

        $cores = $this->cpuCores;
        $loadAvg = function_exists('sys_getloadavg') ? sys_getloadavg() : null;

        if ($loadAvg && count($loadAvg) >= 3 && $cores > 0) {
            $cpuPercent = round(min(100, max(0.1, ($loadAvg[0] / $cores) * 100)), 1);
            $loadStr = round($loadAvg[0], 2).' (1m) • '.round($loadAvg[1], 2).' (5m) • '.round($loadAvg[2], 2).' (15m)';
        } else {
            $cpuPercent = 2.5;
            $loadStr = 'Load Average: N/A';
        }

        return [
            'cpu_percent' => $cpuPercent,
            'cpu_load_str' => $loadStr,
            'cpu_model' => $this->cpuModel,
            'cpu_cores' => $cores,
            'total_ram' => $this->totalPhysicalRam,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage' => $this->formatBytes($memUsage),
            'memory_peak' => $this->formatBytes($memPeak),
            'memory_limit' => $memLimit,
            'storage_total' => $this->formatBytes((int) $storageTotal),
            'storage_used' => $this->formatBytes((int) $storageUsed),
            'storage_free' => $this->formatBytes((int) $storageFree),
            'storage_percent' => $storagePercent,
            'db_status' => $dbStatus,
            'opcache_enabled' => function_exists('opcache_get_status') && ! empty(opcache_get_status(false)),
            'server_os' => PHP_OS_FAMILY.' ('.php_uname('m').')',
            'uptime' => $this->systemUptime,
            'is_shell_supported' => $this->isShellSupported,
        ];
    }

    public function getCpuWavePathProperty(): array
    {
        $cpu = $this->systemStats['cpu_percent'];
        $baseY = 150 - ($cpu * 1.3);
        $points = [];
        for ($i = 0; $i <= 10; $i++) {
            $x = $i * 50;
            $offset = ($i % 2 === 0 ? 1 : -1) * min(15, max(2, $cpu * 0.15));
            $y = max(10, min(140, $baseY + $offset));
            $points[] = "{$x} {$y}";
        }
        $linePath = 'M '.implode(' L ', $points);
        $areaPath = $linePath.' L 500 150 L 0 150 Z';

        return [
            'line' => $linePath,
            'area' => $areaPath,
        ];
    }

    public function getDirectorySizesProperty(): array
    {
        $directories = [
            'storage/app' => ['path' => storage_path('app'), 'desc' => 'Berkas unggahan & data lokal', 'icon' => 'folder', 'color' => 'text-amber-500'],
            'storage/app/public' => ['path' => storage_path('app/public'), 'desc' => 'Aset publik & media terbuka', 'icon' => 'globe-alt', 'color' => 'text-emerald-500'],
            'storage/logs' => ['path' => storage_path('logs'), 'desc' => 'Log aktivitas & kesalahan sistem', 'icon' => 'document-text', 'color' => 'text-rose-500'],
            'storage/framework' => ['path' => storage_path('framework'), 'desc' => 'Cache template, views, & sesi', 'icon' => 'circle-stack', 'color' => 'text-indigo-500'],
        ];

        $results = [];
        foreach ($directories as $name => $meta) {
            $size = 0;
            if (is_dir($meta['path'])) {
                try {
                    $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($meta['path'], \FilesystemIterator::SKIP_DOTS),
                        \RecursiveIteratorIterator::LEAVES_ONLY
                    );
                    foreach ($iterator as $file) {
                        if ($file->isFile()) {
                            $size += $file->getSize();
                        }
                    }
                } catch (\Throwable $e) {
                }
            }

            $results[] = [
                'name' => $name,
                'desc' => $meta['desc'],
                'icon' => $meta['icon'],
                'color' => $meta['color'],
                'raw_bytes' => $size,
                'formatted' => $this->formatBytes($size),
            ];
        }

        return $results;
    }

    protected function fetchSystemProcesses(): array
    {
        if (! $this->isShellSupported) {
            return [];
        }

        try {
            $output = @shell_exec('ps -eo pid,user,%cpu,rss,command -r 2>/dev/null || ps -eo pid,user,%cpu,rss,command --sort=-%cpu 2>/dev/null || ps aux 2>/dev/null');
            if (! $output) {
                return [];
            }

            $lines = explode("\n", trim($output));
            array_shift($lines);

            $procs = [];
            foreach (array_slice($lines, 0, 60) as $line) {
                $line = trim($line);
                if (! $line) {
                    continue;
                }
                $parts = preg_split('/\s+/', $line, 5);
                if (count($parts) < 5) {
                    continue;
                }

                $pid = (int) $parts[0];
                $user = $parts[1];
                $cpuVal = (float) $parts[2];
                $rssKb = (int) $parts[3];
                $cmd = $parts[4];

                $cmdParts = explode(' ', $cmd);
                $bin = basename($cmdParts[0]);
                $name = $bin;

                if (preg_match('/\/([^\/]+)\.app\//i', $cmd, $matches)) {
                    $name = $matches[1];
                } elseif (in_array(strtolower($bin), ['php', 'node', 'python', 'ruby', 'git']) && isset($cmdParts[1])) {
                    $name = $bin.' '.basename($cmdParts[1]);
                }

                $memoryFormatted = $rssKb < 1024
                    ? $rssKb.' KB'
                    : round($rssKb / 1024, 1).' MB';

                $procs[] = [
                    'pid' => $pid,
                    'name' => $name,
                    'command' => $cmd,
                    'user' => $user,
                    'cpu' => number_format($cpuVal, 1).'%',
                    'cpu_val' => $cpuVal,
                    'memory' => $memoryFormatted,
                    'status' => 'Running',
                    'type' => 'system',
                ];
            }

            return $procs;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getProcessesProperty(): array
    {
        $miniosProcesses = [
            ['pid' => 101, 'name' => 'MiniOS Desktop Core', 'user' => 'minios', 'cpu' => '1.8%', 'cpu_val' => 1.8, 'memory' => '24.5 MB', 'status' => 'Running', 'type' => 'minios'],
            ['pid' => 102, 'name' => 'Laravel Vite Dev Server', 'user' => 'minios', 'cpu' => '1.2%', 'cpu_val' => 1.2, 'memory' => '48.1 MB', 'status' => 'Running', 'type' => 'minios'],
            ['pid' => 103, 'name' => 'PHP 8.5 FPM Worker', 'user' => 'www-data', 'cpu' => '0.6%', 'cpu_val' => 0.6, 'memory' => '18.3 MB', 'status' => 'Running', 'type' => 'minios'],
            ['pid' => 104, 'name' => 'SQLite Database Engine', 'user' => 'minios', 'cpu' => '0.2%', 'cpu_val' => 0.2, 'memory' => '12.7 MB', 'status' => 'Running', 'type' => 'minios'],
            ['pid' => 105, 'name' => 'Livewire Reactive Hydrator', 'user' => 'minios', 'cpu' => '0.4%', 'cpu_val' => 0.4, 'memory' => '14.0 MB', 'status' => 'Running', 'type' => 'minios'],
            ['pid' => 106, 'name' => 'Fortify Authentication Guard', 'user' => 'minios', 'cpu' => '0.1%', 'cpu_val' => 0.1, 'memory' => '6.2 MB', 'status' => 'Idle', 'type' => 'minios'],
            ['pid' => 107, 'name' => 'Cache & Session Cleaner', 'user' => 'system', 'cpu' => '0.0%', 'cpu_val' => 0.0, 'memory' => '3.8 MB', 'status' => 'Idle', 'type' => 'minios'],
        ];

        $systemProcesses = $this->fetchSystemProcesses();

        if ($this->processFilter === 'minios' || ! $this->isShellSupported) {
            $all = $miniosProcesses;
        } elseif ($this->processFilter === 'system') {
            $all = ! empty($systemProcesses) ? $systemProcesses : $miniosProcesses;
        } else {
            $all = ! empty($systemProcesses)
                ? array_merge($miniosProcesses, $systemProcesses)
                : $miniosProcesses;
        }

        if ($this->searchProcess !== '') {
            $q = strtolower(trim($this->searchProcess));

            return array_values(array_filter($all, function ($p) use ($q) {
                return str_contains(strtolower($p['name']), $q)
                    || str_contains(strtolower($p['command'] ?? ''), $q)
                    || str_contains(strtolower($p['user']), $q)
                    || str_contains((string) $p['pid'], $q);
            }));
        }

        return $all;
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
     * @return array<string, mixed>
     */
    public function getEnvironmentStatsProperty(): array
    {
        return [
            'app_name' => config('app.name', 'MiniOS'),
            'app_env' => app()->environment(),
            'app_debug' => config('app.debug') ? 'Aktif (True)' : 'Nonaktif (False)',
            'app_url' => config('app.url') ?? 'http://localhost',
            'timezone' => config('app.timezone') ?? 'UTC',
            'locale' => config('app.locale') ?? 'en',
            'cache_driver' => config('cache.default') ?? 'file',
            'session_driver' => config('session.driver') ?? 'file',
            'queue_connection' => config('queue.default') ?? 'sync',
            'db_connection' => config('database.default') ?? 'sqlite',
            'mail_mailer' => config('mail.default') ?? 'log',
        ];
    }

    /**
     * @return array<int, array{name: string, constraint: string, version: string, type: string, type_label: string}>
     */
    public function getRawPackagesProperty(): array
    {
        $composerJsonPath = base_path('composer.json');
        $packages = [];

        if (file_exists($composerJsonPath)) {
            $content = json_decode((string) file_get_contents($composerJsonPath), true);
            $requires = $content['require'] ?? [];
            $devRequires = $content['require-dev'] ?? [];

            foreach ($requires as $name => $constraint) {
                if ($name === 'php') {
                    continue;
                }
                $installedVer = class_exists(InstalledVersions::class) && InstalledVersions::isInstalled($name)
                    ? InstalledVersions::getPrettyVersion($name)
                    : $constraint;

                $packages[] = [
                    'name' => $name,
                    'constraint' => $constraint,
                    'version' => $installedVer ?? $constraint,
                    'type' => 'prod',
                    'type_label' => 'Production',
                ];
            }

            foreach ($devRequires as $name => $constraint) {
                $installedVer = class_exists(InstalledVersions::class) && InstalledVersions::isInstalled($name)
                    ? InstalledVersions::getPrettyVersion($name)
                    : $constraint;

                $packages[] = [
                    'name' => $name,
                    'constraint' => $constraint,
                    'version' => $installedVer ?? $constraint,
                    'type' => 'dev',
                    'type_label' => 'Dev',
                ];
            }
        }

        return $packages;
    }

    /**
     * @return array{total: int, prod: int, dev: int}
     */
    public function getPackageCountsProperty(): array
    {
        $all = $this->rawPackages;
        $prod = count(array_filter($all, fn ($p) => $p['type'] === 'prod'));
        $dev = count(array_filter($all, fn ($p) => $p['type'] === 'dev'));

        return [
            'total' => count($all),
            'prod' => $prod,
            'dev' => $dev,
        ];
    }

    /**
     * @return array<int, array{name: string, constraint: string, version: string, type: string, type_label: string}>
     */
    public function getPackagesProperty(): array
    {
        $packages = $this->rawPackages;

        if ($this->packageTypeFilter !== 'all') {
            $packages = array_values(array_filter($packages, fn ($pkg) => $pkg['type'] === $this->packageTypeFilter));
        }

        if ($this->searchPackage !== '') {
            $q = strtolower(trim($this->searchPackage));
            $packages = array_values(array_filter(
                $packages,
                fn ($pkg) => str_contains(strtolower($pkg['name']), $q) || str_contains(strtolower((string) $pkg['version']), $q)
            ));
        }

        return $packages;
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
        $view = view()->exists('pages.minios.apps.activity-monitor')
            ? 'pages.minios.apps.activity-monitor'
            : 'minios::apps.activity-monitor';

        return view($view, [
            'accent' => $this->accent,
        ]);
    }
}
