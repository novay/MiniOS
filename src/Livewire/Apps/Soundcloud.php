<?php

namespace Novay\MiniOS\Livewire\Apps;

use Livewire\Component;
use Novay\MiniOS\Apps\SoundcloudApp;
use Novay\MiniOS\Concerns\HasNotifications;
use Novay\MiniOS\Concerns\HasTranslations;

class Soundcloud extends Component
{
    use HasNotifications;
    use HasTranslations;

    public string $embedCode = '';

    public string $playerUrl = '';

    public bool $showConfigModal = false;

    public bool $showAboutModal = false;

    public string $version = '1.0.0';

    public const SAMPLE_EMBED = '<iframe width="100%" height="450" scrolling="no" frameborder="no" allow="autoplay; encrypted-media" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/playlists/soundcloud%3Aplaylists%3A2299409466%3Fsecret_token%3Ds-1pQECtHZO72&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true"></iframe><div style="font-size: 10px; color: #cccccc;line-break: anywhere;word-break: normal;overflow: hidden;white-space: nowrap;text-overflow: ellipsis; font-family: Interstate,Lucida Grande,Lucida Sans Unicode,Lucida Sans,Garuda,Verdana,Tahoma,sans-serif;font-weight: 100;"><a href="https://soundcloud.com/nxvay" title="Nxvay" target="_blank" style="color: #cccccc; text-decoration: none;">Nxvay</a> · <a href="https://soundcloud.com/nxvay/sets/dystopia-raya-ai-remix/s-1pQECtHZO72" title="Dystopia Raya (AI Remix)" target="_blank" style="color: #cccccc; text-decoration: none;">Dystopia Raya (AI Remix)</a></div>';

    public const PRESETS = [
        'dystopia' => [
            'name' => 'Dystopia Raya (AI Cover)',
            'url' => 'https://soundcloud.com/nxvay/sets/dystopia-raya-ai-remix/s-1pQECtHZO72',
            'embed' => self::SAMPLE_EMBED,
        ],
    ];

    public array $tracks = [];

    public function mount(): void
    {
        $this->version = app(SoundcloudApp::class)->version();

        $this->embedCode = (string) os_setting('soundcloud.embed_code', '');
        $this->playerUrl = (string) os_setting('soundcloud.player_url', '');

        // If playerUrl is empty but embedCode is present, extract it
        if (empty($this->playerUrl) && ! empty($this->embedCode)) {
            $this->playerUrl = $this->extractPlayerUrl($this->embedCode);
        }

        // Jika belum ada playlist yang disimpan, langsung gunakan contoh bawaan
        if (empty($this->playerUrl)) {
            $this->embedCode = self::SAMPLE_EMBED;
            $this->playerUrl = $this->extractPlayerUrl(self::SAMPLE_EMBED);
        }

        // Ambil tracks yang tersimpan atau resolve otomatis dari playlist
        $savedTracks = os_setting('soundcloud.tracks');
        $needsResolve = empty($savedTracks) || ! is_array($savedTracks);

        // Jika tracks lama yang tersimpan belum lengkap (misal hanya 5 lagu), resolve ulang
        if (! $needsResolve && count($savedTracks) < 10) {
            $needsResolve = true;
        }

        if (! $needsResolve) {
            $this->tracks = $savedTracks;
        } else {
            $this->tracks = $this->resolvePlaylistTracks($this->embedCode ?: $this->playerUrl);
            if (! empty($this->tracks)) {
                os_setting()->set('soundcloud.tracks', $this->tracks);
            }
        }
    }

    public function save(): void
    {
        $raw = trim($this->embedCode);

        if (empty($raw)) {
            $this->error($this->t('notif_enter_code'), 'SoundCloud');

            return;
        }

        $url = $this->extractPlayerUrl($raw);

        if (empty($url)) {
            $this->error($this->t('notif_invalid_format'), 'SoundCloud');

            return;
        }

        // Resolve tracks otomatis sesuai playlist yang dimasukkan
        $resolvedTracks = $this->resolvePlaylistTracks($raw);
        if (! empty($resolvedTracks)) {
            $this->tracks = $resolvedTracks;
            os_setting()->set('soundcloud.tracks', $this->tracks);
        }

        os_setting()->set('soundcloud.embed_code', $raw);
        os_setting()->set('soundcloud.player_url', $url);

        $this->playerUrl = $url;
        $this->showConfigModal = false;

        $this->dispatch('soundcloud-load-url', url: $url, tracks: $this->tracks);
        $this->success($this->t('notif_saved_success'), 'SoundCloud');
    }

