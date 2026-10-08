<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MiniOS Applications
    |--------------------------------------------------------------------------
    |
    | Additional application manifests that should be automatically registered
    | into MiniOS on boot.
    |
    */

    'apps' => [
        // \App\MiniOS\Todo\TodoApp::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Desktop Routes Middleware
    |--------------------------------------------------------------------------
    |
    | Define the middleware stack applied to desktop workspace routes.
    | Set to null to dynamically apply Fortify email verification if enabled,
    | or define explicitly (e.g. ['web', 'auth']) to disable email verification requirement.
    |
    */

    'middleware' => null,

    /*
    |--------------------------------------------------------------------------
    | Default Desktop Settings
    |--------------------------------------------------------------------------
    |
    | Default preferences used across MiniOS components whenever user setting
    | has not been set or when a category is reset to default.
    |
    */

    'settings' => [
        'appearance' => [
            'theme' => 'system',
            'accent_color' => 'indigo',
            'font_family' => 'inter',
            'wallpaper' => 'wall-1',
            'panel_blur' => true,
            'icon_size' => 'medium',
        ],

        'dock' => [
            'size' => 'medium',
            'position' => 'bottom',
            'autohide' => false,
            'show_indicators' => true,
            'pinned_apps' => ['settings', 'files', 'terminal'],
        ],

        'window_manager' => [
            'restore_session' => true,
            'remember_position' => true,
            'start_position' => 'center',
        ],

        'locale_time' => [
            'locale' => 'id',
            'timezone' => 'Asia/Jakarta',
            'time_format' => '24h',
            'date_format' => 'Y-m-d',
        ],

        'accessibility' => [
            'reduce_motion' => false,
            'high_contrast' => false,
            'focus_indicators' => true,
        ],

        'services' => [
            // Filesystem / Storage Driver
            'storage_driver' => 'local',
            's3_key' => '',
            's3_secret' => '',
            's3_region' => 'us-east-1',
            's3_bucket' => '',
            's3_endpoint' => '',
            's3_use_path_style' => false,
            'bunny_storage_zone' => '',
            'bunny_api_key' => '',
            'bunny_region' => 'de',
            'bunny_pull_zone' => '',
            'bunny_token_auth_key' => '',

            // Email Driver
            'mail_driver' => 'log',
            'smtp_host' => '127.0.0.1',
            'smtp_port' => '587',
            'smtp_encryption' => 'tls',
            'smtp_username' => '',
            'smtp_password' => '',
            'resend_api_key' => '',
            'mail_from_address' => 'hello@example.com',
            'mail_from_name' => 'MiniOS Desktop',
        ],
    ],

];
