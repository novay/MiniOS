<x-layouts::minios.auth :title="__('Confirm password')">
    <div class="flex w-full flex-col items-center text-center">
        {{-- Shield Lock Avatar Icon --}}
        <div class="minios-auth-avatar relative flex size-24 items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-red-600 shadow-2xl ring-4 ring-white/10 mb-6" style="width: 96px; height: 96px; min-width: 96px; min-height: 96px; max-width: 96px; max-height: 96px; margin: 0 auto 1.5rem auto;">
            <span class="text-3xl font-bold uppercase tracking-wider text-white">
                <flux:icon name="shield-exclamation" class="size-12 text-white" />
            </span>
        </div>

        {{-- Title & Subtitle --}}
        <h2 class="mt-4 text-xl font-bold tracking-tight text-white sm:text-2xl">
            {{ __('Confirm Password') }}
        </h2>
        <p class="text-xs text-white/60">
            {{ __('This is a secure area. Please confirm your password before continuing.') }}
        </p>

        <x-auth-session-status class="mt-3 text-center text-xs font-medium text-emerald-400" :status="session('status')" />

        <div class="mt-4 w-full text-left">
            <x-passkey-verify
                options-route="passkey.confirm-options"
                submit-route="passkey.confirm"
                :label="__('Confirm with passkey')"
                :loading-label="__('Confirming...')"
                :separator="__('Or confirm with password')"
            />
        </div>

        <form method="POST" action="{{ route('password.confirm.store') }}" class="mt-4 w-full space-y-4 text-left">
            @csrf

            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Password')"
                viewable
            />

            <div class="pt-2">
                <flux:button variant="primary" type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-medium py-3 rounded-xl shadow-lg shadow-indigo-600/30 transition-all duration-200" data-test="confirm-password-button">
                    {{ __('Confirm') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::minios.auth>
