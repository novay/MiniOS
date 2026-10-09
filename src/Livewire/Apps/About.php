<?php

namespace Novay\MiniOS\Livewire\Apps;

use Composer\InstalledVersions;
use Livewire\Component;
use Novay\MiniOS\Concerns\HasTranslations;

class About extends Component
{
    use HasTranslations;

    public string $activeTab = 'specs';

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['specs', 'about'])) {
            $this->activeTab = $tab;
        }
    }

    public function getMiniosVersionProperty(): string
    {
        if (class_exists(InstalledVersions::class) && InstalledVersions::isInstalled('novay/minios')) {
            $ver = InstalledVersions::getPrettyVersion('novay/minios');
            if ($ver && $ver !== '') {
                return ltrim($ver, 'v');
            }
        }

        return '1.0.0';
    }

    public function getBuildNumberProperty(): string
    {
        return 'Build 2026.10';
    }

    public function getLivewireVersionProperty(): string
    {
        if (class_exists(InstalledVersions::class) && InstalledVersions::isInstalled('livewire/livewire')) {
            $ver = InstalledVersions::getPrettyVersion('livewire/livewire');
            if ($ver && $ver !== '') {
                return 'v'.ltrim($ver, 'v');
            }
        }

        return 'v4';
    }

    public function getFluxVersionProperty(): string
    {
        if (class_exists(InstalledVersions::class) && InstalledVersions::isInstalled('livewire/flux')) {
            $ver = InstalledVersions::getPrettyVersion('livewire/flux');
            if ($ver && $ver !== '') {
                return 'v'.ltrim($ver, 'v');
            }
        }

        return 'v2';
    }

    public function getLaravelVersionProperty(): string
    {
        return 'v'.app()->version();
    }

    public function getPhpVersionProperty(): string
    {
        return PHP_VERSION;
    }

    public function getPhpSapiProperty(): string
    {
        return php_sapi_name();
    }

    public function getHostOsProperty(): string
    {
        return PHP_OS_FAMILY.' ('.php_uname('m').')';
    }

    public function getHostnameProperty(): string
    {
        return gethostname() ?: 'MiniOS-Device';
    }

    public function getProcessorProperty(): string
    {
        if (! function_exists('shell_exec')) {
            return php_uname('m').' Processor';
        }

        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        if (in_array('shell_exec', $disabled, true)) {
            return php_uname('m').' Processor';
        }

        $cpu = trim((string) @shell_exec('sysctl -n machdep.cpu.brand_string 2>/dev/null || grep "model name" /proc/cpuinfo 2>/dev/null | head -n 1 | cut -d: -f2'));

        return $cpu !== '' ? $cpu : php_uname('m').' Processor';
    }

    public function getInstalledRamProperty(): string
    {
        if (! function_exists('shell_exec')) {
            return ini_get('memory_limit') ?: 'N/A';
        }

        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        if (in_array('shell_exec', $disabled, true)) {
            return ini_get('memory_limit') ?: 'N/A';
        }

        $bytes = (int) trim((string) @shell_exec('sysctl -n hw.memsize 2>/dev/null || grep MemTotal /proc/meminfo 2>/dev/null | awk \'{print $2*1024}\''));

        if ($bytes > 0) {
            return round($bytes / 1073741824, 1).' GB';
        }

        return ini_get('memory_limit') ?: 'N/A';
    }

    public function getDbConnectionProperty(): string
    {
        return config('database.default', 'sqlite');
    }

    public function getUserNameProperty(): string
    {
        return auth()->user()->name ?? 'Pengguna MiniOS';
    }

    public function getUserEmailProperty(): string
    {
        return auth()->user()->email ?? 'user@minios.local';
    }

    public function getAccentProperty(): array
    {
        $name = os_setting()->get('appearance.accent_color', 'indigo');

        $map = [
            'indigo' => ['name' => 'indigo', 'hex' => '#4f46e5', 'text' => 'text-indigo-600 dark:text-indigo-400', 'bg' => 'bg-indigo-600'],
            'zinc' => ['name' => 'zinc', 'hex' => '#27272a', 'text' => 'text-zinc-600 dark:text-zinc-400', 'bg' => 'bg-zinc-700'],
            'emerald' => ['name' => 'emerald', 'hex' => '#10b981', 'text' => 'text-emerald-600 dark:text-emerald-400', 'bg' => 'bg-emerald-600'],
            'sky' => ['name' => 'sky', 'hex' => '#0ea5e9', 'text' => 'text-sky-600 dark:text-sky-400', 'bg' => 'bg-sky-500'],
            'amber' => ['name' => 'amber', 'hex' => '#f59e0b', 'text' => 'text-amber-600 dark:text-amber-400', 'bg' => 'bg-amber-500'],
            'rose' => ['name' => 'rose', 'hex' => '#f43f5e', 'text' => 'text-rose-600 dark:text-rose-400', 'bg' => 'bg-rose-500'],
        ];

        return $map[$name] ?? $map['indigo'];
    }

    public function getLocaleProperty(): string
    {
        return $this->getActiveLocale();
    }

    public function getCopySpecsTextProperty(): string
    {
        return "MiniOS Web Desktop (Windows 11 Fluent Edition)\n"
            ."{$this->t('device_name')}: {$this->hostname}\n"
            ."{$this->t('processor')}: {$this->processor}\n"
            ."{$this->t('memory')}: {$this->installedRam}\n"
            ."{$this->t('user')}: {$this->userName}\n"
            ."{$this->t('host_system')}: {$this->hostOs}\n"
            ."{$this->t('runtime')}: PHP {$this->phpVersion} ({$this->phpSapi})\n"
            ."{$this->t('framework')}: Laravel {$this->laravelVersion} & Livewire {$this->livewireVersion}\n"
            ."{$this->t('ui_components')}: Flux UI {$this->fluxVersion} & Tailwind CSS\n"
            ."{$this->t('database')}: {$this->dbConnection}";
    }

    public function render()
    {
        $view = view()->exists('pages.minios.apps.about')
            ? 'pages.minios.apps.about'
            : 'minios::apps.about';

        return view($view, [
            'accent' => $this->accent,
        ]);
    }
}
