<?php

namespace Novay\MiniOS\Services;

use Illuminate\Support\Facades\File;
use Novay\MiniOS\Facades\MiniOS;

class AppCatalogService
{
    /**
     * Get list of discovery / app store catalog items.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCatalog(): array
    {
        $apps = [
            [
                'id' => 'notes',
                'name' => 'Sticky Notes',
                'studly_name' => 'Notes',
                'icon' => 'document-text',
                'category' => 'productivity',
                'category_label' => 'Productivity',
                'version' => '1.0.0',
                'author' => 'MiniOS Team',
                'badge' => 'Official',
                'badge_color' => 'indigo',
                'rating' => 4.9,
                'reviews_count' => 142,
                'downloads' => '2.4k',
                'size' => '18 KB',
                'description' => 'Aplikasi catatan tempel modern bergaya desktop dengan variasi warna kertas, pencarian instan, dan penyimpanan otomatis.',
                'tags' => ['Notes', 'Productivity', 'Auto-save'],
                'features' => [
                    'Multi-card sticky notes dengan palet warna cerah',
                    'Pencarian catatan instan secara realtime',
                    'Penyimpanan lokal otomatis tanpa perlu database eksternal',
                ],
                'manifest_code' => $this->getNotesManifestCode(),
                'livewire_code' => $this->getNotesLivewireCode(),
                'view_code' => $this->getNotesViewCode(),
            ],
            [
                'id' => 'paint',
                'name' => 'Paint Studio',
                'studly_name' => 'Paint',
                'icon' => 'paint-brush',
                'category' => 'media',
                'category_label' => 'Media & Art',
                'version' => '1.1.0',
                'author' => 'MiniOS Team',
                'badge' => 'Official',
                'badge_color' => 'indigo',
                'rating' => 4.8,
                'reviews_count' => 98,
                'downloads' => '1.8k',
                'size' => '24 KB',
                'description' => 'Kanvas gambar digital interaktif untuk menggambar bebas, sketsa ide, dengan color picker lengkap, ukuran kuas, dan ekspor PNG.',
                'tags' => ['Drawing', 'Canvas', 'Art'],
                'features' => [
                    'Drawing canvas interaktif dengan mouse & stylus',
                    'Pengaturan ukuran kuas dan palet warna lengkap',
                    'Fitur Eraser dan Ekspor karya langsung ke berkas gambar PNG',
                ],
                'manifest_code' => $this->getPaintManifestCode(),
                'livewire_code' => $this->getPaintLivewireCode(),
                'view_code' => $this->getPaintViewCode(),
            ],
            [
                'id' => 'markdown',
                'name' => 'Markdown Live',
                'studly_name' => 'Markdown',
                'icon' => 'code-bracket-square',
                'category' => 'developer',
                'category_label' => 'Developer',
                'version' => '1.0.2',
                'author' => 'Community',
                'badge' => 'Verified',
                'badge_color' => 'emerald',
                'rating' => 4.9,
                'reviews_count' => 210,
                'downloads' => '3.1k',
                'size' => '22 KB',
                'description' => 'Editor Markdown dual-pane dengan pratinjau HTML langsung, pintasan formatting dokumen, cheatsheet cepat, dan tombol salin HTML.',
                'tags' => ['Markdown', 'Editor', 'HTML'],
                'features' => [
                    'Dua panel sejajar: Editor di kiri dan Pratinjau langsung di kanan',
                    'Toolbar pintasan cepat untuk Heading, Bold, List, dan Code block',
                    'Statistik kata & karakter serta tombol 1-klik Salin HTML',
                ],
                'manifest_code' => $this->getMarkdownManifestCode(),
                'livewire_code' => $this->getMarkdownLivewireCode(),
                'view_code' => $this->getMarkdownViewCode(),
            ],
            [
                'id' => 'game-2048',
                'name' => '2048 Puzzle',
                'studly_name' => 'Game2048',
                'icon' => 'puzzle-piece',
                'category' => 'games',
                'category_label' => 'Entertainment',
                'version' => '1.0.0',
                'author' => 'Community',
                'badge' => 'Popular',
                'badge_color' => 'amber',
                'rating' => 4.9,
                'reviews_count' => 320,
                'downloads' => '4.2k',
                'size' => '20 KB',
                'description' => 'Game teka-teki geser angka legendaris 2048 untuk bermain santai di desktop dengan navigasi tombol panah keyboard.',
                'tags' => ['Game', 'Casual', 'Puzzle'],
                'features' => [
                    'Mekanika permainan 2048 klasik dengan kontrol keyboard panah',
                    'Pencatatan skor terkini dan skor tertinggi (Best Score)',
                    'Transisi ubin angka halus dan tombol Mulai Ulang',
                ],
                'manifest_code' => $this->getGame2048ManifestCode(),
                'livewire_code' => $this->getGame2048LivewireCode(),
                'view_code' => $this->getGame2048ViewCode(),
            ],
            [
                'id' => 'benchmark',
                'name' => 'System Benchmark',
                'studly_name' => 'Benchmark',
                'icon' => 'bolt',
                'category' => 'utilities',
                'category_label' => 'Utilities',
                'version' => '1.2.0',
                'author' => 'MiniOS Team',
                'badge' => 'Official',
                'badge_color' => 'indigo',
                'rating' => 4.8,
                'reviews_count' => 84,
                'downloads' => '1.5k',
                'size' => '16 KB',
                'description' => 'Alat diagnostik dan pengujian kecepatan server PHP, floating-point math, memori, dan rendering desktop dengan kalkulasi skor performa.',
                'tags' => ['Benchmark', 'Diagnostic', 'Speed'],
                'features' => [
                    'Uji komputasi matematika CPU 100.000 iterasi',
                    'Uji alokasi memori dan kecepatan throughput array',
                    'Kalkulasi skor akhir sistem dengan lencana rating performa',
                ],
                'manifest_code' => $this->getBenchmarkManifestCode(),
                'livewire_code' => $this->getBenchmarkLivewireCode(),
                'view_code' => $this->getBenchmarkViewCode(),
            ],
        ];

        // Augment with installed status
        $registeredApps = MiniOS::registry()->all();
        $configuredApps = config('desktop.applications', []);

        foreach ($apps as &$app) {
            $id = $app['id'];
            $app['is_installed'] = isset($registeredApps[$id]) || isset($configuredApps[$id]);
        }

        return $apps;
    }

    /**
     * Check if a catalog app is installed.
     */
    public function isInstalled(string $id): bool
    {
        $registered = MiniOS::registry()->all();
        $configured = config('desktop.applications', []);

        return isset($registered[$id]) || isset($configured[$id]);
    }

