@php
    $currentRoute = request()->route()?->getName();
@endphp

<div class="space-y-1">
    @foreach($menuGroups as $label => $items)
        @if(count($items))
            <p class="px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white/40">
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
                        'bg-white/20 text-white' => $isActive,
                        'text-white/70 hover:bg-white/10 hover:text-white' => !$isActive,
                    ])
                >
                    <flux:icon name="{{ $item['icon'] }}" class="w-5 h-5 shrink-0" />
                    <span>{{ __($item['label']) }}</span>
                </a>
            @endforeach
        @endif
    @endforeach
</div>
