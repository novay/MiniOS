<?php

namespace Novay\MiniOS\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class LockScreen extends Component
{
    public string $password = '';

    public function mount(): void
    {
        session(['desktop_locked' => true]);
    }

    public function unlock()
    {
        $this->validate([
            'password' => 'required|string',
        ], [
            'password.required' => 'Masukkan kata sandi akun Anda.',
        ]);

        $user = Auth::user();

        if (! $user || ! Hash::check($this->password, $user->password)) {
            $this->addError('password', 'Kata sandi yang Anda masukkan salah.');

            return;
        }

        $this->password = '';
        $this->resetErrorBag();
        session()->forget('desktop_locked');

        return $this->redirect('/', navigate: true);
    }

    public function logout()
    {
        Auth::guard('web')->logout();

        session()->invalidate();
        session()->regenerateToken();

        return $this->redirect('/login', navigate: true);
    }

    public function render()
    {
        $view = view()->exists('pages.minios.lock-screen')
            ? 'pages.minios.lock-screen'
            : 'minios::lock-screen';

        $layout = view()->exists('layouts.minios.app')
            ? 'layouts.minios.app'
            : 'minios::layouts.app';

        return view($view)
            ->layout($layout);
    }
}
