<div
    x-data="{
        tabs: [
            {
                id: 1,
                title: 'Start Page',
                inputUrl: '',
                currentUrl: '',
                loading: false,
                isStartPage: true
            }
        ],
        activeTabId: 1,
        nextTabId: 2,

        get currentTab() {
            return this.tabs.find(t => t.id === this.activeTabId) || this.tabs[0];
        },

        formatUrl(val) {
            let target = (val || '').trim();
            if (!target) return 'https://www.google.com/webhp?igu=1';

            if (/^https?:\/\//i.test(target) || (target.includes('.') && !target.includes(' '))) {
                if (!/^https?:\/\//i.test(target)) {
                    target = 'https://' + target;
                }
                if (/google\.(com|co\.id|org)/i.test(target) && !target.includes('igu=1')) {
                    const separator = target.includes('?') ? '&' : '?';
                    target += separator + 'igu=1';
                }
                return target;
            }

            return 'https://www.google.com/search?q=' + encodeURIComponent(target) + '&igu=1';
        },

        navigate() {
            const tab = this.currentTab;
            if (!tab) return;
            tab.isStartPage = false;
            tab.currentUrl = this.formatUrl(tab.inputUrl);
            tab.loading = true;
            tab.title = this.extractDomain(tab.currentUrl);
        },

        extractDomain(url) {
            try {
                const parsed = new URL(url);
                return parsed.hostname.replace(/^www\./, '');
            } catch(e) {
                return 'Web Browser';
            }
        },

        openBookmark(url, name) {
            const tab = this.currentTab;
            if (!tab) return;
            tab.inputUrl = url;
            tab.currentUrl = this.formatUrl(url);
            tab.title = name;
            tab.isStartPage = false;
            tab.loading = true;
        },

        addTab(url = '', title = 'Start Page') {
            const newId = this.nextTabId++;
            const isStart = !url;
            const targetUrl = url ? this.formatUrl(url) : '';
            this.tabs.push({
                id: newId,
                title: title,
                inputUrl: url || '',
                currentUrl: targetUrl,
                loading: false,
                isStartPage: isStart
            });
            this.activeTabId = newId;
        },

        closeTab(id) {
            if (this.tabs.length === 1) return;
            const index = this.tabs.findIndex(t => t.id === id);
            this.tabs = this.tabs.filter(t => t.id !== id);
            if (this.activeTabId === id) {
                const nextTab = this.tabs[Math.max(0, index - 1)];
                this.activeTabId = nextTab.id;
            }
        },

        goHome() {
            const tab = this.currentTab;
            if (!tab) return;
            tab.inputUrl = '';
            tab.currentUrl = '';
            tab.title = 'Start Page';
            tab.isStartPage = true;
            tab.loading = false;
        },

        refresh() {
            const tab = this.currentTab;
            if (!tab || tab.isStartPage) return;
            tab.loading = true;
            const iframe = document.getElementById('iframe-tab-' + tab.id);
            if (iframe) {
                iframe.src = tab.currentUrl;
            }
        },

        openExternal() {
            if (this.currentTab) {
                const target = this.currentTab.isStartPage ? 'https://google.com' : (this.currentTab.currentUrl || 'https://google.com');
                window.open(target, '_blank');
            }
        },

        handleLoaded(tab) {
            if (tab) {
                tab.loading = false;
            }
        }
    }"
    class="flex h-full w-full flex-col bg-[#1e1e1e] text-neutral-200 font-sans overflow-hidden select-none"