    public function loadPreset(string $key): void
    {
        if (! isset(self::PRESETS[$key])) {
            return;
        }

        $preset = self::PRESETS[$key];
        $this->embedCode = $preset['embed'] ?? $preset['url'];
        $url = $this->extractPlayerUrl($this->embedCode);

        // Resolve tracks otomatis
        $resolvedTracks = $this->resolvePlaylistTracks($this->embedCode);
        if (! empty($resolvedTracks)) {
            $this->tracks = $resolvedTracks;
            os_setting()->set('soundcloud.tracks', $this->tracks);
        }

        os_setting()->set('soundcloud.embed_code', $this->embedCode);
        os_setting()->set('soundcloud.player_url', $url);

        $this->playerUrl = $url;
        $this->showConfigModal = false;

        $this->dispatch('soundcloud-load-url', url: $url, tracks: $this->tracks);
        $this->success($this->t('notif_preset_loaded', ['name' => $preset['name']]), 'SoundCloud');
    }

    public function useSample(): void
    {
        $this->loadPreset('dystopia');
    }

    public function openConfig(): void
    {
        $this->showConfigModal = true;
    }

    public function closeConfig(): void
    {
        $this->showConfigModal = false;
    }

    public function openAbout(): void
    {
        $this->showAboutModal = true;
    }

    public function closeAbout(): void
    {
        $this->showAboutModal = false;
    }

    public function resetPlaylist(): void
    {
        os_setting()->set('soundcloud.embed_code', null);
        os_setting()->set('soundcloud.player_url', null);
        os_setting()->set('soundcloud.tracks', null);

        $this->embedCode = self::SAMPLE_EMBED;
        $this->playerUrl = $this->extractPlayerUrl(self::SAMPLE_EMBED);
        $this->tracks = $this->resolvePlaylistTracks(self::SAMPLE_EMBED);
        if (! empty($this->tracks)) {
            os_setting()->set('soundcloud.tracks', $this->tracks);
        }

        $this->showConfigModal = false;

        $this->dispatch('soundcloud-load-url', url: $this->playerUrl, tracks: $this->tracks);
        $this->info($this->t('notif_reset_success'), 'SoundCloud');
    }

    public function extractPlayerUrl(string $code): string
    {
        $code = trim($code);

        // 1. Direct match for iframe src="..."
        if (preg_match('/src=["\']([^"\']+)["\']/', $code, $matches)) {
            return html_entity_decode($matches[1]);
        }

        // 2. Direct player URL: https://w.soundcloud.com/player/?url=...
        if (str_starts_with($code, 'https://w.soundcloud.com/player/')) {
            return $code;
        }

        // 3. Normal SoundCloud URL (tracks or sets): https://soundcloud.com/...
        if (filter_var($code, FILTER_VALIDATE_URL) && str_contains($code, 'soundcloud.com')) {
            return 'https://w.soundcloud.com/player/?url='.urlencode($code).'&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true';
        }

        return '';
    }

    public function extractWebUrl(string $code): ?string
    {
        $code = trim($code);

        // 1. Prioritize playlist (/sets/) web URL
        if (preg_match('#https?://soundcloud\.com/[a-zA-Z0-9_\-]+/sets/[^\s"\'<>]+#i', $code, $matches)) {
            return $matches[0];
        }

        // 2. Match any specific soundcloud track or set web URL
        if (preg_match_all('#https?://soundcloud\.com/(?!player)[^\s"\'<>]+#i', $code, $matches)) {
            foreach ($matches[0] as $match) {
                // If it has a path deeper than just user profile
                if (substr_count(parse_url($match, PHP_URL_PATH) ?? '', '/') >= 2) {
                    return $match;
                }
            }
            if (! empty($matches[0])) {
                return $matches[0][0];
            }
        }

        // 3. Extract from embed code url parameter if present
        if (str_contains($code, 'url=')) {
            $parsed = parse_url($code);
            if (isset($parsed['query'])) {
                parse_str($parsed['query'], $query);
                if (! empty($query['url']) && str_starts_with($query['url'], 'http')) {
                    return $query['url'];
                }
            }
        }

        return null;
    }

