<x-layouts::minios.auth :title="__('Reset password')">
    <div class="flex w-full flex-col items-center text-center">
        {{-- Lock Avatar Icon --}}
        <div class="relative flex size-24 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 shadow-2xl ring-4 ring-white/10 mb-6">
            <span class="text-3xl font-bold uppercase tracking-wider text-white">
                <flux:icon name="lock-closed" class="size-12 text-white" />
            </span>
        </div>

        {{-- Title & Subtitle --}}
        <h2 class="mt-4 text-xl font-bold tracking-tight text-white sm:text-2xl">
            {{ __('Reset Password') }}
        </h2>
        <p class="text-xs text-white/60">
            {{ __('Please enter your new password below') }}
        </p>

        <!-- Session Status -->
        <x-auth-session-status class="mt-3 text-center text-xs font-medium text-emerald-400" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 w-full space-y-4 text-left">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <flux:input
                name="email"
                value="{{ request('email') }}"
                :label="__('Email address')"
                type="email"
                required
                autocomplete="email"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('New Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('New Password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="pt-2">
                <flux:button type="submit" variant="primary" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-medium py-3 rounded-xl shadow-lg shadow-indigo-600/30 transition-all duration-200" data-test="reset-password-button">
                    {{ __('Reset password') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::minios.auth>
