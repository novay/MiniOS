<?php

namespace Novay\MiniOS\Livewire\Apps;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Novay\MiniOS\Concerns\HasNotifications;
use Novay\MiniOS\Concerns\HasTranslations;

class Settings extends Component
{
    use HasNotifications;
    use HasTranslations;

    #[Url(as: 'tab')]
    public string $activeTab = 'appearance';

    /**
     * Sidebar search query.
     */
    public string $search = '';

    /**
     * Profile form state.
     */
    public string $profile_name = '';

    public string $profile_email = '';

    public ?string $profileStatus = null;

    /**
     * Password form state.
     */
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $passwordStatus = null;

    /**
     * Settings state arrays per category.
     *
     * @var array<string, mixed>
     */
    public array $appearance = [];

    /**
     * @var array<string, mixed>
     */
    public array $dock = [];

    /**
     * @var array<string, mixed>
     */
    public array $window_manager = [];

    /**
     * @var array<string, mixed>
     */
    public array $locale_time = [];

    /**
     * @var array<string, mixed>
     */
    public array $notifications = [];

    /**
     * @var array<string, mixed>
     */
    public array $services = [];

    /**
     * Test connection status indicators.
     */
    public ?string $storageTestStatus = null;

    public ?string $storageTestError = null;

    public ?string $mailTestStatus = null;

    public ?string $mailTestError = null;

    /**
     * Save status indicator message.
     */
    public ?string $saveStatus = null;

    public function mount(): void
    {
        $validTabs = ['appearance', 'dock', 'window_manager', 'notifications', 'locale_time', 'account', 'filesystem', 'mail'];
        if (! in_array($this->activeTab, $validTabs, true)) {
            $this->activeTab = 'appearance';
        }

        $this->loadSettings();

        $user = auth()->user();
        if ($user) {
            $this->profile_name = $user->name;
            $this->profile_email = $user->email;
        }
    }

    /**
     * Load settings from storage/cache.
     */
    public function loadSettings(): void
    {
        $this->appearance = os_setting()->getCategory('appearance');
        $this->dock = os_setting()->getCategory('dock');
        $this->dock['enable_drag'] = (bool) ($this->dock['enable_drag'] ?? true);
        $this->window_manager = os_setting()->getCategory('window_manager');
        $this->locale_time = os_setting()->getCategory('locale_time');
        $this->notifications = os_setting()->getCategory('notifications');
        $this->services = os_setting()->getCategory('services');
    }

    /**
     * Select active tab category.
     */
    #[On('open-settings-tab')]
    #[On('set-settings-tab')]
    public function setTab(string|array $tab): void
    {
        $selected = is_array($tab) ? ($tab['tab'] ?? 'appearance') : $tab;
        $validTabs = ['appearance', 'dock', 'window_manager', 'notifications', 'locale_time', 'account', 'filesystem', 'mail'];
        if (in_array($selected, $validTabs, true)) {
            $this->activeTab = $selected;
        }
    }

    /**
     * Livewire updated hook for auto-persisting settings.
     */
    public function updated(string $property, mixed $value): void
    {
        $parts = explode('.', $property, 2);

        if (count($parts) === 2) {
            [$category, $key] = $parts;

            if (in_array($category, ['appearance', 'dock', 'window_manager', 'locale_time', 'notifications', 'services'])) {
                os_setting()->set("{$category}.{$key}", $value);

                $this->saveStatus = $this->trans('saved_auto');

                $this->dispatch('os-setting-updated', [
                    'category' => $category,
                    'key' => $key,
                    'value' => $value,
                ]);
            }
        }
    }

    /**
     * Dispatch sample notification for user testing.
     */
    public function testNotification(): void
    {
        $this->success(
            $this->trans('notif_test_desc'),
            $this->trans('notif_test_title')
        );
    }

