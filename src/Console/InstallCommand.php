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
    protected $signature = 'minios:install 
                            {--force : Overwrite existing published files}
                            {--full : Install the complete Web Desktop OS}
                            {--ui-kit : Install UI Kit components and styles only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and publish MiniOS resources, configuration, and migrations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $mode = $this->determineInstallationMode();

        $this->info($mode === 'ui-kit' ? 'Installing MiniOS UI Kit...' : 'Installing MiniOS Full Desktop OS...');

        $this->comment('Publishing MiniOS Configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'minios-config',
            '--force' => $this->option('force'),
        ]);

        if ($mode === 'full') {
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
        }

        $this->comment('Publishing MiniOS Compiled Assets (minios.min.js & minios.css)...');
        $this->call('vendor:publish', [
            '--tag' => 'minios-dist',
            '--force' => $this->option('force'),
        ]);

        $this->comment('Publishing MiniOS Agent Skills...');
        $this->call('vendor:publish', [
            '--tag' => 'minios-skills',
            '--force' => $this->option('force'),
        ]);

        $this->configureFrontend($mode);

        if ($mode === 'full') {
            $this->configureRoutes();
            $this->configureFortify();
        }

        if ($mode === 'ui-kit') {
            $this->info('MiniOS UI Kit has been successfully installed!');
            $this->line('  <comment>Tip:</comment> You can now use <x-minios::...> components, @miniosStyles, and @miniosScripts in any Blade view.');
        } else {
            $this->info('MiniOS Full Desktop OS has been successfully installed!');
        }

        return Command::SUCCESS;
    }

    /**
     * Determine whether to install Full Desktop OS or UI Kit only.
     */
    protected function determineInstallationMode(): string
    {
        if ($this->option('full')) {
            return 'full';
        }

        if ($this->option('ui-kit')) {
            return 'ui-kit';
        }

        return $this->choice(
            'What would you like to install?',
            [
                'full' => 'Full Desktop OS',
                'ui-kit' => 'UI Kit Only',
            ],
            'full'
        );
    }

    /**
     * Configure frontend integration (Vite aliases, Tailwind CSS, Alpine/Livewire JS).
     */
    protected function configureFrontend(string $mode = 'full'): void
    {
        $this->comment('Configuring frontend integration (Vite, CSS)...');

        $this->configureVite();
        $this->configureAppCss();

        if ($mode === 'full') {
            $this->configureAppJs();
        }
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
        $aliasBlock = "    resolve: {\n        alias: {\n            '@minios/minios': path.resolve(__dirname, 'vendor/novay/minios/dist/minios.esm.js'),\n            '@minios': path.resolve(__dirname, 'vendor/novay/minios/dist/minios.esm.js'),\n            '@minios-css': path.resolve(__dirname, 'vendor/novay/minios/dist/minios.css'),\n            '@minios-img': path.resolve(__dirname, 'vendor/novay/minios/resources/img'),\n        },\n    },\n";

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

        $imports = "@import '../../vendor/novay/minios/dist/minios.css';\n@source '../../vendor/novay/minios/resources/views/**/*.blade.php';\n";

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

        // If MiniOS::fortify() is already present, ensure it sits AFTER configureViews()
        if (str_contains($content, 'MiniOS::fortify()')) {
            if (str_contains($content, '$this->configureViews();')) {
                $posFortify = strpos($content, 'MiniOS::fortify()');
                $posViews = strpos($content, '$this->configureViews()');
                if ($posFortify !== false && $posViews !== false && $posFortify < $posViews) {
                    $content = preg_replace('/\s*MiniOS::fortify\(\);/', '', $content);
                    $content = str_replace(
                        '$this->configureViews();',
                        "\$this->configureViews();\n        MiniOS::fortify();",
                        $content
                    );
                    File::put($fortifyPath, $content);
                    $this->line('  <info>✓</info> Repositioned MiniOS::fortify() after configureViews() in FortifyServiceProvider.php.');

                    return;
                }
            }

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

        // Inject MiniOS::fortify() after $this->configureViews() or at the bottom of boot()
        if (str_contains($content, '$this->configureViews();')) {
            $content = str_replace(
                '$this->configureViews();',
                "\$this->configureViews();\n        MiniOS::fortify();",
                $content
            );
            File::put($fortifyPath, $content);
            $this->line('  <info>✓</info> FortifyServiceProvider.php updated with MiniOS::fortify() after configureViews().');
        } elseif (preg_match('/(public\s+function\s+boot\s*\([^)]*\)\s*:\s*void\s*\{[\s\S]*?)(\n\s*\})/', $content)) {
            $content = preg_replace(
                '/(public\s+function\s+boot\s*\([^)]*\)\s*:\s*void\s*\{[\s\S]*?)(\n\s*\})/',
                "$1\n        MiniOS::fortify();$2",
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
