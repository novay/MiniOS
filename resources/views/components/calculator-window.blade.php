<div
    x-data="{
        display: '0',
        prevValue: null,
        operator: null,
        waitingForOperand: false,
        copied: false,
        activeKey: null,
        historyOpen: false,
        history: [],
        memory: null,

        get hasMemory() {
            return this.memory !== null && this.memory !== 0;
        },

        cleanNumber(val) {
            if (typeof val !== 'number' || isNaN(val) || !isFinite(val)) return 'Error';
            const cleaned = parseFloat(val.toPrecision(12));
            return String(cleaned);
        },

        formatNumber(val) {
            if (val === 'Error' || val === 'Tidak bisa dibagi 0') return val;
            if (!val) return '0';
            const parts = val.split('.');
            const intPart = parts[0];
            const isNegative = intPart.startsWith('-');
            const cleanDigits = isNegative ? intPart.slice(1) : intPart;
            const formattedInt = cleanDigits.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            const formatted = (isNegative ? '-' : '') + formattedInt;
            return parts.length > 1 ? formatted + '.' + parts[1] : formatted;
        },

        get formattedDisplay() {
            return this.formatNumber(this.display);
        },

        get expression() {
            if (this.prevValue === null) return '';
            return `${this.formatNumber(String(this.prevValue))} ${this.operator || ''}`;
        },

        appendDigit(digit) {
            this.flashKey(digit);
            if (this.display === 'Error' || this.waitingForOperand) {
                this.display = digit;
                this.waitingForOperand = false;
                return;
            }
            const rawDigits = this.display.replace(/[^0-9]/g, '');
            if (rawDigits.length >= 14) return;

            this.display = this.display === '0' ? digit : this.display + digit;
        },

        appendDot() {
            this.flashKey('.');
            if (this.display === 'Error' || this.waitingForOperand) {
                this.display = '0.';
                this.waitingForOperand = false;
                return;
            }
            if (!this.display.includes('.')) {
                this.display += '.';
            }
        },

        backspace() {
            this.flashKey('backspace');
            if (this.display === 'Error' || this.waitingForOperand) {
                this.clearEntry();
                return;
            }
            if (this.display.length <= 1 || (this.display.length === 2 && this.display.startsWith('-'))) {
                this.display = '0';
                return;
            }
            this.display = this.display.slice(0, -1);
        },

        clearEntry() {
            this.display = '0';
        },

        clearAll() {
            this.display = '0';
            this.prevValue = null;
            this.operator = null;
            this.waitingForOperand = false;
        },

        handleClear() {
            this.flashKey('clear');
            if (this.display !== '0' && !this.waitingForOperand) {
                this.clearEntry();
            } else {
                this.clearAll();
            }
        },

        toggleSign() {
            this.flashKey('sign');
            if (this.display === '0' || this.display === 'Error') return;
            if (this.display.startsWith('-')) {
                this.display = this.display.slice(1);
            } else {
                this.display = '-' + this.display;
            }
        },

        percentage() {
            this.flashKey('%');
            if (this.display === 'Error') return;
            const current = parseFloat(this.display);
            if (isNaN(current)) return;
            this.display = this.cleanNumber(current / 100);
        },

        // Quick advanced operations
        sqrt() {
            this.flashKey('sqrt');
            if (this.display === 'Error') return;
            const current = parseFloat(this.display);
            if (current < 0) {
                this.display = 'Error';
                return;
            }
            const res = this.cleanNumber(Math.sqrt(current));
            this.addHistory(`√(${this.formatNumber(this.display)})`, res);
            this.display = res;
            this.waitingForOperand = true;
        },

        square() {
            this.flashKey('sqr');
            if (this.display === 'Error') return;
            const current = parseFloat(this.display);
            const res = this.cleanNumber(Math.pow(current, 2));
            this.addHistory(`sqr(${this.formatNumber(this.display)})`, res);
            this.display = res;
            this.waitingForOperand = true;
        },

        // Memory operations
        memoryClear() {
            this.flashKey('mc');
            this.memory = null;
        },

        memoryRecall() {
            this.flashKey('mr');
            if (this.memory !== null) {
                this.display = this.cleanNumber(this.memory);
                this.waitingForOperand = true;
            }
        },

        memoryAdd() {
            this.flashKey('m+');
            if (this.display === 'Error') return;
            const current = parseFloat(this.display);
            if (!isNaN(current)) {
                this.memory = (this.memory || 0) + current;
                this.waitingForOperand = true;
            }
        },

        memorySubtract() {
            this.flashKey('m-');
            if (this.display === 'Error') return;
            const current = parseFloat(this.display);
            if (!isNaN(current)) {
                this.memory = (this.memory || 0) - current;
                this.waitingForOperand = true;
            }
        },

        setOperator(op) {
            this.flashKey(op);
            if (this.display === 'Error') return;
            const inputValue = parseFloat(this.display);

            if (this.prevValue === null) {
                this.prevValue = inputValue;
            } else if (this.operator && !this.waitingForOperand) {
                const result = this.performCalculation();
                if (result === 'Error') {
                    this.display = 'Error';
                    this.prevValue = null;
                    this.operator = null;
                    this.waitingForOperand = true;
                    return;
                }
                this.display = this.cleanNumber(result);
                this.prevValue = result;
            }

            this.waitingForOperand = true;
            this.operator = op;
        },

        performCalculation() {
            const prev = parseFloat(this.prevValue);
            const current = parseFloat(this.display);
            if (isNaN(prev) || isNaN(current)) return current;

            switch (this.operator) {
                case '+': return prev + current;
                case '-': return prev - current;
                case '×': return prev * current;
                case '÷': return current === 0 ? 'Error' : prev / current;
                default: return current;
            }
        },

        equals() {
            this.flashKey('=');
            if (this.operator === null || this.prevValue === null || this.display === 'Error') return;
            const prev = this.prevValue;
            const op = this.operator;
            const current = this.display;
            const result = this.performCalculation();

            if (result === 'Error') {
                this.display = 'Error';
            } else {
                const cleaned = this.cleanNumber(result);
                this.addHistory(`${this.formatNumber(String(prev))} ${op} ${this.formatNumber(String(current))}`, cleaned);
                this.display = cleaned;
            }
            this.prevValue = null;
            this.operator = null;
            this.waitingForOperand = true;
        },

        addHistory(expr, res) {
            this.history.unshift({
                expression: expr,
                result: res
            });
            if (this.history.length > 25) {
                this.history.pop();
            }
        },

        recallHistory(item) {
            this.display = item.result;
            this.waitingForOperand = true;
            this.historyOpen = false;
        },

        clearHistory() {
            this.history = [];
        },

        flashKey(key) {
            this.activeKey = key;
            setTimeout(() => {
                if (this.activeKey === key) this.activeKey = null;
            }, 120);
        },

        async copyDisplay() {
            if (this.display === 'Error') return;
            try {
                await navigator.clipboard.writeText(this.display);
                this.copied = true;
                setTimeout(() => this.copied = false, 1500);
            } catch (e) {
                console.error(e);
            }
        },

        async pasteDisplay() {
            try {
                const text = await navigator.clipboard.readText();
                const cleaned = (text || '').trim().replace(/,/g, '');
                const num = parseFloat(cleaned);
                if (!isNaN(num) && isFinite(num)) {
                    this.display = this.cleanNumber(num);
                    this.waitingForOperand = false;
                }
            } catch (e) {
                console.error(e);
            }
        },

        handleKeyDown(e) {
            if (typeof this.isWindowFocused === 'function' && !this.isWindowFocused('calculator')) {
                return;
            }
            if (['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName)) {
                return;
            }

            if ((e.metaKey || e.ctrlKey) && (e.key === 'c' || e.key === 'C')) {
                e.preventDefault();
                this.copyDisplay();
                return;
            }
            if ((e.metaKey || e.ctrlKey) && (e.key === 'v' || e.key === 'V')) {
                e.preventDefault();
                this.pasteDisplay();
                return;
            }

            const key = e.key;
            if (/^[0-9]$/.test(key)) {
                e.preventDefault();
                this.appendDigit(key);
            } else if (key === '.' || key === ',') {
                e.preventDefault();
                this.appendDot();
            } else if (key === '+') {
                e.preventDefault();
                this.setOperator('+');
            } else if (key === '-') {
                e.preventDefault();
                this.setOperator('-');
            } else if (key === '*' || key === 'x' || key === 'X') {
                e.preventDefault();
                this.setOperator('×');
            } else if (key === '/') {
                e.preventDefault();
                this.setOperator('÷');
            } else if (key === 'Enter' || key === '=') {
                e.preventDefault();
                this.equals();
            } else if (key === 'Backspace') {
                e.preventDefault();
                this.backspace();
            } else if (key === 'Escape') {
                e.preventDefault();
                if (this.historyOpen) {
                    this.historyOpen = false;
                } else {
                    this.handleClear();
                }
            } else if (key === '%') {
                e.preventDefault();
                this.percentage();
            }
        }
    }"
    @keydown.window="handleKeyDown($event)"
    class="relative flex h-full w-full flex-col bg-zinc-100/90 dark:bg-[#18181b]/95 text-zinc-900 dark:text-zinc-100 font-sans p-3 sm:p-3.5 overflow-hidden select-none backdrop-blur-2xl transition-colors duration-200"