>
    {{-- macOS Safari Header Bar --}}
    <div class="flex flex-col border-b border-black/40 bg-[#2b2b2b]/95 backdrop-blur-xl">
        {{-- Tabs Bar --}}
        <div class="flex items-center gap-1 px-3 pt-2 pb-1 overflow-x-auto scrollbar-none border-b border-black/20">
            <template x-for="tab in tabs" :key="tab.id">
                <div
                    @click="activeTabId = tab.id"
                    class="group relative flex h-7 min-w-36 max-w-56 flex-1 items-center justify-between gap-2 rounded-t-lg px-3 text-xs transition-all cursor-pointer"
                    :class="activeTabId === tab.id
                        ? 'bg-[#1e1e1e] text-white font-medium shadow-sm border-t border-x border-white/10'
                        : 'text-neutral-400 hover:bg-white/5 hover:text-neutral-200'"
                >
                    <div class="flex items-center gap-1.5 truncate">
                        {{-- Favicon / Globe Icon --}}
                        <svg class="size-3.5 flex-shrink-0" :class="activeTabId === tab.id ? 'text-sky-400' : 'text-neutral-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke-width="1.8"/>
                            <path stroke-width="1.5" d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                        </svg>
                        <span x-text="tab.title" class="truncate"></span>
                    </div>

                    {{-- Close Tab Button --}}
                    <button
                        x-show="tabs.length > 1"
                        @click.stop="closeTab(tab.id)"
                        type="button"
                        class="flex h-4 w-4 items-center justify-center rounded-full opacity-60 hover:opacity-100 hover:bg-white/20 transition-all"
                    >
                        <svg class="size-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>

            {{-- New Tab Button --}}
            <button
                @click="addTab()"
                type="button"
                title="Buka Tab Baru"
                class="flex h-7 w-7 items-center justify-center rounded-lg text-neutral-400 hover:bg-white/10 hover:text-white transition-all ml-1"
            >
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
        </div>

        {{-- Toolbar / Address Bar Row --}}
        <div class="flex items-center gap-2.5 px-3 py-2">
            {{-- Navigation Action Controls --}}
            <div class="flex items-center gap-1">
                {{-- Back Button --}}
                <button
                    @click="refresh()"
                    type="button"
                    title="Kembali"
                    class="flex h-7 w-7 items-center justify-center rounded-md text-neutral-400 hover:bg-white/10 hover:text-white transition-all disabled:opacity-30"
                >
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                {{-- Forward Button --}}
                <button
                    type="button"
                    title="Maju"
                    disabled
                    class="flex h-7 w-7 items-center justify-center rounded-md text-neutral-500 opacity-40 transition-all cursor-not-allowed"
                >
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                {{-- Refresh Button --}}
                <button
                    @click="refresh()"
                    type="button"
                    title="Muat Ulang Halaman"
                    class="flex h-7 w-7 items-center justify-center rounded-md text-neutral-400 hover:bg-white/10 hover:text-white transition-all"
                >
                    <svg class="size-3.5" :class="{ 'animate-spin': currentTab?.loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>

                {{-- Home Button --}}
                <button
                    @click="goHome()"
                    type="button"
                    title="Halaman Utama"
                    class="flex h-7 w-7 items-center justify-center rounded-md text-neutral-400 hover:bg-white/10 hover:text-white transition-all"
                >
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 00-1 1m-6 0h6"/>
                    </svg>
                </button>
            </div>

            {{-- Capsule Address Bar (macOS Safari Pill) --}}
            <form @submit.prevent="navigate()" class="flex flex-1 items-center relative">
                <div class="pointer-events-none absolute left-3.5 flex items-center text-emerald-400">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>

                <input
                    x-model="currentTab.inputUrl"
                    @focus="$el.select()"
                    type="text"
                    placeholder="Cari dengan Google atau masukkan alamat URL..."
                    class="w-full rounded-xl bg-[#141414]/90 border border-white/10 py-1.5 pl-9 pr-16 text-xs text-neutral-100 placeholder-neutral-500 focus:border-sky-500/80 focus:bg-black focus:outline-none focus:ring-1 focus:ring-sky-500/80 transition-all text-center focus:text-left shadow-inner"
                />

                <div class="absolute right-2 flex items-center gap-1">
                    {{-- Open External Button --}}
                    <button
                        @click="openExternal()"
                        type="button"
                        title="Buka di Tab Eksternal Browser"
                        class="flex h-5 w-5 items-center justify-center rounded-md text-neutral-400 hover:bg-white/15 hover:text-white transition-all"
                    >
                        <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Loading Bar Indicator --}}
    <div x-show="currentTab?.loading" x-transition.opacity class="h-0.5 w-full bg-neutral-900 overflow-hidden z-20">
        <div class="h-full bg-gradient-to-r from-sky-400 via-indigo-500 to-purple-500 animate-pulse w-full"></div>
    </div>

    {{-- Browser Viewport Area --}}
    <div class="relative flex-1 bg-white">
        <template x-for="tab in tabs" :key="tab.id">
            <div
                x-show="activeTabId === tab.id"
                class="h-full w-full"
            >
                {{-- Start Page / Bookmarks --}}
                <div
                    x-show="tab.isStartPage"
                    class="flex h-full w-full flex-col items-center justify-center bg-[#181818] p-8 text-neutral-100"
                >
                    <div class="flex flex-col items-center max-w-lg text-center space-y-6">
                        {{-- macOS Safari Compass Badge --}}
                        <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-tr from-sky-600 to-blue-500 shadow-xl shadow-sky-500/10 border border-white/20">
                            <x-minios.icon name="browser" class="size-12" />
                        </div>

                        <div>
                            <h2 class="text-xl font-medium tracking-tight text-white">Safari Web Browser</h2>
                            <p class="text-xs text-neutral-400 mt-1">Cari atau pilih favorit di bawah untuk mulai menjelajah</p>
                        </div>

                        {{-- Central Search Box on Start Page --}}
                        <form @submit.prevent="navigate()" class="w-full max-w-md relative">
                            <div class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 flex items-center text-neutral-400">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input
                                x-model="currentTab.inputUrl"
                                type="text"
                                placeholder="Cari dengan Google atau ketik URL..."
                                class="w-full rounded-2xl bg-white/10 hover:bg-white/15 focus:bg-black/90 border border-white/15 py-2.5 pl-11 pr-4 text-xs text-white placeholder-neutral-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 transition-all shadow-lg"
                            />
                        </form>

                        {{-- Favorites Grid --}}
                        <div class="grid grid-cols-4 gap-4 w-full pt-2">
                            <button
                                @click="openBookmark('https://google.com', 'Google')"
                                class="flex flex-col items-center gap-2 p-3 rounded-2xl bg-white/5 border border-white/5 hover:bg-white/10 hover:scale-105 transition-all group"
                            >
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-neutral-900 font-bold text-base shadow">G</div>
                                <span class="text-[11px] font-medium text-neutral-300 group-hover:text-white">Google</span>
                            </button>

                            <button
                                @click="openBookmark('https://wikipedia.org', 'Wikipedia')"
                                class="flex flex-col items-center gap-2 p-3 rounded-2xl bg-white/5 border border-white/5 hover:bg-white/10 hover:scale-105 transition-all group"
                            >
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-neutral-800 text-white font-bold text-base shadow">W</div>
                                <span class="text-[11px] font-medium text-neutral-300 group-hover:text-white">Wikipedia</span>
                            </button>

                            <button
                                @click="openBookmark('https://duckduckgo.com', 'DuckDuckGo')"
                                class="flex flex-col items-center gap-2 p-3 rounded-2xl bg-white/5 border border-white/5 hover:bg-white/10 hover:scale-105 transition-all group"
                            >
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-white font-bold text-base shadow">D</div>
                                <span class="text-[11px] font-medium text-neutral-300 group-hover:text-white">DuckDuckGo</span>
                            </button>

                            <button
                                @click="openBookmark('https://laravel.com', 'Laravel')"
                                class="flex flex-col items-center gap-2 p-3 rounded-2xl bg-white/5 border border-white/5 hover:bg-white/10 hover:scale-105 transition-all group"
                            >
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-600 text-white font-bold text-base shadow">L</div>
                                <span class="text-[11px] font-medium text-neutral-300 group-hover:text-white">Laravel</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Iframe Content --}}
                <iframe
                    x-show="!tab.isStartPage && tab.currentUrl"
                    :id="'iframe-tab-' + tab.id"
                    :src="tab.currentUrl || 'about:blank'"
                    @load="handleLoaded(tab)"
                    class="h-full w-full border-none bg-white"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                ></iframe>
            </div>
        </template>
    </div>
</div>