    /**
     * Reset a category to system defaults.
     */
    public function resetCategory(string $category): void
    {
        if (in_array($category, ['appearance', 'dock', 'window_manager', 'locale_time', 'notifications', 'services'])) {
            os_setting()->resetCategory($category);
            $this->loadSettings();

            $this->saveStatus = $this->trans('reset_success');

            $this->dispatch('os-setting-reset', [
                'category' => $category,
            ]);
        } elseif ($category === 'filesystem') {
            $defaults = config('minios.settings.services', []);
            $storageKeys = [
                'storage_driver',
                's3_key', 's3_secret', 's3_region', 's3_bucket', 's3_endpoint', 's3_use_path_style',
                'bunny_storage_zone', 'bunny_api_key', 'bunny_region', 'bunny_pull_zone', 'bunny_token_auth_key',
            ];
            foreach ($storageKeys as $k) {
                if (array_key_exists($k, $defaults)) {
                    os_setting()->set("services.{$k}", $defaults[$k]);
                }
            }
            $this->loadSettings();
            $this->saveStatus = $this->trans('reset_filesystem_success');
            $this->dispatch('os-setting-reset', ['category' => 'services']);
        } elseif ($category === 'mail') {
            $defaults = config('minios.settings.services', []);
            $mailKeys = [
                'mail_driver',
                'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_password',
                'resend_api_key',
                'mail_from_address', 'mail_from_name',
            ];
            foreach ($mailKeys as $k) {
                if (array_key_exists($k, $defaults)) {
                    os_setting()->set("services.{$k}", $defaults[$k]);
                }
            }
            $this->loadSettings();
            $this->saveStatus = $this->trans('reset_mail_success');
            $this->dispatch('os-setting-reset', ['category' => 'services']);
        }
    }

