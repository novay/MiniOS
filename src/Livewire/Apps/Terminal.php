<?php

namespace Novay\MiniOS\Livewire\Apps;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Terminal extends Component
{
    public array $history = [];

    public string $command = '';

    public array $commandHistory = [];

    public function mount(): void
    {
        $this->history[] = [
            'type' => 'welcome',
            'output' => "MiniOS CLI Shell v2.0 (x86_64-apple-darwin)\nKetik 'help' atau 'minios' untuk bantuan perintah.\n",
        ];
    }

    public function executeCommand(): void
    {
        $input = trim($this->command);
        $this->command = '';

        if ($input === '') {
            return;
        }

        $this->commandHistory[] = $input;
        $this->history[] = [
            'type' => 'input',
            'command' => $input,
            'user' => Auth::user()->name ?? 'user',
        ];

        $parts = explode(' ', $input);
        $cmd = strtolower($parts[0]);
        $args = array_slice($parts, 1);

        switch ($cmd) {
            case 'clear':
                $this->history = [];
                break;

            case 'help':
                $this->history[] = [
                    'type' => 'output',
                    'output' => "Perintah MiniOS Terminal yang tersedia:\n".
                        "  minios      Tampilkan informasi spesifikasi sistem MiniOS\n".
                        "  whoami        Tampilkan nama pengguna aktif\n".
                        "  uname         Tampilkan versi sistem dan kernel\n".
                        "  pwd           Tampilkan direktori kerja saat ini\n".
                        "  date          Tampilkan tanggal dan waktu sistem saat ini\n".
                        "  ls            Tampilkan isi file & folder dalam direktori storage\n".
                        "  artisan       Jalankan perintah Artisan (contoh: artisan inspire, artisan about)\n".
                        "  echo [teks]   Cetak teks ke layar\n".
                        "  clear         Bersihkan layar terminal\n",
                ];
                break;

            case 'whoami':
                $user = Auth::user();
                $this->history[] = [
                    'type' => 'output',
                    'output' => ($user ? "{$user->name} <{$user->email}>" : 'guest')."\n",
                ];
                break;

            case 'pwd':
                $this->history[] = [
                    'type' => 'output',
                    'output' => storage_path()."\n",
                ];
                break;

            case 'uname':
                $this->history[] = [
                    'type' => 'output',
                    'output' => "MiniOS 2.0 Darwin Kernel Version 23.0.0 (x86_64/arm64)\n",
                ];
                break;

            case 'date':
                $this->history[] = [
                    'type' => 'output',
                    'output' => date('D M j H:i:s T Y')."\n",
                ];
                break;

            case 'echo':
                $this->history[] = [
                    'type' => 'output',
                    'output' => implode(' ', $args)."\n",
                ];
                break;

            case 'ls':
                if (! Auth::check()) {
                    $this->history[] = [
                        'type' => 'error',
                        'output' => "ls: Permission denied (Guest user). Silakan login terlebih dahulu ke MiniOS.\n",
                    ];
                    break;
                }

                $path = storage_path(implode(' ', $args));
                if (file_exists($path) && is_dir($path)) {
                    $items = scandir($path);
                    $output = [];
                    foreach ($items as $item) {
                        if ($item !== '.' && $item !== '..') {
                            $output[] = is_dir($path.'/'.$item) ? "📁 {$item}/" : "📄 {$item}";
                        }
                    }
                    $this->history[] = [
                        'type' => 'output',
                        'output' => implode('  ', $output)."\n",
                    ];
                } else {
                    $this->history[] = [
                        'type' => 'error',
                        'output' => "ls: {$path}: Direktori tidak ditemukan\n",
                    ];
                }
                break;

            case 'minios':
            case 'fastfetch':
                $user = Auth::user()->name ?? 'user';
                $phpVer = PHP_VERSION;
                $laravelVer = app()->version();
                $output = <<<ASCII
               .---.             {$user}@minios
              /     \            ----------------
             |  O  O |           OS: MiniOS 2.0 Web Desktop
             |   ^   |           Host: Simpora Engine
             |  ---  |           Kernel: PHP {$phpVer} / Laravel {$laravelVer}
              \_____/            Shell: minios-zsh 5.9
                                 Theme: Dynamic Accent
                                 Terminal: MiniOS Terminal 2.0
ASCII;
                $this->history[] = [
                    'type' => 'output',
                    'output' => $output."\n",
                ];
                break;

            case 'artisan':
            case 'php':
                $artisanCmd = ($cmd === 'php' && isset($args[0]) && $args[0] === 'artisan')
                    ? implode(' ', array_slice($args, 1))
                    : implode(' ', $args);

                if (empty($artisanCmd)) {
                    $artisanCmd = 'list';
                }

                $allowed = ['inspire', 'about', 'version', 'list', 'route:list'];
                $baseArtisan = explode(' ', $artisanCmd)[0];

                if (! in_array($baseArtisan, $allowed)) {
                    $this->history[] = [
                        'type' => 'error',
                        'output' => "MiniOS Security: Perintah 'artisan {$baseArtisan}' dibatasi di Terminal Web. Perintah yang diizinkan: ".implode(', ', $allowed)."\n",
                    ];
                } else {
                    try {
                        Artisan::call($artisanCmd);
                        $artisanOutput = Artisan::output();
                        $this->history[] = [
                            'type' => 'output',
                            'output' => $artisanOutput ?: "OK\n",
                        ];
                    } catch (\Exception $e) {
                        $this->history[] = [
                            'type' => 'error',
                            'output' => 'Gagal menjalankan artisan: '.$e->getMessage()."\n",
                        ];
                    }
                }
                break;

            case 'sl':
                $train = <<<TRAIN
      ====        ________                ___________
  _D _|  |_______/        \__I_I_____===__|_________|
   |(_)---  |   H\________/ _____   |   |           |
   /     |  |   H  |  |     |   |   |   |  CHOO     |
  |      |  |   H  |__|_____|___|___|___|  CHOO!    |
  |________|__H___________________________________|
  (________)   (O)  (O)  (O)         (O)  (O)  (O)
TRAIN;
                $this->history[] = [
                    'type' => 'output',
                    'output' => $train."\n",
                ];
                break;

            case 'cowsay':
            case 'cowthink':
                $text = implode(' ', $args) ?: 'MiniOS Web Desktop Rocks!';
                $cow = <<<COW
  < {$text} >
        \   ^__^
         \  (oo)\_______
            (__)\       )\/\
                ||----w |
                ||     ||
COW;
                $this->history[] = [
                    'type' => 'output',
                    'output' => $cow."\n",
                ];
                break;

            case 'matrix':
                $matrix = <<<'MATRIX'
01001101 01101001 01101110 01101001 01001111 01010011
Wake up, Neo...
The Matrix has you.
Follow the white rabbit. 🐇
Knock, knock, Neo.
MATRIX;
                $this->history[] = [
                    'type' => 'output',
                    'output' => $matrix."\n",
                ];
                break;

            case 'sudo':
                $subCmd = strtolower(implode(' ', $args));
                if (str_contains($subCmd, 'rm')) {
                    $output = "⚠️ [SYSTEM CRITICAL ALERT]\nSelf-destruct sequence initiated...\n3... 2... 1...\n[ABORTED]: Just kidding! MiniOS is indestructible 🛡️\n";
                } elseif (str_contains($subCmd, 'sandwich')) {
                    $output = "Okay! 🥪 Here is your delicious sandwich!\n";
                } else {
                    $output = "With great power comes great responsibility. Access Granted! 🔐\n";
                }
                $this->history[] = [
                    'type' => 'output',
                    'output' => $output,
                ];
                break;

            default:
                $this->history[] = [
                    'type' => 'error',
                    'output' => "command not found: {$input}. Ketik 'help' untuk daftar perintah.\n",
                ];
                break;
        }

        $this->dispatch('terminal-scrolled');
    }

    public function render()
    {
        $view = view()->exists('pages.minios.apps.terminal')
            ? 'pages.minios.apps.terminal'
            : 'minios::apps.terminal';

        return view($view);
    }
}