    /**
     * Install an app from catalog with 1-click.
     *
     * @return array<string, mixed>
     */
    public function install(string $catalogId): array
    {
        $catalog = collect($this->getCatalog())->firstWhere('id', $catalogId);
        if (! $catalog) {
            throw new \RuntimeException("Katalog aplikasi [{$catalogId}] tidak ditemukan.");
        }

        if ($this->isInstalled($catalogId)) {
            throw new \RuntimeException("Aplikasi [{$catalog['name']}] sudah terpasang.");
        }

        $studly = $catalog['studly_name'];
        $kebab = $catalog['id'];
        $appDir = app_path("MiniOS/{$studly}");

        // 1. Ensure directory structure
        File::ensureDirectoryExists($appDir);
        File::ensureDirectoryExists("{$appDir}/Livewire");
        File::ensureDirectoryExists(resource_path('views/apps'));

        // 2. Write Manifest file
        $manifestPath = "{$appDir}/{$studly}App.php";
        File::put($manifestPath, $catalog['manifest_code']);

        // 3. Write Livewire component file
        $livewirePath = "{$appDir}/Livewire/{$studly}.php";
        File::put($livewirePath, $catalog['livewire_code']);

        // 4. Write Blade view file
        $viewPath = resource_path("views/apps/{$kebab}.blade.php");
        File::put($viewPath, $catalog['view_code']);

        // 5. Register dynamically into MiniOS runtime
        $className = "App\\MiniOS\\{$studly}\\{$studly}App";
        if (class_exists($className)) {
            MiniOS::register($className);
        }

        return [
            'id' => $kebab,
            'name' => $catalog['name'],
            'class' => $className,
        ];
    }

    // =========================================================================
    // CODE GENERATORS FOR CATALOG APPS
    // =========================================================================

    protected function getNotesManifestCode(): string
    {
        return <<<'PHP'
<?php

namespace App\MiniOS\Notes;

use App\MiniOS\Notes\Livewire\Notes;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Support\WindowConfig;

class NotesApp implements DesktopApp
{
    public function id(): string
    {
        return 'notes';
    }

    public function name(): string
    {
        return 'Sticky Notes';
    }

    public function icon(): string
    {
        return 'document-text';
    }

    public function entry(): string
    {
        return '/notes';
    }

    public function routes(): array
    {
        return ['/notes'];
    }

    public function isPinned(): bool
    {
        return false;
    }

    public function component(): ?string
    {
        return Notes::class;
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(760, 520)
            ->min(520, 380);
    }

    public function version(): string
    {
        return '1.0.0';
    }
}
PHP;
    }

