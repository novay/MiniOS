<x-layouts::minios.auth :title="__('Email verification')">
    <div class="flex w-full flex-col items-center text-center">
        {{-- Envelope Avatar Icon --}}
        <div class="relative flex size-24 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-indigo-600 shadow-2xl ring-4 ring-white/10">
            <span class="text-3xl font-bold uppercase tracking-wider text-white">
                <flux:icon name="envelope" class="size-12 text-white" />
            </span>
        </div>

        {{-- Title & Subtitle --}}
        <h2 class="mt-4 text-xl font-bold tracking-tight text-white sm:text-2xl">
            {{ __('Verify Email') }}
        </h2>
        <p class="mt-1 text-xs text-white/60">
            {{ __('Please verify your email address by clicking on the link we just emailed to you.') }}
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="mt-4 p-3 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-xs text-center font-medium">
                {{ __('A new verification link has been sent to your email address.') }}
            </div>
        @endif

        <div class="mt-6 flex flex-col items-center justify-between gap-3 w-full">
            <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                @csrf
                <flux:button type="submit" variant="primary" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-medium py-3 rounded-xl shadow-lg shadow-indigo-600/30 transition-all duration-200">
                    {{ __('Resend verification email') }}
                </flux:button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="w-full text-xs text-white/60 hover:text-white py-1 transition-colors cursor-pointer" data-test="logout-button">
                    {{ __('Log out') }}
                </button>
            </form>
        </div>
    </div>
</x-layouts::minios.auth>