    public function resolvePlaylistTracks(string $code): array
    {
        $webUrl = $this->extractWebUrl($code);
        if (! $webUrl) {
            return [];
        }

        try {
            $context = stream_context_create([
                'http' => [
                    'header' => "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36\r\n",
                    'timeout' => 5,
                ],
            ]);
            $html = @file_get_contents($webUrl, false, $context);
            if (! $html) {
                return [];
            }

            if (preg_match_all('/window\.__sc_hydration\s*=\s*(\[.*?\]);<\/script>/s', $html, $matches)) {
                foreach ($matches[1] as $jsonStr) {
                    $data = json_decode($jsonStr, true);
                    if (! is_array($data)) {
                        continue;
                    }
                    foreach ($data as $item) {
                        if (($item['hydratable'] ?? '') === 'playlist' && ! empty($item['data']['tracks'])) {
                            $rawTracks = $item['data']['tracks'];
                            $playlistId = $item['data']['id'] ?? null;
                            $playlistSecretToken = $item['data']['secret_token'] ?? null;

                            // Temukan tracks yang belum memiliki detail/judul (hanya stub ID)
                            $missingTrackIds = [];
                            foreach ($rawTracks as $t) {
                                if (empty($t['title']) && ! empty($t['id'])) {
                                    $missingTrackIds[] = $t['id'];
                                }
                            }

                            $fetchedTracks = [];
                            if (! empty($missingTrackIds)) {
                                $fetchedTracks = $this->fetchBatchTracks($missingTrackIds, $playlistId, $playlistSecretToken);
                            }

                            $tracks = [];
                            foreach ($rawTracks as $idx => $track) {
                                $trackData = $track;
                                if (empty($trackData['title']) && ! empty($trackData['id']) && isset($fetchedTracks[$trackData['id']])) {
                                    $trackData = array_merge($trackData, $fetchedTracks[$trackData['id']]);
                                }

                                if (empty($trackData['title'])) {
                                    continue;
                                }

                                $dur = (int) ($trackData['duration'] ?? 180000);
                                $durSec = (int) floor($dur / 1000);
                                $mins = floor($durSec / 60);
                                $secs = $durSec % 60;

                                $tracks[] = [
                                    'id' => $trackData['id'] ?? ($idx + 1),
                                    'title' => $trackData['title'],
                                    'artist' => $trackData['user']['username'] ?? 'SoundCloud Artist',
                                    'uploader' => $trackData['user']['username'] ?? 'SoundCloud Artist',
                                    'plays' => isset($trackData['playback_count']) ? (string) $trackData['playback_count'] : '',
                                    'durationSec' => $durSec,
                                    'durationFormatted' => sprintf('%d:%02d', $mins, $secs),
                                    'artwork' => ! empty($trackData['artwork_url']) ? str_replace('-large', '-t500x500', $trackData['artwork_url']) : null,
                                    'scUrl' => $trackData['permalink_url'] ?? 'https://soundcloud.com',
                                ];
                            }

                            if (! empty($tracks)) {
                                return $tracks;
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently fall back to client-side widget resolver
        }

        return [];
    }

    /**
     * @param  array<int, int|string>  $trackIds
     * @return array<int|string, array<string, mixed>>
     */
    protected function fetchBatchTracks(array $trackIds, int|string|null $playlistId = null, ?string $secretToken = null): array
    {
        if (empty($trackIds)) {
            return [];
        }

        $clientId = 'IysGhRTI7AdLDwRrKw5hy3UqwhvyW3Vw';
        $idsParam = implode(',', $trackIds);
        $url = "https://api-widget.soundcloud.com/tracks?ids={$idsParam}&client_id={$clientId}";
        if ($playlistId) {
            $url .= "&playlistId={$playlistId}";
        }
        if ($secretToken) {
            $url .= "&playlistSecretToken={$secretToken}";
        }

        try {
            $context = stream_context_create([
                'http' => [
                    'header' => "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36\r\n",
                    'timeout' => 5,
                ],
            ]);
            $res = @file_get_contents($url, false, $context);
            if (! $res) {
                return [];
            }
            $data = json_decode($res, true);
            if (! is_array($data)) {
                return [];
            }

            $indexed = [];
            foreach ($data as $t) {
                if (! empty($t['id'])) {
                    $indexed[$t['id']] = $t;
                }
            }

            return $indexed;
        } catch (\Throwable) {
            return [];
        }
    }

    public function render()
    {
        return view('minios::apps.soundcloud.index');
    }
}