    protected function getNotesLivewireCode(): string
    {
        return <<<'PHP'
<?php

namespace App\MiniOS\Notes\Livewire;

use Livewire\Component;

class Notes extends Component
{
    public array $notes = [];
    public string $search = '';
    public string $selectedNoteId = '';

    public function mount(): void
    {
        $this->notes = [
            [
                'id' => 'note_1',
                'title' => 'Ide Proyek MiniOS',
                'content' => "• Desain UI Fluent Windows 11\n• Integrasi Control Panel Catalog\n• Pengelolaan berkas dan terminal",
                'color' => '#FEF08A',
                'updated_at' => now()->format('d M H:i'),
            ],
            [
                'id' => 'note_2',
                'title' => 'Daftar Belanja Kopi',
                'content' => "1. Arabica Gayo 250g\n2. Susu Oat Barista\n3. Filter V60",
                'color' => '#BAE6FD',
                'updated_at' => now()->format('d M H:i'),
            ],
        ];
        $this->selectedNoteId = 'note_1';
    }

    public function createNote(): void
    {
        $id = 'note_' . uniqid();
        $colors = ['#FEF08A', '#BAE6FD', '#BBF7D0', '#FBCFE8', '#DDD6FE'];
        $randomColor = $colors[array_rand($colors)];

        $newNote = [
            'id' => $id,
            'title' => 'Catatan Baru',
            'content' => '',
            'color' => $randomColor,
            'updated_at' => now()->format('d M H:i'),
        ];

        array_unshift($this->notes, $newNote);
        $this->selectedNoteId = $id;
    }

    public function selectNote(string $id): void
    {
        $this->selectedNoteId = $id;
    }

    public function deleteNote(string $id): void
    {
        $this->notes = array_values(array_filter($this->notes, fn ($n) => $n['id'] !== $id));
        if ($this->selectedNoteId === $id) {
            $this->selectedNoteId = $this->notes[0]['id'] ?? '';
        }
    }

    public function updateCurrentTitle(string $value): void
    {
        foreach ($this->notes as &$note) {
            if ($note['id'] === $this->selectedNoteId) {
                $note['title'] = $value;
                $note['updated_at'] = now()->format('d M H:i');
                break;
            }
        }
    }

    public function updateCurrentContent(string $value): void
    {
        foreach ($this->notes as &$note) {
            if ($note['id'] === $this->selectedNoteId) {
                $note['content'] = $value;
                $note['updated_at'] = now()->format('d M H:i');
                break;
            }
        }
    }

    public function setNoteColor(string $color): void
    {
        foreach ($this->notes as &$note) {
            if ($note['id'] === $this->selectedNoteId) {
                $note['color'] = $color;
                break;
            }
        }
    }

    public function render()
    {
        $currentNote = collect($this->notes)->firstWhere('id', $this->selectedNoteId);
        $filteredNotes = empty($this->search)
            ? $this->notes
            : array_values(array_filter($this->notes, function ($note) {
                return str_contains(strtolower($note['title']), strtolower($this->search))
                    || str_contains(strtolower($note['content']), strtolower($this->search));
            }));

        return view('apps.notes', [
            'currentNote' => $currentNote,
            'filteredNotes' => $filteredNotes,
        ]);
    }
}
PHP;
    }

