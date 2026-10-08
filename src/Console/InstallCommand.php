<?php

namespace Novay\MiniOS\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'minios:install {--force : Overwrite existing published files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and publish all MiniOS resources, configuration, and migrations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing MiniOS...');

        $this->comment('Publishing MiniOS Configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'minios-config',
            '--force' => $this->option('force'),
        ]);

        $this->comment('Publishing MiniOS Migrations...');
        $this->call('vendor:publish', [
            '--tag' => 'minios-migrations',
            '--force' => $this->option('force'),
        ]);

        $this->comment('Publishing MiniOS Assets (Images & Wallpapers)...');
        $this->call('vendor:publish', [
            '--tag' => 'minios-assets',
            '--force' => $this->option('force'),
        ]);

        $this->comment('Publishing MiniOS Agent Skills...');
        $this->call('vendor:publish', [
            '--tag' => 'minios-skills',
            '--force' => $this->option('force'),
        ]);

        $this->configureFrontend();
        $this->configureRoutes();
        $this->configureFortify();

        $this->info('MiniOS has been successfully installed!');

        return Command::SUCCESS;
    }

    /**
     * Configure frontend integration (Vite aliases, Tailwind CSS, Alpine/Livewire JS).
     */
    protected function configureFrontend(): void
    {
        $this->comment('Configuring frontend integration (Vite, CSS, JS)...');

        $this->configureVite();
        $this->configureAppCss();
        $this->configureAppJs();
    }

    /**
     * Inject @minios aliases into vite.config.js if not already present.
     */
    protected function configureVite(): void
    {
        $vitePath = base_path('vite.config.js');
        if (! File::exists($vitePath)) {
            return;
        }

        $content = File::get($vitePath);
        if (str_contains($content, '@minios')) {
            $this->line('  <info>✓</info> vite.config.js already contains @minios alias.');

            return;
        }

        // 1. Ensure node:path and fileURLToPath imports exist
        if (! str_contains($content, "from 'node:path'") && ! str_contains($content, 'from "node:path"')) {
            $content = "import path from 'node:path';\nimport { fileURLToPath } from 'node:url';\n\nconst __dirname = path.dirname(fileURLToPath(import.meta.url));\n\n".$content;
        } elseif (! str_contains($content, '__dirname')) {
            $content = "const __dirname = path.dirname(fileURLToPath(import.meta.url));\n\n".$content;
        }

        // 2. Inject resolve.alias block into defineConfig
        $aliasBlock = "    resolve: {\n        alias: {\n            '@minios': path.resolve(__dirname, 'vendor/novay/minios/resources/js'),\n            '@minios-css': path.resolve(__dirname, 'vendor/novay/minios/resources/css'),\n            '@minios-img': path.resolve(__dirname, 'vendor/novay/minios/resources/img'),\n        },\n    },\n";

        if (preg_match('/(export\s+default\s+defineConfig\(\s*\{)/', $content)) {
            $content = preg_replace(
                '/(export\s+default\s+defineConfig\(\s*\{)/',
                "$1\n".$aliasBlock,
                $content,
                1
            );
            File::put($vitePath, $content);
            $this->line('  <info>✓</info> vite.config.js updated with @minios aliases.');
        } else {
            $this->line('  <comment>!</comment> Could not automatically inject aliases into vite.config.js. Refer to README.md for manual setup.');
        }
    }

    /**
     * Inject MiniOS stylesheet and Blade source scanning into resources/css/app.css.
     */
    protected function configureAppCss(): void
    {
        $cssPath = resource_path('css/app.css');
        if (! File::exists($cssPath)) {
            return;
        }

        $content = File::get($cssPath);
        if (str_contains($content, 'minios.css')) {
            $this->line('  <info>✓</info> resources/css/app.css already imports minios.css.');

            return;
        }

        $imports = "@import '../../vendor/novay/minios/resources/css/minios.css';\n@source '../../vendor/novay/minios/resources/views/**/*.blade.php';\n";

        if (str_contains($content, "@import 'tailwindcss';")) {
            $content = str_replace("@import 'tailwindcss';", "@import 'tailwindcss';\n".$imports, $content);
        } else {
            $content = $imports."\n".$content;
        }

        File::put($cssPath, $content);
        $this->line('  <info>✓</info> resources/css/app.css updated with MiniOS styles and view scanning.');
    }

    /**
     * Inject MiniOS Livewire & Alpine registration into resources/js/app.js.
     */
    protected function configureAppJs(): void
    {
        $jsPath = resource_path('js/app.js');
        if (! File::exists($jsPath)) {
            return;
        }

        $content = File::get($jsPath);
        if (str_contains($content, 'Livewire.start()') && str_contains($content, 'minios')) {
            $this->line('  <info>✓</info> resources/js/app.js already configures Livewire and MiniOS.');

            return;
        }

        // If app.js already has the old partial registration without Livewire.start(), clean it up
        if (str_contains($content, 'minios') && ! str_contains($content, 'Livewire.start()')) {
            $content = preg_replace(
                '/\n?import\s+minios\s+from\s+[\'"]@minios\/minios[\'"];[\s\S]*?(?:}\n|(?=\n\S|\z))/',
                '',
                $content
            ) ?? $content;
        }

        $registration = "\nimport {\n    Livewire,\n    Alpine,\n} from '../../vendor/livewire/livewire/dist/livewire.esm';\nimport minios from '@minios/minios';\n\nAlpine.data('minios', minios);\n\nLivewire.start();\n";

        $content = rtrim($content)."\n".$registration;
        File::put($jsPath, $content);
        $this->line('  <info>✓</info> resources/js/app.js updated with MiniOS Livewire & Alpine setup.');
    }

    /**
     * Inject MiniOS::routes() catch-all at the bottom of routes/web.php.
     */
    protected function configureRoutes(): void
    {
        $routePath = base_path('routes/web.php');
        if (! File::exists($routePath)) {
            return;
        }

        $content = File::get($routePath);
        if (str_contains($content, 'MiniOS::routes()')) {
            $this->line('  <info>✓</info> routes/web.php already registers MiniOS routes.');

            return;
        }

        $import = str_contains($content, 'use Novay\MiniOS\Facades\MiniOS;')
            ? ''
            : "use Novay\MiniOS\Facades\MiniOS;\n\n";

        $snippet = "\n// MiniOS Desktop Routes\n".$import."MiniOS::routes();\n";

        File::append($routePath, $snippet);
        $this->line('  <info>✓</info> routes/web.php updated with MiniOS::routes() at the bottom.');
    }

    /**
     * Configure Laravel Fortify with MiniOS authentication views if Fortify is detected.
     */
    protected function configureFortify(): void
    {
        $fortifyPath = app_path('Providers/FortifyServiceProvider.php');
        if (! File::exists($fortifyPath)) {
            $this->line('  <comment>i</comment> FortifyServiceProvider not detected (using existing application authentication).');

            return;
        }

        $content = File::get($fortifyPath);
        if (str_contains($content, 'MiniOS::fortify()')) {
            $this->line('  <info>✓</info> FortifyServiceProvider already configures MiniOS authentication.');

            return;
        }

        // Add import if missing
        if (! str_contains($content, 'use Novay\MiniOS\Facades\MiniOS;')) {
            if (preg_match('/(namespace\s+App\\\\Providers;)/', $content)) {
                $content = preg_replace(
                    '/(namespace\s+App\\\\Providers;)/',
                    "$1\n\nuse Novay\MiniOS\Facades\MiniOS;",
                    $content,
                    1
                );
            }
        }

        // Inject MiniOS::fortify() into boot()
        if (preg_match('/(public\s+function\s+boot\s*\([^)]*\)\s*:\s*void\s*\{)/', $content)) {
            $content = preg_replace(
                '/(public\s+function\s+boot\s*\([^)]*\)\s*:\s*void\s*\{)/',
                "$1\n        MiniOS::fortify();",
                $content,
                1
            );
            File::put($fortifyPath, $content);
            $this->line('  <info>✓</info> FortifyServiceProvider.php updated with MiniOS authentication views.');
        } else {
            $this->line('  <comment>!</comment> Could not find boot() in FortifyServiceProvider.php. Add MiniOS::fortify() manually.');
        }
    }
}