>
    {{-- Calculator Display Inset Card --}}
    <div class="relative flex flex-col justify-between rounded-2xl bg-white/75 dark:bg-black/35 border border-black/[0.06] dark:border-white/[0.08] p-3 mb-2 shadow-[inset_0_1px_3px_rgba(0,0,0,0.05)] dark:shadow-[inset_0_1px_3px_rgba(0,0,0,0.3)] transition-all">
        {{-- Expression & Action Toolbar --}}
        <div class="flex items-center justify-between min-h-5 text-xs mb-1">
            <div class="flex items-center gap-1.5 truncate">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium tracking-wide bg-neutral-200/80 dark:bg-white/10 text-neutral-600 dark:text-neutral-400">
                    Standard
                </span>
                <span
                    x-show="hasMemory"
                    x-transition.opacity
                    class="inline-flex items-center px-1 py-0.2 rounded text-[9px] font-bold tracking-wider bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/30"
                >
                    M
                </span>
                <span class="font-mono text-[11px] text-neutral-500 dark:text-neutral-400 truncate tracking-wide" x-text="expression"></span>
            </div>

            <div class="flex items-center gap-1 shrink-0">
                {{-- History Toggle Button --}}
                <button
                    type="button"
                    @click="historyOpen = !historyOpen"
                    class="p-1 rounded-md transition-colors relative"
                    :class="historyOpen ? 'bg-amber-500 text-white dark:bg-amber-500' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10'"
                    title="Riwayat perhitungan"
                >
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>

                {{-- Copy Button --}}
                <button
                    type="button"
                    @click="copyDisplay()"
                    class="p-1 rounded-md text-neutral-500 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition-colors relative"
                    title="Salin hasil (⌘C)"
                >
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span
                        x-show="copied"
                        x-transition.opacity
                        class="absolute -top-7 right-0 px-2 py-0.5 text-[10px] bg-emerald-600 text-white font-medium rounded-md shadow-md pointer-events-none"
                    >
                        Tersalin!
                    </span>
                </button>

                {{-- Backspace Button --}}
                <button
                    type="button"
                    @click="backspace()"
                    class="p-1 rounded-md text-neutral-500 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                    title="Hapus digit terakhir (Backspace)"
                >
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414-6.414a2 2 0 011.414-.586H19a2 2 0 012 2v10a2 2 0 01-2 2h-7.172a2 2 0 01-1.414-.586L3 12z" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Main Result Value with dynamic scaling & crisp tabular figures --}}
        <div
            x-text="formattedDisplay"
            class="text-right font-light tracking-tight truncate font-mono tabular-nums text-neutral-900 dark:text-white transition-all duration-150 py-0.5"
            :class="{
                'text-4xl': formattedDisplay.length <= 9,
                'text-3xl': formattedDisplay.length > 9 && formattedDisplay.length <= 13,
                'text-2xl': formattedDisplay.length > 13
            }"
        ></div>
    </div>

    {{-- Memory & Quick Math Bar --}}
    <div class="grid grid-cols-6 gap-1 mb-2 px-0.5 text-xs">
        <button
            type="button"
            @click="memoryClear()"
            :disabled="!hasMemory"
            class="py-1 px-1 rounded-lg text-center font-medium transition-all"
            :class="hasMemory ? 'text-neutral-700 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/10 active:scale-95' : 'text-neutral-400/40 dark:text-neutral-600/40 cursor-not-allowed'"
            title="Memory Clear (MC)"
        >
            MC
        </button>
        <button
            type="button"
            @click="memoryRecall()"
            :disabled="!hasMemory"
            class="py-1 px-1 rounded-lg text-center font-medium transition-all"
            :class="hasMemory ? 'text-amber-600 dark:text-amber-400 font-semibold hover:bg-black/5 dark:hover:bg-white/10 active:scale-95' : 'text-neutral-400/40 dark:text-neutral-600/40 cursor-not-allowed'"
            title="Memory Recall (MR)"
        >
            MR
        </button>
        <button
            type="button"
            @click="memoryAdd()"
            class="py-1 px-1 rounded-lg text-center font-medium text-neutral-700 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/10 active:scale-95 transition-all"
            title="Memory Add (M+)"
        >
            M+
        </button>
        <button
            type="button"
            @click="memorySubtract()"
            class="py-1 px-1 rounded-lg text-center font-medium text-neutral-700 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/10 active:scale-95 transition-all"
            title="Memory Subtract (M-)"
        >
            M−
        </button>
        <button
            type="button"
            @click="sqrt()"
            class="py-1 px-1 rounded-lg text-center font-medium text-neutral-700 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/10 active:scale-95 transition-all"
            title="Akar Kuadrat (√x)"
        >
            √x
        </button>
        <button
            type="button"
            @click="square()"
            class="py-1 px-1 rounded-lg text-center font-medium text-neutral-700 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/10 active:scale-95 transition-all"
            title="Kuadrat (x²)"
        >
            x²
        </button>
    </div>

    {{-- Keypad Buttons Grid --}}
    <div class="grid grid-cols-4 gap-2 flex-1">
        {{-- Row 1: Special Functions & Divide --}}
        <button
            @click="handleClear()"
            type="button"
            class="flex items-center justify-center rounded-xl bg-neutral-200/80 hover:bg-neutral-300/80 dark:bg-white/[0.12] dark:hover:bg-white/[0.18] text-neutral-700 dark:text-neutral-200 font-semibold text-base border border-black/[0.05] dark:border-white/[0.06] shadow-xs active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="activeKey === 'clear' ? 'scale-[0.94] brightness-125' : ''"
        >
            <span x-text="display !== '0' && !waitingForOperand ? 'C' : 'AC'"></span>
        </button>
        <button
            @click="toggleSign()"
            type="button"
            class="flex items-center justify-center rounded-xl bg-neutral-200/80 hover:bg-neutral-300/80 dark:bg-white/[0.12] dark:hover:bg-white/[0.18] text-neutral-700 dark:text-neutral-200 font-semibold text-base border border-black/[0.05] dark:border-white/[0.06] shadow-xs active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="activeKey === 'sign' ? 'scale-[0.94] brightness-125' : ''"
        >±</button>
        <button
            @click="percentage()"
            type="button"
            class="flex items-center justify-center rounded-xl bg-neutral-200/80 hover:bg-neutral-300/80 dark:bg-white/[0.12] dark:hover:bg-white/[0.18] text-neutral-700 dark:text-neutral-200 font-semibold text-base border border-black/[0.05] dark:border-white/[0.06] shadow-xs active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="activeKey === '%' ? 'scale-[0.94] brightness-125' : ''"
        >%</button>
        <button
            @click="setOperator('÷')"
            type="button"
            class="flex items-center justify-center rounded-xl bg-amber-500 hover:bg-amber-400 dark:bg-amber-500 dark:hover:bg-amber-400 text-white font-bold text-xl border border-amber-400/40 shadow-sm active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="[
                operator === '÷' && waitingForOperand ? 'ring-2 ring-amber-400 dark:ring-amber-300 ring-offset-2 ring-offset-zinc-100 dark:ring-offset-[#18181b]' : '',
                activeKey === '÷' ? 'scale-[0.94] brightness-125' : ''
            ]"
        >÷</button>

        {{-- Row 2: 7, 8, 9, Multiply --}}
        <button
            @click="appendDigit('7')"
            type="button"
            class="flex items-center justify-center rounded-xl bg-white hover:bg-neutral-50 dark:bg-white/[0.07] dark:hover:bg-white/[0.12] text-neutral-800 dark:text-neutral-100 font-medium text-xl border border-black/[0.05] dark:border-white/[0.06] shadow-xs active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="activeKey === '7' ? 'scale-[0.94] brightness-125' : ''"
        >7</button>
        <button
            @click="appendDigit('8')"
            type="button"
            class="flex items-center justify-center rounded-xl bg-white hover:bg-neutral-50 dark:bg-white/[0.07] dark:hover:bg-white/[0.12] text-neutral-800 dark:text-neutral-100 font-medium text-xl border border-black/[0.05] dark:border-white/[0.06] shadow-xs active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="activeKey === '8' ? 'scale-[0.94] brightness-125' : ''"
        >8</button>
        <button
            @click="appendDigit('9')"
            type="button"
            class="flex items-center justify-center rounded-xl bg-white hover:bg-neutral-50 dark:bg-white/[0.07] dark:hover:bg-white/[0.12] text-neutral-800 dark:text-neutral-100 font-medium text-xl border border-black/[0.05] dark:border-white/[0.06] shadow-xs active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="activeKey === '9' ? 'scale-[0.94] brightness-125' : ''"
        >9</button>
        <button
            @click="setOperator('×')"
            type="button"
            class="flex items-center justify-center rounded-xl bg-amber-500 hover:bg-amber-400 dark:bg-amber-500 dark:hover:bg-amber-400 text-white font-bold text-xl border border-amber-400/40 shadow-sm active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="[
                operator === '×' && waitingForOperand ? 'ring-2 ring-amber-400 dark:ring-amber-300 ring-offset-2 ring-offset-zinc-100 dark:ring-offset-[#18181b]' : '',
                activeKey === '×' ? 'scale-[0.94] brightness-125' : ''
            ]"
        >×</button>

        {{-- Row 3: 4, 5, 6, Subtract --}}
        <button
            @click="appendDigit('4')"
            type="button"
            class="flex items-center justify-center rounded-xl bg-white hover:bg-neutral-50 dark:bg-white/[0.07] dark:hover:bg-white/[0.12] text-neutral-800 dark:text-neutral-100 font-medium text-xl border border-black/[0.05] dark:border-white/[0.06] shadow-xs active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="activeKey === '4' ? 'scale-[0.94] brightness-125' : ''"
        >4</button>
        <button
            @click="appendDigit('5')"
            type="button"
            class="flex items-center justify-center rounded-xl bg-white hover:bg-neutral-50 dark:bg-white/[0.07] dark:hover:bg-white/[0.12] text-neutral-800 dark:text-neutral-100 font-medium text-xl border border-black/[0.05] dark:border-white/[0.06] shadow-xs active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="activeKey === '5' ? 'scale-[0.94] brightness-125' : ''"
        >5</button>
        <button
            @click="appendDigit('6')"
            type="button"
            class="flex items-center justify-center rounded-xl bg-white hover:bg-neutral-50 dark:bg-white/[0.07] dark:hover:bg-white/[0.12] text-neutral-800 dark:text-neutral-100 font-medium text-xl border border-black/[0.05] dark:border-white/[0.06] shadow-xs active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="activeKey === '6' ? 'scale-[0.94] brightness-125' : ''"
        >6</button>
        <button
            @click="setOperator('-')"
            type="button"
            class="flex items-center justify-center rounded-xl bg-amber-500 hover:bg-amber-400 dark:bg-amber-500 dark:hover:bg-amber-400 text-white font-bold text-xl border border-amber-400/40 shadow-sm active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="[
                operator === '-' && waitingForOperand ? 'ring-2 ring-amber-400 dark:ring-amber-300 ring-offset-2 ring-offset-zinc-100 dark:ring-offset-[#18181b]' : '',
                activeKey === '-' ? 'scale-[0.94] brightness-125' : ''
            ]"
        >−</button>

        {{-- Row 4: 1, 2, 3, Add --}}
        <button
            @click="appendDigit('1')"
            type="button"
            class="flex items-center justify-center rounded-xl bg-white hover:bg-neutral-50 dark:bg-white/[0.07] dark:hover:bg-white/[0.12] text-neutral-800 dark:text-neutral-100 font-medium text-xl border border-black/[0.05] dark:border-white/[0.06] shadow-xs active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="activeKey === '1' ? 'scale-[0.94] brightness-125' : ''"
        >1</button>
        <button
            @click="appendDigit('2')"
            type="button"
            class="flex items-center justify-center rounded-xl bg-white hover:bg-neutral-50 dark:bg-white/[0.07] dark:hover:bg-white/[0.12] text-neutral-800 dark:text-neutral-100 font-medium text-xl border border-black/[0.05] dark:border-white/[0.06] shadow-xs active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="activeKey === '2' ? 'scale-[0.94] brightness-125' : ''"
        >2</button>
        <button
            @click="appendDigit('3')"
            type="button"
            class="flex items-center justify-center rounded-xl bg-white hover:bg-neutral-50 dark:bg-white/[0.07] dark:hover:bg-white/[0.12] text-neutral-800 dark:text-neutral-100 font-medium text-xl border border-black/[0.05] dark:border-white/[0.06] shadow-xs active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="activeKey === '3' ? 'scale-[0.94] brightness-125' : ''"
        >3</button>
        <button
            @click="setOperator('+')"
            type="button"
            class="flex items-center justify-center rounded-xl bg-amber-500 hover:bg-amber-400 dark:bg-amber-500 dark:hover:bg-amber-400 text-white font-bold text-xl border border-amber-400/40 shadow-sm active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="[
                operator === '+' && waitingForOperand ? 'ring-2 ring-amber-400 dark:ring-amber-300 ring-offset-2 ring-offset-zinc-100 dark:ring-offset-[#18181b]' : '',
                activeKey === '+' ? 'scale-[0.94] brightness-125' : ''
            ]"
        >+</button>

        {{-- Row 5: 0 (span 2), ., Equals --}}
        <button
            @click="appendDigit('0')"
            type="button"
            class="col-span-2 flex items-center justify-start pl-6 rounded-xl bg-white hover:bg-neutral-50 dark:bg-white/[0.07] dark:hover:bg-white/[0.12] text-neutral-800 dark:text-neutral-100 font-medium text-xl border border-black/[0.05] dark:border-white/[0.06] shadow-xs active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="activeKey === '0' ? 'scale-[0.94] brightness-125' : ''"
        >0</button>
        <button
            @click="appendDot()"
            type="button"
            class="flex items-center justify-center rounded-xl bg-white hover:bg-neutral-50 dark:bg-white/[0.07] dark:hover:bg-white/[0.12] text-neutral-800 dark:text-neutral-100 font-medium text-xl border border-black/[0.05] dark:border-white/[0.06] shadow-xs active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="activeKey === '.' ? 'scale-[0.94] brightness-125' : ''"
        >.</button>
        <button
            @click="equals()"
            type="button"
            class="flex items-center justify-center rounded-xl bg-amber-500 hover:bg-amber-400 dark:bg-amber-500 dark:hover:bg-amber-400 text-white font-bold text-xl border border-amber-400/40 shadow-md active:scale-[0.96] transition-all duration-100 focus:outline-none"
            :class="activeKey === '=' ? 'scale-[0.94] brightness-125' : ''"
        >=</button>
    </div>

    {{-- History Tape Drawer Overlay --}}
    <div
        x-show="historyOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="absolute inset-0 z-20 flex flex-col bg-zinc-100/98 dark:bg-[#18181b]/98 backdrop-blur-2xl p-3.5"
    >
        {{-- Drawer Header --}}
        <div class="flex items-center justify-between pb-2 mb-2 border-b border-black/[0.08] dark:border-white/[0.08]">
            <div class="flex items-center gap-1.5 font-semibold text-xs text-neutral-800 dark:text-neutral-200">
                <svg class="size-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Riwayat Perhitungan</span>
            </div>

            <div class="flex items-center gap-1">
                <button
                    type="button"
                    x-show="history.length > 0"
                    @click="clearHistory()"
                    class="px-2 py-0.5 rounded text-[11px] text-rose-600 dark:text-rose-400 hover:bg-rose-500/10 transition-colors"
                    title="Hapus semua riwayat"
                >
                    Hapus
                </button>
                <button
                    type="button"
                    @click="historyOpen = false"
                    class="p-1 rounded-md text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                    title="Tutup riwayat (Esc)"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- History Items List --}}
        <div class="flex-1 overflow-y-auto space-y-1.5 pr-1 text-right">
            <template x-if="history.length === 0">
                <div class="flex flex-col items-center justify-center h-full text-center py-12 text-neutral-400 dark:text-neutral-500">
                    <svg class="size-8 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="text-xs">Belum ada riwayat perhitungan</p>
                    <p class="text-[11px] text-neutral-400/80 mt-0.5">Hasil hitungan Anda akan tersimpan otomatis di sini</p>
                </div>
            </template>

            <template x-for="(item, index) in history" :key="index">
                <button
                    type="button"
                    @click="recallHistory(item)"
                    class="w-full text-right p-2.5 rounded-xl hover:bg-white/80 dark:hover:bg-white/[0.08] border border-transparent hover:border-black/5 dark:hover:border-white/5 transition-all group select-none"
                    title="Klik untuk gunakan hasil ini"
                >
                    <div class="text-[11px] text-neutral-500 dark:text-neutral-400 font-mono tracking-tight" x-text="item.expression + ' =' "></div>
                    <div class="text-lg font-semibold font-mono tabular-nums text-neutral-900 dark:text-white group-hover:text-amber-500 dark:group-hover:text-amber-400 transition-colors" x-text="formatNumber(item.result)"></div>
                </button>
            </template>
        </div>
    </div>
</div>