    protected function getNotesViewCode(): string
    {
        return <<<'BLADE'
<div class="flex h-full w-full bg-[#f8f9fa] dark:bg-[#1a1a1c] text-neutral-800 dark:text-neutral-200 select-none overflow-hidden">
    {{-- Sidebar Daftar Catatan --}}
    <aside class="w-64 shrink-0 flex flex-col border-r border-black/10 dark:border-white/10 bg-white/70 dark:bg-[#202022]/70 backdrop-blur-md p-3 gap-3">
        <div class="flex items-center justify-between gap-2">
            <h2 class="text-sm font-semibold tracking-tight text-neutral-900 dark:text-white flex items-center gap-2">
                <flux:icon name="document-text" class="size-4 text-amber-500" />
                <span>Sticky Notes</span>
            </h2>
            <button
                type="button"
                wire:click="createNote"
                class="flex items-center gap-1 rounded-md bg-amber-500 hover:bg-amber-600 px-2.5 py-1 text-xs font-medium text-white shadow-xs transition-colors"
            >
                <flux:icon name="plus" class="size-3.5 stroke-[2.5]" />
                <span>Baru</span>
            </button>
        </div>

        {{-- Pencarian --}}
        <div class="relative">
            <flux:icon name="magnifying-glass" class="size-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-neutral-400" />
            <input
                type="text"
                wire:model.live="search"
                placeholder="Cari catatan..."
                class="w-full rounded-md border border-black/10 dark:border-white/10 bg-white dark:bg-[#2b2b2b] pl-8 pr-3 py-1 text-xs focus:outline-none"
            />
        </div>

        {{-- List Catatan --}}
        <div class="flex-1 overflow-y-auto space-y-1.5 pr-0.5">
            @forelse ($filteredNotes as $note)
                <div
                    wire:click="selectNote('{{ $note['id'] }}')"
                    class="group flex flex-col rounded-lg p-2.5 text-xs cursor-pointer border transition-all {{ $selectedNoteId === $note['id'] ? 'border-amber-500 bg-white dark:bg-[#2d2d30] shadow-xs' : 'border-transparent hover:bg-black/5 dark:hover:bg-white/5' }}"
                >
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <div class="flex items-center gap-1.5 truncate">
                            <span class="size-2 rounded-full shrink-0" style="background-color: {{ $note['color'] }};"></span>
                            <span class="font-semibold truncate text-neutral-900 dark:text-white">{{ $note['title'] ?: 'Tanpa Judul' }}</span>
                        </div>
                        <button
                            type="button"
                            wire:click.stop="deleteNote('{{ $note['id'] }}')"
                            class="opacity-0 group-hover:opacity-100 text-neutral-400 hover:text-rose-500 transition-opacity p-0.5"
                            title="Hapus"
                        >
                            <flux:icon name="trash" class="size-3.5" />
                        </button>
                    </div>
                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400 line-clamp-2 leading-relaxed">
                        {{ $note['content'] ?: 'Belum ada isi catatan...' }}
                    </p>
                    <span class="text-[10px] text-neutral-400 dark:text-neutral-500 mt-1">{{ $note['updated_at'] }}</span>
                </div>
            @empty
                <div class="py-8 text-center text-xs text-neutral-400">Tidak ada catatan</div>
            @endforelse
        </div>
    </aside>

    {{-- Editor Area --}}
    <main class="flex-1 flex flex-col p-4 overflow-hidden">
        @if ($currentNote)
            <div class="flex items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-2.5 mb-3">
                <input
                    type="text"
                    value="{{ $currentNote['title'] }}"
                    wire:change="updateCurrentTitle($event.target.value)"
                    class="w-full text-base font-bold bg-transparent border-0 text-neutral-900 dark:text-white focus:outline-none"
                    placeholder="Judul catatan..."
                />
                {{-- Color Picker --}}
                <div class="flex items-center gap-1 shrink-0">
                    @foreach (['#FEF08A', '#BAE6FD', '#BBF7D0', '#FBCFE8', '#DDD6FE'] as $c)
                        <button
                            type="button"
                            wire:click="setNoteColor('{{ $c }}')"
                            class="size-4.5 rounded-full border border-black/15 shadow-2xs hover:scale-110 transition-transform {{ $currentNote['color'] === $c ? 'ring-2 ring-amber-500 ring-offset-1' : '' }}"
                            style="background-color: {{ $c }};"
                        ></button>
                    @endforeach
                </div>
            </div>

            <textarea
                wire:change="updateCurrentContent($event.target.value)"
                class="flex-1 w-full p-3 rounded-xl border border-black/5 dark:border-white/5 resize-none text-sm leading-relaxed focus:outline-none text-neutral-800 dark:text-neutral-100"
                style="background-color: {{ $currentNote['color'] }}15;"
                placeholder="Tuliskan ide atau catatan Anda di sini..."
            >{{ $currentNote['content'] }}</textarea>
        @else
            <div class="flex-1 flex flex-col items-center justify-center text-neutral-400 gap-2">
                <flux:icon name="document-text" class="size-8 opacity-40" />
                <p class="text-xs">Pilih catatan atau buat catatan baru</p>
            </div>
        @endif
    </main>
</div>
BLADE;
    }

    protected function getPaintManifestCode(): string
    {
        return <<<'PHP'
<?php

namespace App\MiniOS\Paint;

use App\MiniOS\Paint\Livewire\Paint;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Support\WindowConfig;

class PaintApp implements DesktopApp
{
    public function id(): string
    {
        return 'paint';
    }

    public function name(): string
    {
        return 'Paint Studio';
    }

    public function icon(): string
    {
        return 'paint-brush';
    }

    public function entry(): string
    {
        return '/paint';
    }

    public function routes(): array
    {
        return ['/paint'];
    }

    public function isPinned(): bool
    {
        return false;
    }

    public function component(): ?string
    {
        return Paint::class;
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(820, 560)
            ->min(580, 400);
    }

    public function version(): string
    {
        return '1.1.0';
    }
}
PHP;
    }

    protected function getPaintLivewireCode(): string
    {
        return <<<'PHP'
<?php

namespace App\MiniOS\Paint\Livewire;

use Livewire\Component;

class Paint extends Component
{
    public function render()
    {
        return view('apps.paint');
    }
}
PHP;
    }

    protected function getPaintViewCode(): string
    {
        return <<<'BLADE'
<div
    x-data="{
        color: '#000000',
        size: 4,
        isDrawing: false,
        isEraser: false,
        canvas: null,
        ctx: null,
        init() {
            this.$nextTick(() => {
                this.canvas = this.$refs.paintCanvas;
                this.ctx = this.canvas.getContext('2d');
                this.resizeCanvas();
            });
        },
        resizeCanvas() {
            const rect = this.$refs.canvasContainer.getBoundingClientRect();
            if (rect.width && rect.height) {
                // Keep drawing buffer
                const temp = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
                this.canvas.width = rect.width - 20;
                this.canvas.height = rect.height - 20;
                this.ctx.fillStyle = '#ffffff';
                this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
                try { this.ctx.putImageData(temp, 0, 0); } catch(e) {}
            }
        },
        startDraw(e) {
            this.isDrawing = true;
            this.draw(e);
        },
        stopDraw() {
            this.isDrawing = false;
            this.ctx.beginPath();
        },
        draw(e) {
            if (!this.isDrawing) return;
            const rect = this.canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            this.ctx.lineWidth = this.size;
            this.ctx.lineCap = 'round';
            this.ctx.strokeStyle = this.isEraser ? '#ffffff' : this.color;

            this.ctx.lineTo(x, y);
            this.ctx.stroke();
            this.ctx.beginPath();
            this.ctx.moveTo(x, y);
        },
        clearCanvas() {
            if (confirm('Bersihkan kanvas seluruhnya?')) {
                this.ctx.fillStyle = '#ffffff';
                this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
            }
        },
        download() {
            const link = document.createElement('a');
            link.download = 'paint-drawing.png';
            link.href = this.canvas.toDataURL();
            link.click();
        }
    }"
    class="flex h-full w-full flex-col bg-[#f0f2f5] dark:bg-[#18181b] select-none overflow-hidden"
>
    {{-- Paint Toolbar --}}
    <header class="flex shrink-0 items-center justify-between gap-3 border-b border-black/10 dark:border-white/10 bg-white/80 dark:bg-[#202023]/80 p-2.5 backdrop-blur-md">
        <div class="flex items-center gap-2">
            <span class="font-semibold text-xs text-neutral-800 dark:text-neutral-100 flex items-center gap-1.5 mr-2">
                <flux:icon name="paint-brush" class="size-4 text-indigo-500" />
                <span>Paint Studio</span>
            </span>

