@php
    $currentRoute = request()->route()?->getName();
@endphp

<div class="space-y-1">
    @foreach($menuGroups as $label => $items)
        @if(count($items))
            <p class="px-3 py-1 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                {{ __($label) }}
            </p>
            @foreach($items as $item)
                @php
                    $isActive = $currentRoute && str_starts_with($currentRoute, $item['route_prefix']);
                @endphp
                <a
                    href="{{ route($item['route'], $item['params'] ?? []) }}"
                    wire:navigate
                    @class([
                        'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                        'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-white' => $isActive,
                        'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700/50 hover:text-zinc-900 dark:hover:text-white' => !$isActive,
                    ])
                >
                    {!! $item['svg'] !!}
                    <span>{{ __($item['label']) }}</span>
                </a>
            @endforeach
        @endif
    @endforeach
</div>
