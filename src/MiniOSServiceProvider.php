<?php

namespace Novay\MiniOS;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Novay\MiniOS\Console\InstallCommand;
use Novay\MiniOS\Console\MakeAppCommand;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Services\SettingService;
use Novay\MiniOS\Services\TrashService;
use Novay\MiniOS\Support\AppRegistry;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNAdapter;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNClient;

class MiniOSServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/minios.php', 'minios'
        );

        $this->app->singleton(AppRegistry::class, function () {
            return new AppRegistry;
        });

        $this->app->singleton(SettingService::class, function () {
            return new SettingService;
        });

        $this->app->singleton(TrashService::class, function () {
            return new TrashService;
        });

        $this->app->singleton(Services\AppCatalogService::class, function () {
            return new Services\AppCatalogService;
        });

        $this->app->singleton('novay.minios', function ($app) {
            return new MiniOS($app->make(AppRegistry::class));
        });

        $this->app->alias('novay.minios', MiniOS::class);
        $this->app->alias(SettingService::class, 'minios.settings');
        $this->app->alias(TrashService::class, 'minios.trash');
        $this->app->alias(Services\AppCatalogService::class, 'minios.catalog');
    }

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerPublishing();
        $this->registerResources();

        /** @var MiniOS $minios */
        $minios = $this->app->make('novay.minios');

        // Register default core apps
        $minios->register([
            Apps\BrowserApp::class,
            Apps\FilesApp::class,
            Apps\TerminalApp::class,
            Apps\SettingsApp::class,
            Apps\CalculatorApp::class,
            Apps\ActivityMonitorApp::class,
            Apps\AboutApp::class,
            Apps\KatalogApp::class,
            Apps\PreviewApp::class,
            Apps\EditorApp::class,
            Apps\PlayerApp::class,
            Apps\SoundcloudApp::class,
        ]);

        // Register additional configured apps from config/minios.php
        $configuredApps = config('minios.apps', []);
        if (! empty($configuredApps)) {
            $minios->register($configuredApps);
        }

        // Auto-discover custom apps in app/Apps directory
        $this->discoverCustomApps($minios);

        // Register custom BunnyCDN storage driver
        $this->registerBunnyStorageDriver();

        // Apply dynamic infrastructure configurations (Storage & Mail)
        $this->applyRuntimeConfigurations();
    }

    /**
     * Register BunnyCDN filesystem driver using PlatformCommunity Flysystem adapter.
     */
    protected function registerBunnyStorageDriver(): void
    {
        if (! class_exists(BunnyCDNAdapter::class)) {
            return;
        }

        Storage::extend('bunny', function ($app, $config) {
            $adapter = new BunnyCDNAdapter(
                new BunnyCDNClient(
                    $config['storage_zone'] ?? '',
                    $config['api_key'] ?? '',
                    $config['region'] ?? 'de',
                ),
                $config['pull_zone'] ?? '',
                $config['root'] ?? ''
            );

            if (! empty($config['token_auth_key'])) {
                $adapter->setTokenAuthKey($config['token_auth_key']);
            }

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }

    /**
     * Auto-discover custom desktop applications in app/MiniOS and app/Apps directories.
     */
    protected function discoverCustomApps(MiniOS $minios): void
    {
        $scanRoots = [
            ['path' => app_path('MiniOS'), 'namespace' => 'App\\MiniOS'],
            ['path' => app_path('Apps'), 'namespace' => 'App\\Apps'],
        ];

        foreach ($scanRoots as $root) {
            $appsDir = $root['path'];
            $nsPrefix = $root['namespace'];

            if (! is_dir($appsDir)) {
                continue;
            }

            $directories = glob($appsDir.'/*', GLOB_ONLYDIR);
            if (! $directories) {
                continue;
            }

            foreach ($directories as $dir) {
                $folderName = basename($dir);
                $manifestCandidate = $dir.'/'.$folderName.'App.php';
                if (file_exists($manifestCandidate)) {
                    $className = "{$nsPrefix}\\{$folderName}\\{$folderName}App";
                    if (class_exists($className) && is_subclass_of($className, DesktopApp::class)) {
                        $minios->register($className);
                    }
                } else {
                    foreach (glob($dir.'/*App.php') as $file) {
                        $className = "{$nsPrefix}\\{$folderName}\\".basename($file, '.php');
                        if (class_exists($className) && is_subclass_of($className, DesktopApp::class)) {
                            $minios->register($className);
                            break;
                        }
                    }
                }
            }
        }
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                MakeAppCommand::class,
            ]);
        }
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/minios.php' => config_path('minios.php'),
            ], 'minios-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'minios-migrations');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/minios'),
            ], 'minios-views');

            $this->publishes([
                __DIR__.'/../resources/img' => public_path('minios'),
            ], 'minios-assets');

            $this->publishes([
                __DIR__.'/../resources/js' => resource_path('js/vendor/minios'),
                __DIR__.'/../resources/css' => resource_path('css/vendor/minios'),
            ], 'minios-src');

            $this->publishes([
                __DIR__.'/../resources/lang' => $this->app->langPath('vendor/minios'),
            ], 'minios-lang');

            $this->publishes([
                __DIR__.'/../.agents/skills' => base_path('.agents/skills'),
            ], 'minios-skills');
        }
    }

    protected function registerResources(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'minios');
        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');

        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'minios');

        $componentsDir = __DIR__.'/../resources/views/components';
        if (is_dir($componentsDir)) {
            foreach (glob($componentsDir.'/*.blade.php') as $file) {
                $name = basename($file, '.blade.php');
                Blade::component('minios::components.'.$name, 'minios.'.$name);
            }
        }

        Blade::component('minios::components.toast.group', 'minios.toast.group');
        Blade::component('minios::components.toast.index', 'minios.toast');

        Blade::component('minios::components.menubar.index', 'minios.menubar');
        Blade::component('minios::components.menubar.menu', 'minios.menubar.menu');
        Blade::component('minios::components.menubar.item', 'minios.menubar.item');
        Blade::component('minios::components.menubar.submenu', 'minios.menubar.submenu');
        Blade::component('minios::components.menubar.checkbox', 'minios.menubar.checkbox');
        Blade::component('minios::components.menubar.radio', 'minios.menubar.radio');
        Blade::component('minios::components.menubar.separator', 'minios.menubar.separator');

        Blade::component('minios::layouts.app', 'layouts::minios.app');
        Blade::component('minios::layouts.auth', 'layouts::minios.auth');
    }

    /**
     * Apply dynamic infrastructure configurations from MiniOS settings to Laravel runtime.
     */
    protected function applyRuntimeConfigurations(): void
    {
        try {
            // 1. Filesystem / Storage Driver
            $storageDriver = os_setting('services.storage_driver');
            if ($storageDriver && in_array($storageDriver, ['local', 'public', 's3', 'bunny'])) {
                config(['filesystems.default' => $storageDriver]);

                if ($storageDriver === 's3') {
                    $key = os_setting('services.s3_key');
                    $secret = os_setting('services.s3_secret');
                    $region = os_setting('services.s3_region', 'us-east-1');
                    $bucket = os_setting('services.s3_bucket');
                    $endpoint = os_setting('services.s3_endpoint');
                    $pathStyle = (bool) os_setting('services.s3_use_path_style', false);

                    if (! empty($key)) {
                        config(['filesystems.disks.s3.key' => $key]);
                    }
                    if (! empty($secret)) {
                        config(['filesystems.disks.s3.secret' => $secret]);
                    }
                    if (! empty($region)) {
                        config(['filesystems.disks.s3.region' => $region]);
                    }
                    if (! empty($bucket)) {
                        config(['filesystems.disks.s3.bucket' => $bucket]);
                    }
                    if (! empty($endpoint)) {
                        config(['filesystems.disks.s3.endpoint' => $endpoint]);
                    }
                    config(['filesystems.disks.s3.use_path_style_endpoint' => $pathStyle]);
                } elseif ($storageDriver === 'bunny') {
                    $storageZone = os_setting('services.bunny_storage_zone');
                    $apiKey = os_setting('services.bunny_api_key');
                    $region = os_setting('services.bunny_region', 'de');
                    $pullZone = os_setting('services.bunny_pull_zone');
                    $tokenAuthKey = os_setting('services.bunny_token_auth_key');

                    config([
                        'filesystems.disks.bunny' => [
                            'driver' => 'bunny',
                            'storage_zone' => $storageZone,
                            'api_key' => $apiKey,
                            'region' => $region ?: 'de',
                            'pull_zone' => $pullZone,
                            'token_auth_key' => $tokenAuthKey,
                        ],
                    ]);
                }
            }

            // 2. Email Delivery Driver
            $mailDriver = os_setting('services.mail_driver');
            if ($mailDriver && in_array($mailDriver, ['log', 'smtp', 'sendmail', 'resend'])) {
                config(['mail.default' => $mailDriver]);

                if ($mailDriver === 'smtp') {
                    $host = os_setting('services.smtp_host');
                    $port = os_setting('services.smtp_port', '587');
                    $encryption = os_setting('services.smtp_encryption', 'tls');
                    $username = os_setting('services.smtp_username');
                    $password = os_setting('services.smtp_password');

                    if (! empty($host)) {
                        config(['mail.mailers.smtp.host' => $host]);
                    }
                    if (! empty($port)) {
                        config(['mail.mailers.smtp.port' => (int) $port]);
                    }
                    config(['mail.mailers.smtp.encryption' => $encryption === 'none' ? null : $encryption]);
                    if (! empty($username)) {
                        config(['mail.mailers.smtp.username' => $username]);
                    }
                    if (! empty($password)) {
                        config(['mail.mailers.smtp.password' => $password]);
                    }
                } elseif ($mailDriver === 'resend') {
                    $apiKey = os_setting('services.resend_api_key');
                    if (! empty($apiKey)) {
                        config(['resend.api_key' => $apiKey]);
                    }
                    config([
                        'mail.mailers.resend' => [
                            'transport' => 'resend',
                        ],
                    ]);
                }

                $fromAddress = os_setting('services.mail_from_address');
                $fromName = os_setting('services.mail_from_name');
                if (! empty($fromAddress)) {
                    config(['mail.from.address' => $fromAddress]);
                }
                if (! empty($fromName)) {
                    config(['mail.from.name' => $fromName]);
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore during migration, CLI setup, or when DB is unavailable
        }
    }
}
