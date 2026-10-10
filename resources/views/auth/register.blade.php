<x-layouts::minios.auth :title="__('Register')">
    <div class="flex w-full flex-col items-center text-center">
        {{-- User Avatar Icon --}}
        <div class="relative flex size-24 items-center justify-center rounded-full shadow-2xl ring-4 ring-white/10 overflow-hidden bg-white/5">
            <img src="{{ asset('minios/images/logo.png') }}" alt="Logo MiniOS" class="size-full object-contain p-2" width="96" height="96">
        </div>

        {{-- Title & Subtitle --}}
        <h2 class="mt-6 text-xl font-bold tracking-tight text-white sm:text-2xl">
            {{ __('Create Account') }}
        </h2>
        <p class="mt-1 text-sm text-white/60">
            {{ __('Sign up to start using MiniOS Desktop') }}
        </p>

        <!-- Session Status -->
        <x-auth-session-status class="mt-3 text-center text-xs font-medium text-emerald-400" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="mt-6 w-full space-y-4 text-left">
            @csrf

            <!-- Name -->
            <div class="flex flex-col space-y-1">
                <flux:input
                    name="name"
                    :value="old('name')"
                    type="text"
                    autocomplete="name"
                    :placeholder="__('Full name')"
                />
                <x-minios::error name="name" />
            </div>

            <!-- Email Address -->
            <div class="flex flex-col space-y-1">
                <flux:input
                    name="email"
                    :placeholder="__('Email address')"
                    :value="old('email')"
                    type="email"
                    autocomplete="email"
                />
                <x-minios::error name="email" />
            </div>

            <!-- Password -->
            <div class="flex flex-col space-y-1">
                <flux:input
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    :placeholder="__('Password')"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    viewable
                />
                <x-minios::error name="password" />
            </div>

            <!-- Confirm Password -->
            <div class="flex flex-col space-y-1">
                <flux:input
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    :placeholder="__('Confirm password')"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    viewable
                />
                <x-minios::error name="password_confirmation" />
            </div>

            <div class="pt-2">
                <flux:button type="submit" variant="primary" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-medium py-3 rounded shadow-sm shadow-indigo-600/30 transition-all duration-200" data-test="register-user-button">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="mt-6 text-sm text-white/60">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate class="text-indigo-300 hover:text-indigo-200 font-medium ml-1">{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::minios.auth>