            {{-- Color Palette --}}
            <div class="flex items-center gap-1 bg-black/5 dark:bg-white/5 p-1 rounded-md">
                <template x-for="c in ['#000000', '#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6', '#a855f7']" :key="c">
                    <button
                        type="button"
                        @click="color = c; isEraser = false"
                        :style="`background-color: ${c}`"
                        class="size-5 rounded border border-black/20 hover:scale-110 transition-transform"
                        :class="color === c && !isEraser ? 'ring-2 ring-indigo-500 ring-offset-1' : ''"
                    ></button>
                </template>
                <input type="color" x-model="color" @change="isEraser = false" class="size-5 cursor-pointer rounded border-0 bg-transparent p-0" />
            </div>

            {{-- Brush Size --}}
            <div class="flex items-center gap-1 text-xs text-neutral-600 dark:text-neutral-300 ml-2">
                <span>Ukuran:</span>
                <input type="range" min="1" max="32" x-model="size" class="w-16 accent-indigo-600 cursor-pointer" />
                <span class="text-[10px] font-mono w-4" x-text="size"></span>
            </div>

            {{-- Eraser Toggle --}}
            <button
                type="button"
                @click="isEraser = !isEraser"
                class="flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium transition-colors"
                :class="isEraser ? 'bg-amber-500 text-white shadow-xs' : 'bg-black/5 dark:bg-white/5 hover:bg-black/10 text-neutral-700 dark:text-neutral-200'"
            >
                <flux:icon name="backspace" class="size-3.5" />
                <span>Eraser</span>
            </button>
        </div>

        <div class="flex items-center gap-1.5">
            <button
                type="button"
                @click="clearCanvas()"
                class="flex items-center gap-1 rounded-md border border-black/10 dark:border-white/10 px-2.5 py-1 text-xs font-medium hover:bg-rose-50 dark:hover:bg-rose-500/10 text-rose-600 transition-colors"
            >
                <flux:icon name="trash" class="size-3.5" />
                <span>Bersihkan</span>
            </button>
            <button
                type="button"
                @click="download()"
                class="flex items-center gap-1 rounded-md bg-indigo-600 hover:bg-indigo-700 px-3 py-1 text-xs font-medium text-white shadow-xs transition-colors"
            >
                <flux:icon name="arrow-down-tray" class="size-3.5" />
                <span>Unduh PNG</span>
            </button>
        </div>
    </header>

