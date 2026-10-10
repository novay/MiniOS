<?php

namespace Novay\MiniOS\Livewire\Apps;

use Livewire\Component;

class Browser extends Component
{
    public string $defaultUrl = 'https://google.com';

    public function getAccentProperty(): array
    {
        $name = os_setting()->get('appearance.accent_color', 'indigo');

        $map = [
            'indigo' => ['name' => 'indigo', 'hex' => '#4f46e5', 'text' => 'text-indigo-600 dark:text-indigo-400', 'bg' => 'bg-indigo-600'],
            'zinc' => ['name' => 'zinc', 'hex' => '#27272a', 'text' => 'text-zinc-600 dark:text-zinc-400', 'bg' => 'bg-zinc-700'],
            'emerald' => ['name' => 'emerald', 'hex' => '#10b981', 'text' => 'text-emerald-600 dark:text-emerald-400', 'bg' => 'bg-emerald-600'],
            'sky' => ['name' => 'sky', 'hex' => '#0ea5e9', 'text' => 'text-sky-600 dark:text-sky-400', 'bg' => 'bg-sky-500'],
            'amber' => ['name' => 'amber', 'hex' => '#f59e0b', 'text' => 'text-amber-600 dark:text-amber-400', 'bg' => 'bg-amber-500'],
            'rose' => ['name' => 'rose', 'hex' => '#f43f5e', 'text' => 'text-rose-600 dark:text-rose-400', 'bg' => 'bg-rose-500'],
        ];

        return $map[$name] ?? $map['indigo'];
    }

    public function render()
    {
        return view('minios::apps.browser.index', [
            'accent' => $this->accent,
        ]);
    }
}
