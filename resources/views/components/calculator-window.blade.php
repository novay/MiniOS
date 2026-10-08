<div
    x-data="{
        display: '0',
        prevValue: null,
        operator: null,
        waitingForOperand: false,

        appendDigit(digit) {
            if (this.waitingForOperand) {
                this.display = digit;
                this.waitingForOperand = false;
            } else {
                this.display = this.display === '0' ? digit : this.display + digit;
            }
        },

        appendDot() {
            if (this.waitingForOperand) {
                this.display = '0.';
                this.waitingForOperand = false;
                return;
            }
            if (!this.display.includes('.')) {
                this.display += '.';
            }
        },

        clear() {
            this.display = '0';
            this.prevValue = null;
            this.operator = null;
            this.waitingForOperand = false;
        },

        toggleSign() {
            this.display = String(parseFloat(this.display) * -1);
        },

        percentage() {
            this.display = String(parseFloat(this.display) / 100);
        },

        setOperator(op) {
            const inputValue = parseFloat(this.display);

            if (this.prevValue === null) {
                this.prevValue = inputValue;
            } else if (this.operator && !this.waitingForOperand) {
                const result = this.performCalculation();
                this.display = String(result);
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
            if (this.operator === null || this.prevValue === null) return;
            const result = this.performCalculation();
            this.display = String(result);
            this.prevValue = null;
            this.operator = null;
            this.waitingForOperand = true;
        }
    }"
    class="flex h-full w-full flex-col bg-[#1c1c1e] text-white font-sans p-3 overflow-hidden select-none"
>
    {{-- Calculator Display --}}
    <div class="flex flex-col justify-end h-24 px-3 py-2 mb-2 text-right">
        <div class="text-[10px] text-neutral-400 font-mono min-h-4">
            <span x-text="prevValue !== null ? prevValue + ' ' + (operator || '') : ''"></span>
        </div>
        <div x-text="display" class="text-4xl font-light tracking-tight truncate"></div>
    </div>

    {{-- Keypad Buttons Grid --}}
    <div class="grid grid-cols-4 gap-2 flex-1">
        {{-- Row 1 --}}
        <button @click="clear()" type="button" class="flex items-center justify-center rounded-xl bg-[#a5a5a5] text-black font-semibold text-base hover:bg-[#d9d9d9] active:scale-95 transition-all">AC</button>
        <button @click="toggleSign()" type="button" class="flex items-center justify-center rounded-xl bg-[#a5a5a5] text-black font-semibold text-base hover:bg-[#d9d9d9] active:scale-95 transition-all">±</button>
        <button @click="percentage()" type="button" class="flex items-center justify-center rounded-xl bg-[#a5a5a5] text-black font-semibold text-base hover:bg-[#d9d9d9] active:scale-95 transition-all">%</button>
        <button @click="setOperator('÷')" type="button" class="flex items-center justify-center rounded-xl bg-[#ff9f0a] text-white font-bold text-xl hover:bg-[#ffb340] active:scale-95 transition-all" :class="operator === '÷' ? 'ring-2 ring-white' : ''">÷</button>

        {{-- Row 2 --}}
        <button @click="appendDigit('7')" type="button" class="flex items-center justify-center rounded-xl bg-[#333333] text-white font-medium text-xl hover:bg-[#444444] active:scale-95 transition-all">7</button>
        <button @click="appendDigit('8')" type="button" class="flex items-center justify-center rounded-xl bg-[#333333] text-white font-medium text-xl hover:bg-[#444444] active:scale-95 transition-all">8</button>
        <button @click="appendDigit('9')" type="button" class="flex items-center justify-center rounded-xl bg-[#333333] text-white font-medium text-xl hover:bg-[#444444] active:scale-95 transition-all">9</button>
        <button @click="setOperator('×')" type="button" class="flex items-center justify-center rounded-xl bg-[#ff9f0a] text-white font-bold text-xl hover:bg-[#ffb340] active:scale-95 transition-all" :class="operator === '×' ? 'ring-2 ring-white' : ''">×</button>

        {{-- Row 3 --}}
        <button @click="appendDigit('4')" type="button" class="flex items-center justify-center rounded-xl bg-[#333333] text-white font-medium text-xl hover:bg-[#444444] active:scale-95 transition-all">4</button>
        <button @click="appendDigit('5')" type="button" class="flex items-center justify-center rounded-xl bg-[#333333] text-white font-medium text-xl hover:bg-[#444444] active:scale-95 transition-all">5</button>
        <button @click="appendDigit('6')" type="button" class="flex items-center justify-center rounded-xl bg-[#333333] text-white font-medium text-xl hover:bg-[#444444] active:scale-95 transition-all">6</button>
        <button @click="setOperator('-')" type="button" class="flex items-center justify-center rounded-xl bg-[#ff9f0a] text-white font-bold text-xl hover:bg-[#ffb340] active:scale-95 transition-all" :class="operator === '-' ? 'ring-2 ring-white' : ''">−</button>

        {{-- Row 4 --}}
        <button @click="appendDigit('1')" type="button" class="flex items-center justify-center rounded-xl bg-[#333333] text-white font-medium text-xl hover:bg-[#444444] active:scale-95 transition-all">1</button>
        <button @click="appendDigit('2')" type="button" class="flex items-center justify-center rounded-xl bg-[#333333] text-white font-medium text-xl hover:bg-[#444444] active:scale-95 transition-all">2</button>
        <button @click="appendDigit('3')" type="button" class="flex items-center justify-center rounded-xl bg-[#333333] text-white font-medium text-xl hover:bg-[#444444] active:scale-95 transition-all">3</button>
        <button @click="setOperator('+')" type="button" class="flex items-center justify-center rounded-xl bg-[#ff9f0a] text-white font-bold text-xl hover:bg-[#ffb340] active:scale-95 transition-all" :class="operator === '+' ? 'ring-2 ring-white' : ''">+</button>

        {{-- Row 5 --}}
        <button @click="appendDigit('0')" type="button" class="col-span-2 flex items-center justify-start pl-6 rounded-xl bg-[#333333] text-white font-medium text-xl hover:bg-[#444444] active:scale-95 transition-all">0</button>
        <button @click="appendDot()" type="button" class="flex items-center justify-center rounded-xl bg-[#333333] text-white font-medium text-xl hover:bg-[#444444] active:scale-95 transition-all">.</button>
        <button @click="equals()" type="button" class="flex items-center justify-center rounded-xl bg-[#ff9f0a] text-white font-bold text-xl hover:bg-[#ffb340] active:scale-95 transition-all">=</button>
    </div>
</div>