    {{-- Canvas Drawing Area --}}
    <div x-ref="canvasContainer" class="flex-1 flex items-center justify-center p-3 overflow-hidden">
        <canvas
            x-ref="paintCanvas"
            @mousedown="startDraw($event)"
            @mouseup="stopDraw()"
            @mouseleave="stopDraw()"
            @mousemove="draw($event)"
            class="rounded-xl border border-black/10 dark:border-white/10 shadow-md cursor-crosshair bg-white"
        ></canvas>
    </div>
</div>
BLADE;
    }

    protected function getMarkdownManifestCode(): string
    {
        return <<<'PHP'
<?php

namespace App\MiniOS\Markdown;

use App\MiniOS\Markdown\Livewire\Markdown;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Support\WindowConfig;

class MarkdownApp implements DesktopApp
{
    public function id(): string
    {
        return 'markdown';
    }

    public function name(): string
    {
        return 'Markdown Live';
    }

    public function icon(): string
    {
        return 'code-bracket-square';
    }

    public function entry(): string
    {
        return '/markdown';
    }

    public function routes(): array
    {
        return ['/markdown'];
    }

    public function isPinned(): bool
    {
        return false;
    }

    public function component(): ?string
    {
        return Markdown::class;
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(840, 560)
            ->min(600, 400);
    }

    public function version(): string
    {
        return '1.0.2';
    }
}
PHP;
    }

    protected function getMarkdownLivewireCode(): string
    {
        return <<<'PHP'
<?php

namespace App\MiniOS\Markdown\Livewire;

use Livewire\Component;

class Markdown extends Component
{
    public string $content = "# Selamat Datang di Markdown Live!\n\nIni adalah editor Markdown dua panel interaktif untuk MiniOS.\n\n### Fitur Utama:\n- **Pratinjau Langsung**: Perubahan teks di kiri langsung ter-render di kanan.\n- *Dukungan Format*: Heading, List, Quotes, dan Code blocks.\n\n```php\necho 'Hello from MiniOS!';\n```\n\n> Nikmati kemudahan menulis dokumen di web desktop.";

    public function render()
    {
        return view('apps.markdown');
    }
}
PHP;
    }

    protected function getMarkdownViewCode(): string
    {
        return <<<'BLADE'
<div
    x-data="{
        raw: @entangle('content'),
        copied: false,
        get parsedHtml() {
            let text = this.raw || '';
            // Basic Markdown Parsing
            text = text.replace(/^### (.*$)/gim, '<h3 class=\'text-base font-bold my-2 text-neutral-900 dark:text-white\'>$1</h3>');
            text = text.replace(/^## (.*$)/gim, '<h2 class=\'text-lg font-bold my-2 text-neutral-900 dark:text-white\'>$1</h2>');
            text = text.replace(/^# (.*$)/gim, '<h1 class=\'text-xl font-extrabold my-3 text-neutral-900 dark:text-white border-b pb-1\'>$1</h1>');
            text = text.replace(/^\> (.*$)/gim, '<blockquote class=\'border-l-4 border-indigo-500 pl-3 py-1 my-2 italic text-neutral-600 dark:text-neutral-400 bg-neutral-100 dark:bg-white/5 rounded-r\'>$1</blockquote>');
            text = text.replace(/```([\s\S]*?)```/gim, '<pre class=\'bg-neutral-900 text-neutral-100 p-3 rounded-lg my-2 font-mono text-xs overflow-x-auto\'><code>$1</code></pre>');
            text = text.replace(/`([^`]+)`/gim, '<code class=\'bg-neutral-200 dark:bg-white/10 px-1 py-0.5 rounded font-mono text-xs text-indigo-600 dark:text-indigo-400\'>$1</code>');
            text = text.replace(/\*\*(.*)\*\*/gim, '<strong>$1</strong>');
            text = text.replace(/\*(.*)\*/gim, '<em>$1</em>');
            text = text.replace(/^\- (.*$)/gim, '<li class=\'ml-4 list-disc text-xs\'>$1</li>');
            text = text.replace(/\n$/gim, '<br />');
            return text;
        },
        copyHtml() {
            navigator.clipboard.writeText(this.parsedHtml);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        }
    }"
    class="flex h-full w-full flex-col bg-[#f5f5f7] dark:bg-[#1a1a1c] select-none overflow-hidden"
>
    <header class="flex shrink-0 items-center justify-between border-b border-black/10 dark:border-white/10 bg-white/80 dark:bg-[#202023]/80 px-4 py-2.5 backdrop-blur-md">
        <div class="flex items-center gap-2">
            <flux:icon name="code-bracket-square" class="size-4 text-emerald-500" />
            <span class="text-xs font-semibold text-neutral-900 dark:text-white">Markdown Live</span>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                @click="copyHtml()"
                class="flex items-center gap-1.5 rounded-md bg-emerald-600 hover:bg-emerald-700 px-3 py-1 text-xs font-medium text-white shadow-xs transition-colors"
            >
                <flux:icon name="clipboard-document" class="size-3.5" />
                <span x-text="copied ? 'Tersalin!' : 'Salin HTML'"></span>
            </button>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden">
        {{-- Left: Textarea --}}
        <div class="w-1/2 flex flex-col border-r border-black/10 dark:border-white/10 bg-white dark:bg-[#1e1e20] p-3">
            <span class="text-[10px] font-bold uppercase text-neutral-400 mb-1">Editor Markdown</span>
            <textarea
                x-model="raw"
                class="flex-1 w-full bg-transparent resize-none font-mono text-xs leading-relaxed focus:outline-none text-neutral-800 dark:text-neutral-200"
                placeholder="Tulis markdown di sini..."
            ></textarea>
        </div>

        {{-- Right: Live Preview --}}
        <div class="w-1/2 flex flex-col bg-[#fafafa] dark:bg-[#18181b] p-3 overflow-y-auto">
            <span class="text-[10px] font-bold uppercase text-neutral-400 mb-1">Pratinjau HTML</span>
            <div class="flex-1 text-xs text-neutral-800 dark:text-neutral-200 leading-relaxed" x-html="parsedHtml"></div>
        </div>
    </div>
</div>
BLADE;
    }

    protected function getGame2048ManifestCode(): string
    {
        return <<<'PHP'
<?php

namespace App\MiniOS\Game2048;

use App\MiniOS\Game2048\Livewire\Game2048;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Support\WindowConfig;

class Game2048App implements DesktopApp
{
    public function id(): string
    {
        return 'game-2048';
    }

    public function name(): string
    {
        return '2048 Puzzle';
    }

    public function icon(): string
    {
        return 'puzzle-piece';
    }

    public function entry(): string
    {
        return '/game-2048';
    }

    public function routes(): array
    {
        return ['/game-2048'];
    }

    public function isPinned(): bool
    {
        return false;
    }

    public function component(): ?string
    {
        return Game2048::class;
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(540, 620)
            ->min(440, 500);
    }

    public function version(): string
    {
        return '1.0.0';
    }
}
PHP;
    }

    protected function getGame2048LivewireCode(): string
    {
        return <<<'PHP'
<?php

namespace App\MiniOS\Game2048\Livewire;

use Livewire\Component;

class Game2048 extends Component
{
    public function render()
    {
        return view('apps.game-2048');
    }
}
PHP;
    }

    protected function getGame2048ViewCode(): string
    {
        return <<<'BLADE'
<div
    x-data="{
        grid: [
            [0, 2, 0, 0],
            [0, 0, 4, 0],
            [0, 0, 0, 0],
            [0, 2, 0, 0]
        ],
        score: 8,
        bestScore: 256,
        gameOver: false,
        getTileColor(val) {
            const colors = {
                0: 'bg-neutral-200/50 dark:bg-white/5 text-transparent',
                2: 'bg-[#eee4da] text-[#776e65]',
                4: 'bg-[#ede0c8] text-[#776e65]',
                8: 'bg-[#f2b179] text-white',
                16: 'bg-[#f59563] text-white',
                32: 'bg-[#f67c5f] text-white',
                64: 'bg-[#f65e3b] text-white',
                128: 'bg-[#edcf72] text-white',
                256: 'bg-[#edcc61] text-white',
                512: 'bg-[#edc850] text-white',
                1024: 'bg-[#edc53f] text-white',
                2048: 'bg-[#edc22e] text-white shadow-lg'
            };
            return colors[val] || 'bg-amber-600 text-white';
        },
        restart() {
            this.grid = [
                [0, 2, 0, 0],
                [0, 0, 4, 0],
                [0, 0, 0, 0],
                [0, 2, 0, 0]
            ];
            this.score = 8;
            this.gameOver = false;
        },
        move(dir) {
            // Simplified shift for interactive demo
            this.score += 4;
            if (this.score > this.bestScore) this.bestScore = this.score;
        }
    }"
    @keydown.arrow-up.window="move('up')"
    @keydown.arrow-down.window="move('down')"
    @keydown.arrow-left.window="move('left')"
    @keydown.arrow-right.window="move('right')"
    class="flex h-full w-full flex-col items-center justify-center p-6 bg-[#faf8ef] dark:bg-[#18181b] select-none overflow-hidden"
>
    <div class="w-full max-w-xs space-y-4">
        {{-- Header & Score --}}
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-black text-[#776e65] dark:text-white">2048</h1>
            <div class="flex gap-2">
                <div class="rounded-lg bg-[#bbada0] px-3 py-1 text-center text-white">
                    <div class="text-[9px] uppercase font-bold tracking-wider">Score</div>
                    <div class="text-sm font-bold" x-text="score"></div>
                </div>
                <div class="rounded-lg bg-[#bbada0] px-3 py-1 text-center text-white">
                    <div class="text-[9px] uppercase font-bold tracking-wider">Best</div>
                    <div class="text-sm font-bold" x-text="bestScore"></div>
                </div>
            </div>
        </div>

        {{-- Controls --}}
        <div class="flex items-center justify-between text-xs">
            <span class="text-neutral-500">Gunakan tombol panah 🡑 🡓 🡐 🡒</span>
            <button
                type="button"
                @click="restart()"
                class="rounded-md bg-[#8f7a66] hover:bg-[#9f8a76] px-3 py-1 font-bold text-white shadow-xs transition-colors"
            >
                Mulai Ulang
            </button>
        </div>

        {{-- Grid Board --}}
        <div class="grid grid-cols-4 gap-2.5 rounded-xl bg-[#bbada0] p-3 shadow-md">
            <template x-for="(row, r) in grid" :key="r">
                <template x-for="(val, c) in row" :key="r + '_' + c">
                    <div
                        class="flex aspect-square items-center justify-center rounded-lg text-lg font-bold transition-all"
                        :class="getTileColor(val)"
                        x-text="val > 0 ? val : ''"
                    ></div>
                </template>
            </template>
        </div>
    </div>
</div>
BLADE;
    }

    protected function getBenchmarkManifestCode(): string
    {
        return <<<'PHP'
<?php

namespace App\MiniOS\Benchmark;

use App\MiniOS\Benchmark\Livewire\Benchmark;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Support\WindowConfig;

class BenchmarkApp implements DesktopApp
{
    public function id(): string
    {
        return 'benchmark';
    }

    public function name(): string
    {
        return 'System Benchmark';
    }

    public function icon(): string
    {
        return 'bolt';
    }

    public function entry(): string
    {
        return '/benchmark';
    }

    public function routes(): array
    {
        return ['/benchmark'];
    }

    public function isPinned(): bool
    {
        return false;
    }

    public function component(): ?string
    {
        return Benchmark::class;
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(680, 520)
            ->min(500, 380);
    }

    public function version(): string
    {
        return '1.2.0';
    }
}
PHP;
    }

    protected function getBenchmarkLivewireCode(): string
    {
        return <<<'PHP'
<?php

namespace App\MiniOS\Benchmark\Livewire;

use Livewire\Component;

class Benchmark extends Component
{
    public bool $isRunning = false;
    public ?array $results = null;

    public function runBenchmark(): void
    {
        $this->isRunning = true;

        // 1. CPU Floating-point test
        $startCpu = microtime(true);
        $val = 0.0;
        for ($i = 0; $i < 100000; $i++) {
            $val += sqrt($i) * sin($i);
        }
        $cpuTime = round((microtime(true) - $startCpu) * 1000, 2);

        // 2. Memory Throughput test
        $startMem = microtime(true);
        $arr = [];
        for ($i = 0; $i < 50000; $i++) {
            $arr[] = "bench_key_{$i}";
        }
        $memTime = round((microtime(true) - $startMem) * 1000, 2);
        unset($arr);

        // 3. String Concatenation test
        $startStr = microtime(true);
        $str = '';
        for ($i = 0; $i < 20000; $i++) {
            $str .= 'x';
        }
        $strTime = round((microtime(true) - $startStr) * 1000, 2);
        unset($str);

        $totalTime = $cpuTime + $memTime + $strTime;
        $score = max(100, (int) round(10000 / ($totalTime ?: 1)));

        $this->results = [
            'cpu_ms' => $cpuTime,
            'memory_ms' => $memTime,
            'string_ms' => $strTime,
            'total_ms' => $totalTime,
            'score' => $score,
            'php_version' => PHP_VERSION,
            'os' => PHP_OS,
        ];

        $this->isRunning = false;
    }

    public function render()
    {
        return view('apps.benchmark');
    }
}
PHP;
    }

    protected function getBenchmarkViewCode(): string
    {
        return <<<'BLADE'
<div class="flex h-full w-full flex-col p-6 bg-[#f8f9fa] dark:bg-[#1a1a1c] text-neutral-800 dark:text-neutral-100 select-none overflow-y-auto">
    <div class="max-w-lg mx-auto w-full space-y-5">
        <div class="flex items-center gap-3 border-b border-black/10 dark:border-white/10 pb-4">
            <div class="flex size-11 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-600 dark:text-amber-400">
                <flux:icon name="bolt" class="size-6" />
            </div>
            <div>
                <h1 class="text-lg font-bold text-neutral-900 dark:text-white">System Benchmark</h1>
                <p class="text-xs text-neutral-500">Uji performa kalkulasi CPU, alokasi memori, dan responsivitas PHP.</p>
            </div>
        </div>

        {{-- Action Button --}}
        <div class="flex justify-center py-2">
            <button
                type="button"
                wire:click="runBenchmark"
                class="flex items-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md transition-all active:scale-98"
            >
                <flux:icon name="play" class="size-4" />
                <span>Mulai Tes Benchmark</span>
            </button>
        </div>

        {{-- Results Card --}}
        @if ($results)
            <div class="rounded-2xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#232326] p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-500">Skor Performa MiniOS</span>
                    <span class="rounded-full bg-emerald-500/15 px-2.5 py-0.5 text-xs font-bold text-emerald-600 dark:text-emerald-400">OPTIMAL</span>
                </div>
                <div class="text-4xl font-black text-amber-500 tracking-tight">
                    {{ number_format($results['score']) }} <span class="text-xs font-normal text-neutral-400">pts</span>
                </div>

                <div class="grid grid-cols-3 gap-2 pt-2 border-t border-black/5 dark:border-white/5 text-xs">
                    <div class="rounded-lg bg-neutral-100 dark:bg-white/5 p-2 text-center">
                        <div class="text-[10px] text-neutral-400 font-medium">CPU Math</div>
                        <div class="font-bold text-neutral-800 dark:text-white mt-0.5">{{ $results['cpu_ms'] }} ms</div>
                    </div>
                    <div class="rounded-lg bg-neutral-100 dark:bg-white/5 p-2 text-center">
                        <div class="text-[10px] text-neutral-400 font-medium">Memory Allocation</div>
                        <div class="font-bold text-neutral-800 dark:text-white mt-0.5">{{ $results['memory_ms'] }} ms</div>
                    </div>
                    <div class="rounded-lg bg-neutral-100 dark:bg-white/5 p-2 text-center">
                        <div class="text-[10px] text-neutral-400 font-medium">String Concat</div>
                        <div class="font-bold text-neutral-800 dark:text-white mt-0.5">{{ $results['string_ms'] }} ms</div>
                    </div>
                </div>

                <div class="text-[11px] text-neutral-400 text-center pt-1">
                    PHP {{ $results['php_version'] }} • {{ $results['os'] }}
                </div>
            </div>
        @endif
    </div>
</div>
BLADE;
    }
}
