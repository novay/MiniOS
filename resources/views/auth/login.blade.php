<x-layouts::minios.auth :title="__('Log in')">
    <div class="flex w-full flex-col items-center text-center">
        
        {{-- User / Security Avatar --}}
        <div class="relative flex size-28 items-center justify-center rounded-full shadow-2xl ring-3 ring-white/10 mb-6">
            <img src="{{ asset('minios/images/logo.png') }}" alt="Logo MiniOS">
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mt-3 text-center text-xs font-medium text-emerald-400" :status="session('status')" />

        <x-passkey-verify />

        {{-- Login Form --}}
        <form method="POST" action="{{ route('login.store') }}" class="w-full space-y-4 text-left">
            @csrf

            <!-- Email Address -->
            <div class="flex flex-col space-y-1">
                <flux:input
                    name="email"
                    :value="old('email')"
                    type="email"
                    autocomplete="email"
                    :placeholder="__('Email address')"
                />
                <x-minios::error name="email" />
            </div>

            <!-- Password -->
            <div class="flex flex-col space-y-1">
                <flux:input
                    name="password"
                    :placeholder="__('Password')"
                    type="password"
                    autocomplete="current-password"
                    viewable
                />
                <x-minios::error name="password" />
            </div>

            <div class="relative flex items-center justify-between">
                <!-- Remember Me -->
                <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

                @if(Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm inset-e-0 text-indigo-300 hover:text-indigo-200" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot password?') }}
                    </flux:link>
                @endif
            </div>
                
            <flux:button variant="primary" type="submit" class="mt-2 w-full" data-test="login-button">
                {{ __('Log in') }}
            </flux:button>
        </form>


        <div class="mt-6 text-white/60 text-sm">
            <span>{{ __('Don\'t have an account?') }}</span>
            <flux:link :href="route('register')" wire:navigate class="text-indigo-300 hover:text-indigo-200 font-medium ml-1">{{ __('Sign up') }}</flux:link>
        </div>
    </div>
</x-layouts::minios.auth>
