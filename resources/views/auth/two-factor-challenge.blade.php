<x-layouts::minios.auth :title="__('Two-factor authentication')">
    <div class="flex w-full flex-col items-center text-center">
        <div
            class="relative w-full h-auto"
            x-cloak
            x-data="{
                showRecoveryInput: @js($errors->has('recovery_code')),
                code: '',
                recovery_code: '',
                focusOtp() {
                    this.$nextTick(() => this.$refs.otp?.querySelector('input')?.focus());
                },
                init() {
                    if (! this.showRecoveryInput) {
                        this.focusOtp();
                    }
                },
                toggleInput() {
                    this.showRecoveryInput = !this.showRecoveryInput;

                    this.code = '';
                    this.recovery_code = '';

                    $nextTick(() => {
                        this.showRecoveryInput
                            ? this.$refs.recovery_code?.focus()
                            : this.focusOtp();
                    });
                },
            }"
        >
            <div x-show="!showRecoveryInput" class="text-center">
                <div class="relative flex size-24 items-center justify-center mx-auto rounded-full bg-linear-to-br from-emerald-500 to-teal-600 shadow-2xl ring-4 ring-white/10">
                    <span class="text-3xl font-bold uppercase tracking-wider text-white">
                        <flux:icon name="shield-check" class="size-12 text-white" />
                    </span>
                </div>
                <h2 class="mt-4 text-xl font-bold tracking-tight text-white sm:text-2xl">{{ __('Authentication Code') }}</h2>
                <p class="mt-1 text-xs text-white/60">{{ __('Enter the code provided by your authenticator application.') }}</p>
            </div>

            <div x-show="showRecoveryInput" class="text-center">
                <div class="relative flex size-24 items-center justify-center mx-auto rounded-full bg-linear-to-br from-amber-500 to-red-600 shadow-2xl ring-4 ring-white/10">
                    <span class="text-3xl font-bold uppercase tracking-wider text-white">
                        <flux:icon name="key" class="size-12 text-white" />
                    </span>
                </div>
                <h2 class="mt-4 text-xl font-bold tracking-tight text-white sm:text-2xl">{{ __('Recovery Code') }}</h2>
                <p class="mt-1 text-xs text-white/60">{{ __('Please confirm access by entering one of your emergency recovery codes.') }}</p>
            </div>

            <form method="POST" action="{{ route('two-factor.login.store') }}" class="mt-6 w-full text-left">
                @csrf

                <div class="space-y-4 text-center">
                    <div x-show="!showRecoveryInput">
                        <div class="flex items-center justify-center my-4" x-ref="otp">
                            <flux:otp
                                x-model="code"
                                length="6"
                                name="code"
                                label="OTP Code"
                                label:sr-only
                                class="mx-auto"
                             />
                        </div>
                    </div>

                    <div x-show="showRecoveryInput">
                        <div class="my-4 text-left">
                            <flux:input
                                type="text"
                                name="recovery_code"
                                x-ref="recovery_code"
                                x-bind:required="showRecoveryInput"
                                autocomplete="one-time-code"
                                x-model="recovery_code"
                                placeholder="Recovery Code"
                            />
                        </div>

                        @error('recovery_code')
                            <flux:text color="red" class="text-xs">
                                {{ $message }}
                            </flux:text>
                        @enderror
                    </div>

                    <flux:button
                        variant="primary"
                        type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-medium py-3 rounded-xl shadow-lg shadow-indigo-600/30 transition-all duration-200"
                    >
                        {{ __('Continue') }}
                    </flux:button>
                </div>

                <div class="mt-6 text-xs text-center text-white/60 border-t border-white/10 pt-3">
                    <span>{{ __('or you can') }}</span>
                    <button type="button" class="text-indigo-300 hover:text-indigo-200 font-medium ml-1 cursor-pointer focus:outline-none" @click="toggleInput()">
                        <span x-show="!showRecoveryInput">{{ __('login using a recovery code') }}</span>
                        <span x-show="showRecoveryInput">{{ __('login using an authentication code') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::minios.auth>
