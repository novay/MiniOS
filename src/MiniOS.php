<?php

namespace Novay\MiniOS;

use Laravel\Fortify\Fortify;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Support\AppRegistry;

class MiniOS
{
    public function __construct(
        protected AppRegistry $registry
    ) {}

    /**
     * Register a new desktop application.
     */
    public function register(DesktopApp|string|array $app): self
    {
        if (is_array($app)) {
            foreach ($app as $item) {
                $this->registry->register($item);
            }
        } else {
            $this->registry->register($app);
        }

        $registryApps = $this->registry->toArray();
        $configuredApps = config('desktop.applications', []);

        $merged = [];
        foreach ($registryApps as $id => $appData) {
            $merged[$id] = isset($configuredApps[$id])
                ? array_merge($appData, array_filter($configuredApps[$id], fn ($val) => $val !== null))
                : $appData;
        }

        foreach ($configuredApps as $id => $appData) {
            if (! isset($merged[$id])) {
                $merged[$id] = $appData;
            }
        }

        config(['desktop.applications' => $merged]);

        return $this;
    }

    /**
     * Get all registered applications.
     *
     * @return array<string, DesktopApp>
     */
    public function getApplications(): array
    {
        return $this->registry->all();
    }

    public const CORE_APPS = [
        'browser',
        'files',
        'terminal',
        'settings',
        'calculator',
        'activity-monitor',
        'about',
        'katalog',
        'control-panel',
        'preview',
        'editor',
        'player',
        'soundcloud',
        'window-manager',
    ];

    /**
     * Check if an app is a built-in core system app.
     */
    public function isCoreApp(string $id): bool
    {
        return in_array($id, self::CORE_APPS, true);
    }

    /**
     * Get list of core app identifiers.
     *
     * @return array<string>
     */
    public function getCoreAppIds(): array
    {
        return self::CORE_APPS;
    }

    /**
     * Unregister an application by ID.
     */
    public function unregister(string $id): self
    {
        $this->registry->unregister($id);

        $configuredApps = config('desktop.applications', []);
        unset($configuredApps[$id]);
        config(['desktop.applications' => $configuredApps]);

        return $this;
    }

    /**
     * Get single application by ID.
     */
    public function getApplication(string $id): ?DesktopApp
    {
        return $this->registry->get($id);
    }

    /**
     * Get configured desktop URL prefix.
     */
    public static function prefix(): string
    {
        return trim(config('minios.prefix', ''), '/');
    }

    /**
     * Get MiniOS path with configured prefix.
     */
    public static function path(?string $path = null): string
    {
        $prefix = static::prefix();
        $cleanPath = $path !== null ? trim($path, '/') : '';

        if ($prefix === '') {
            return $cleanPath === '' ? '/' : "/{$cleanPath}";
        }

        return $cleanPath === '' ? "/{$prefix}" : "/{$prefix}/{$cleanPath}";
    }

    /**
     * Get MiniOS full URL with configured prefix.
     */
    public static function url(?string $path = null): string
    {
        return url(static::path($path));
    }

    /**
     * Get the HTML script tags to load MiniOS JavaScript.
     */
    public static function scripts(): string
    {
        $published = public_path('vendor/minios/minios.min.js');
        $url = file_exists($published)
            ? asset('vendor/minios/minios.min.js')
            : route('minios.assets.js');

        return '<script src="'.$url.'" defer></script>';
    }

    /**
     * Get the HTML script & style tags for OS appearance (dark mode) handling.
     */
    public static function appearance(array $options = []): string
    {
        $nonce = isset($options['nonce']) ? ' nonce="'.$options['nonce'].'"' : '';

        return <<<HTML
<style{$nonce}>
    :root.dark {
        color-scheme: dark;
    }
</style>
<script{$nonce}>
    window.OS = {
        applyAppearance (appearance) {
            let applyDark = () => document.documentElement.classList.add('dark');
            let applyLight = () => document.documentElement.classList.remove('dark');

            if (appearance === 'system') {
                let media = window.matchMedia('(prefers-color-scheme: dark)');
                window.localStorage.removeItem('os.appearance');
                media.matches ? applyDark() : applyLight();
            } else if (appearance === 'dark') {
                window.localStorage.setItem('os.appearance', 'dark');
                applyDark();
            } else if (appearance === 'light') {
                window.localStorage.setItem('os.appearance', 'light');
                applyLight();
            }
        }
    };

    window.OS.applyAppearance(window.localStorage.getItem('os.appearance') || 'system');
</script>
HTML;
    }

    /**
     * Get the HTML script tag to load OS UI component scripts.
     */
    public static function osScripts(array $options = []): string
    {
        $nonce = isset($options['nonce']) ? ' nonce="'.$options['nonce'].'"' : '';
        $published = public_path('vendor/minios/ui.min.js');
        $url = file_exists($published)
            ? asset('vendor/minios/ui.min.js')
            : asset('vendor/minios/ui.js');

        return '<script src="'.$url.'" data-navigate-once'.$nonce.' defer></script>';
    }

    /**
     * Get the HTML link tag to load OS UI component CSS.
     */
    public static function osStyles(array $options = []): string
    {
        $nonce = isset($options['nonce']) ? ' nonce="'.$options['nonce'].'"' : '';
        $published = public_path('vendor/minios/ui.css');
        $url = file_exists($published)
            ? asset('vendor/minios/ui.css')
            : asset('vendor/minios/ui.css');

        return '<link rel="stylesheet" href="'.$url.'"'.$nonce.'>';
    }

    /**
     * Get the HTML link tag to load MiniOS CSS.
     */
    public static function styles(): string
    {
        $published = public_path('vendor/minios/minios.css');
        $url = file_exists($published)
            ? asset('vendor/minios/minios.css')
            : route('minios.assets.css');

        return '<link rel="stylesheet" href="'.$url.'">';
    }

    /**
     * Register MiniOS desktop and lock routes.
     */
    public static function routes(): void
    {
        require __DIR__.'/../routes/web.php';
    }

    /**
     * Register Fortify authentication views using MiniOS auth views.
     */
    public static function fortify(): void
    {
        if (! config('minios.fortify_views', true)) {
            return;
        }

        if (class_exists(Fortify::class)) {
            $prefix = static::prefix();
            if ($prefix !== '' && config('fortify.home') === '/') {
                config(['fortify.home' => static::path()]);
            }

            $view = fn (string $name) => view("minios::auth.{$name}");

            Fortify::loginView(fn () => $view('login'));
            Fortify::verifyEmailView(fn () => $view('verify-email'));
            Fortify::twoFactorChallengeView(fn () => $view('two-factor-challenge'));
            Fortify::confirmPasswordView(fn () => $view('confirm-password'));
            Fortify::registerView(fn () => $view('register'));
            Fortify::resetPasswordView(fn () => $view('reset-password'));
            Fortify::requestPasswordResetLinkView(fn () => $view('forgot-password'));
        }
    }

    /**
     * Get registry instance.
     */
    public function registry(): AppRegistry
    {
        return $this->registry;
    }
}