    /**
     * Test connection to configured storage disk.
     */
    public function testStorageConnection(): void
    {
        $this->storageTestStatus = null;
        $this->storageTestError = null;

        try {
            $driver = $this->services['storage_driver'] ?? 'local';

            if ($driver === 's3') {
                config([
                    'filesystems.disks.s3.key' => $this->services['s3_key'] ?? '',
                    'filesystems.disks.s3.secret' => $this->services['s3_secret'] ?? '',
                    'filesystems.disks.s3.region' => $this->services['s3_region'] ?? 'us-east-1',
                    'filesystems.disks.s3.bucket' => $this->services['s3_bucket'] ?? '',
                    'filesystems.disks.s3.endpoint' => $this->services['s3_endpoint'] ?? null,
                    'filesystems.disks.s3.use_path_style_endpoint' => (bool) ($this->services['s3_use_path_style'] ?? false),
                ]);
            } elseif ($driver === 'bunny') {
                config([
                    'filesystems.disks.bunny' => [
                        'driver' => 'bunny',
                        'storage_zone' => $this->services['bunny_storage_zone'] ?? '',
                        'api_key' => $this->services['bunny_api_key'] ?? '',
                        'region' => $this->services['bunny_region'] ?? 'de',
                        'pull_zone' => $this->services['bunny_pull_zone'] ?? '',
                        'token_auth_key' => $this->services['bunny_token_auth_key'] ?? '',
                    ],
                ]);
            }

            $disk = Storage::disk($driver);

            $testFileName = 'minios_probe_'.time().'.txt';
            $testContent = 'MiniOS Storage Probe at '.now()->toIso8601String();

            $disk->put($testFileName, $testContent);
            $retrieved = $disk->get($testFileName);

            if ($retrieved !== $testContent) {
                throw new \Exception($this->trans('storage_content_mismatch'));
            }

            $disk->delete($testFileName);

            $this->storageTestStatus = $this->trans('storage_test_success', ['driver' => $driver]);
        } catch (\Throwable $e) {
            $this->storageTestError = $this->trans('storage_test_fail', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Test delivery via configured mail driver.
     */
    public function testMailDelivery(): void
    {
        $this->mailTestStatus = null;
        $this->mailTestError = null;

        $user = auth()->user();
        $targetEmail = $user?->email ?? $this->services['mail_from_address'] ?? 'admin@minios.local';

        try {
            $driver = $this->services['mail_driver'] ?? 'log';

            if ($driver === 'log') {
                Log::info("[MiniOS Test Email] Uji coba pengiriman surel ke: {$targetEmail}");
                $this->mailTestStatus = $this->trans('mail_log_success');

                return;
            }

            if ($driver === 'resend') {
                $apiKey = $this->services['resend_api_key'] ?? '';
                if (empty($apiKey)) {
                    throw new \Exception($this->trans('mail_resend_key_empty'));
                }
                config([
                    'resend.api_key' => $apiKey,
                    'mail.default' => 'resend',
                    'mail.mailers.resend' => [
                        'transport' => 'resend',
                    ],
                ]);
            }

            $subject = $this->trans('mail_subject', ['time' => now()->format('H:i:s')]);
            $body = $this->trans('mail_body');

            Mail::raw($body, function ($message) use ($targetEmail, $subject) {
                $message->to($targetEmail)->subject($subject);
            });

            $this->mailTestStatus = $this->trans('mail_test_success', ['email' => $targetEmail, 'driver' => $driver]);
        } catch (\Throwable $e) {
            $this->mailTestError = $this->trans('mail_test_fail', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update user profile information.
     */
    public function updateProfile(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $validated = $this->validate([
            'profile_name' => ['required', 'string', 'max:255'],
            'profile_email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'profile_name.required' => $this->trans('profile_name_required'),
            'profile_email.required' => $this->trans('profile_email_required'),
            'profile_email.email' => $this->trans('profile_email_invalid'),
            'profile_email.unique' => $this->trans('profile_email_unique'),
        ]);

        $user->name = $validated['profile_name'];
        $user->email = $validated['profile_email'];
        $user->save();

        $this->profileStatus = $this->trans('profile_update_success');
    }

    /**
     * Update user password.
     */
    public function updatePassword(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $validated = $this->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => $this->trans('password_current_required'),
            'current_password.current_password' => $this->trans('password_current_wrong'),
            'password.required' => $this->trans('password_new_required'),
            'password.min' => $this->trans('password_new_min'),
            'password.confirmed' => $this->trans('password_confirmed_wrong'),
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->passwordStatus = $this->trans('password_update_success');
    }

    /**
     * Get dynamic accent color classes for active elements.
     *
     * @return array<string, string>
     */
    public function getAccentProperty(): array
    {
        $color = $this->appearance['accent_color'] ?? 'indigo';

        return match ($color) {
            'zinc' => [
                'name' => 'zinc',
                'hex' => '#27272a',
                'bg' => 'bg-zinc-700',
                'badge' => 'bg-zinc-800 text-white shadow-zinc-500/30',
                'active_tab' => 'bg-zinc-700 text-white shadow-sm font-medium',
                'radio_card' => 'border-zinc-500 bg-zinc-500/10 ring-1 ring-zinc-500',
                'ring' => 'ring-zinc-500',
                'text' => 'text-zinc-400',
            ],
            'emerald' => [
                'name' => 'emerald',
                'hex' => '#10b981',
                'bg' => 'bg-emerald-600',
                'badge' => 'bg-emerald-600 text-white shadow-emerald-500/30',
                'active_tab' => 'bg-emerald-600 text-white shadow-sm font-medium',
                'radio_card' => 'border-emerald-500 bg-emerald-500/10 ring-1 ring-emerald-500',
                'ring' => 'ring-emerald-500',
                'text' => 'text-emerald-400',
            ],
            'sky' => [
                'name' => 'sky',
                'hex' => '#0ea5e9',
                'bg' => 'bg-sky-500',
                'badge' => 'bg-sky-500 text-white shadow-sky-500/30',
                'active_tab' => 'bg-sky-600 text-white shadow-sm font-medium',
                'radio_card' => 'border-sky-500 bg-sky-500/10 ring-1 ring-sky-500',
                'ring' => 'ring-sky-500',
                'text' => 'text-sky-400',
            ],
            'amber' => [
                'name' => 'amber',
                'hex' => '#f59e0b',
                'bg' => 'bg-amber-500',
                'badge' => 'bg-amber-500 text-white shadow-amber-500/30',
                'active_tab' => 'bg-amber-600 text-white shadow-sm font-medium',
                'radio_card' => 'border-amber-500 bg-amber-500/10 ring-1 ring-amber-500',
                'ring' => 'ring-amber-500',
                'text' => 'text-amber-400',
            ],
            'rose' => [
                'name' => 'rose',
                'hex' => '#f43f5e',
                'bg' => 'bg-rose-600',
                'badge' => 'bg-rose-600 text-white shadow-rose-500/30',
                'active_tab' => 'bg-rose-600 text-white shadow-sm font-medium',
                'radio_card' => 'border-rose-500 bg-rose-500/10 ring-1 ring-rose-500',
                'ring' => 'ring-rose-500',
                'text' => 'text-rose-400',
            ],
            'violet' => [
                'name' => 'violet',
                'hex' => '#8b5cf6',
                'bg' => 'bg-violet-600',
                'badge' => 'bg-violet-600 text-white shadow-violet-500/30',
                'active_tab' => 'bg-violet-600 text-white shadow-sm font-medium',
                'radio_card' => 'border-violet-500 bg-violet-500/10 ring-1 ring-violet-500',
                'ring' => 'ring-violet-500',
                'text' => 'text-violet-400',
            ],
            default => [
                'name' => 'indigo',
                'hex' => '#6366f1',
                'bg' => 'bg-indigo-600',
                'badge' => 'bg-indigo-600 text-white shadow-indigo-500/30',
                'active_tab' => 'bg-indigo-600 text-white shadow-sm font-medium',
                'radio_card' => 'border-indigo-500 bg-indigo-500/10 ring-1 ring-indigo-500',
                'ring' => 'ring-indigo-500',
                'text' => 'text-indigo-400',
            ],
        };
    }

    /**
     * Get all navigation items filtered by search query.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getNavItemsProperty(): array
    {
        $items = [
            'appearance' => [
                'section_label' => $this->trans('section_system'),
                'label' => $this->trans('nav_appearance'),
                'desc' => $this->trans('nav_appearance_desc'),
                'icon' => 'paint-brush',
                'color' => 'bg-pink-500 text-white',
                'keywords' => ['tampilan', 'personalisasi', 'tema', 'dark', 'light', 'terang', 'gelap', 'aksen', 'font', 'tipografi', 'ubuntu', 'segoe', 'inter', 'google', 'san francisco', 'system-ui', 'wallpaper', 'blur', 'transparansi', 'acrylic', 'mica', 'appearance', 'theme', 'accent', 'personalization'],
            ],
            'dock' => [
                'label' => $this->trans('nav_dock'),
                'desc' => $this->trans('nav_dock_desc'),
                'icon' => 'rectangle-stack',
                'color' => 'bg-amber-500 text-white',
                'keywords' => ['dock', 'taskbar', 'ukuran', 'posisi', 'layar', 'autohide', 'sembunyikan', 'indikator', 'aplikasi', 'size', 'position', 'screen', 'indicators', 'drag', 'geser', 'reorder'],
            ],
            'window_manager' => [
                'label' => $this->trans('nav_window_manager'),
                'desc' => $this->trans('nav_window_manager_desc'),
                'icon' => 'squares-2x2',
                'color' => 'bg-sky-500 text-white',
                'keywords' => ['sistem', 'window', 'jendela', 'manager', 'restore', 'sesi', 'pemulihan', 'posisi', 'ukuran', 'system', 'session', 'coordinates'],
            ],
            'notifications' => [
                'label' => $this->trans('nav_notifications'),
                'desc' => $this->trans('nav_notifications_desc'),
                'icon' => 'bell',
                'color' => 'bg-purple-500 text-white',
                'keywords' => ['notifikasi', 'toast', 'suara', 'sound', 'chime', 'posisi', 'position', 'alert', 'notifications', 'action center', 'flux', 'provider', 'bawaan'],
            ],
            'locale_time' => [
                'label' => $this->trans('nav_locale_time'),
                'desc' => $this->trans('nav_locale_time_desc'),
                'icon' => 'globe-alt',
                'color' => 'bg-emerald-500 text-white',
                'keywords' => ['waktu', 'bahasa', 'jam', 'zona', 'locale', 'timezone', 'jakarta', 'makassar', 'jayapura', 'wib', 'wita', 'wit', 'time', 'language', 'clock', 'date'],
            ],
            'account' => [
                'label' => $this->trans('nav_account'),
                'desc' => $this->trans('nav_account_desc'),
                'icon' => 'user',
                'color' => 'bg-indigo-500 text-white',
                'keywords' => ['akun', 'profil', 'nama', 'email', 'kata sandi', 'password', 'keamanan', 'security', '2fa', 'passkey', 'account', 'profile', 'user'],
            ],
            'filesystem' => [
                'section_label' => $this->trans('section_services'),
                'label' => $this->trans('nav_filesystem'),
                'desc' => $this->trans('nav_filesystem_desc'),
                'icon' => 'server',
                'color' => 'bg-teal-500 text-white',
                'keywords' => ['filesystem', 'penyimpanan', 'storage', 'disk', 's3', 'minio', 'r2', 'spaces', 'wasabi', 'upload', 'berkas', 'cloud', 'files', 'bunny', 'bunnycdn'],
            ],
            'mail' => [
                'label' => $this->trans('nav_mail'),
                'desc' => $this->trans('nav_mail_desc'),
                'icon' => 'envelope',
                'color' => 'bg-sky-500 text-white',
                'keywords' => ['mail', 'delivery', 'email', 'surel', 'smtp', 'log', 'sendmail', 'notifikasi', 'pesan', 'messages', 'resend'],
            ],
        ];

        $query = trim(mb_strtolower($this->search));
        if ($query === '') {
            return $items;
        }

        return array_filter($items, function ($item) use ($query) {
            if (str_contains(mb_strtolower($item['label']), $query)) {
                return true;
            }

            if (str_contains(mb_strtolower($item['desc']), $query)) {
                return true;
            }

            foreach ($item['keywords'] as $keyword) {
                if (str_contains(mb_strtolower($keyword), $query)) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * When search is updated, automatically select the first matching category if current tab is not in results.
     */
    public function updatedSearch(string $value): void
    {
        $filtered = $this->navItems;
        if (! empty($filtered) && ! array_key_exists($this->activeTab, $filtered)) {
            $this->activeTab = array_key_first($filtered);
        }
    }

    public function render()
    {
        return view('minios::apps.settings', [
            'accent' => $this->accent,
            'navItems' => $this->navItems,
        ]);
    }
}
