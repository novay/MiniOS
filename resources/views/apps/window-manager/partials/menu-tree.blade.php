@foreach($nodes as $node)
    <div class="min-w-0" wire:key="wm-tree-{{ $node['id'] ?? \Illuminate\Support\Str::slug($node['label']).'-'.$depth }}">
        <div class="flex items-center gap-2 rounded-lg px-2 py-1.5 {{ ($node['enabled'] ?? true) ? 'bg-neutral-50 dark:bg-white/5' : 'bg-neutral-50/50 opacity-50 dark:bg-white/[0.02]' }}" style="margin-left: {{ ($depth - 1) * 15 }}px">
            <span class="shrink-0 text-neutral-400">{{ $depth === 1 ? '▤' : '↳' }}</span>
            <span class="min-w-0 flex-1 truncate text-xs font-medium">{{ ($node['action_type'] ?? null) === 'separator' ? '────────' : $node['label'] }}</span>
            <span class="truncate text-[10px] text-neutral-500">{{ $node['action_type'] ?? 'group' }}</span>
            @if($customized)
                @if(($node['action_type'] ?? null) === null && $depth < 3)
                    <button type="button" wire:click="add({{ (int) $node['id'] }})" title="Add child" class="rounded px-1 text-indigo-600 hover:bg-indigo-500/10">+</button>
                @endif
                <button type="button" wire:click="edit({{ (int) $node['id'] }})" title="Edit" class="rounded px-1 hover:bg-neutral-200 dark:hover:bg-white/10">✎</button>
                <button type="button" wire:click="move({{ (int) $node['id'] }}, -1)" title="Move up" class="rounded px-1 hover:bg-neutral-200 dark:hover:bg-white/10">↑</button>
                <button type="button" wire:click="move({{ (int) $node['id'] }}, 1)" title="Move down" class="rounded px-1 hover:bg-neutral-200 dark:hover:bg-white/10">↓</button>
                <button type="button" wire:click="deleteMenu({{ (int) $node['id'] }})" wire:confirm="Hapus menu berikut submenu-nya?" title="Delete" class="rounded px-1 text-red-500 hover:bg-red-500/10">×</button>
            @endif
        </div>
        @if(!empty($node['children']))
            @include('minios::apps.window-manager.partials.menu-tree', ['nodes' => $node['children'], 'depth' => $depth + 1, 'customized' => $customized])
        @endif
    </div>
@endforeach
