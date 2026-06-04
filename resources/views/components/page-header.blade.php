@props(['title'])

<div class="hidden lg:flex items-center justify-between border-b border-neutral-200 pb-4 mb-6 dark:border-neutral-700">
    <h1 class="text-2xl font-bold">{{ $title }}</h1>

    <flux:dropdown position="bottom" align="end">
        <button type="button" class="flex items-center gap-2 rounded-full p-1 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
            @if (auth()->user()->profilePhotoUrl())
                <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="{{ auth()->user()->name }}" class="h-9 w-9 rounded-full object-cover">
            @else
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-300 dark:bg-zinc-600 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                    {{ auth()->user()->initials() }}
                </span>
            @endif
            <flux:icon name="chevrons-up-down" variant="micro" class="size-4 text-zinc-400" />
        </button>

        <flux:menu>
            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                @if (auth()->user()->profilePhotoUrl())
                    <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="" class="h-8 w-8 rounded-full object-cover">
                @else
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-200 dark:bg-zinc-600 text-xs font-semibold text-zinc-600 dark:text-zinc-300">
                        {{ auth()->user()->initials() }}
                    </span>
                @endif
                <div class="grid flex-1 text-start text-sm leading-tight">
                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                </div>
            </div>
            <flux:menu.separator />
            <flux:menu.radio.group>
                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                    {{ __('Settings') }}
                </flux:menu.item>
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item
                        as="button"
                        type="submit"
                        icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer"
                    >
                        {{ __('Log out') }}
                    </flux:menu.item>
                </form>
            </flux:menu.radio.group>
        </flux:menu>
    </flux:dropdown>
</div>
