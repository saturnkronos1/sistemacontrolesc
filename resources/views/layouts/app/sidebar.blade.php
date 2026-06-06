<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
    <div
        x-data="layout()"
        x-init="init()"
        class="flex min-h-screen"
    >
        {{-- ============================================ --}}
        {{-- SIDEBAR (Desktop: fixed, Mobile: overlay)    --}}
        {{-- ============================================ --}}
        
        <aside
            :style="sidebarOpen ? 'translate: none;' : ''"
            class="flex w-64 min-h-0 flex-col border-e border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800 transition-transform duration-200 ease-in-out max-lg:fixed max-lg:inset-y-0 max-lg:left-0 max-lg:z-50 max-lg:-translate-x-full"
        >
            {{-- Sidebar Header: Logo + Close (mobile only) --}}
            <div class="flex items-center justify-between px-4 py-3">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2">
                    <x-app-logo-icon class="h-8 w-auto text-accent-content" />
                    <span class="text-lg font-bold hidden sm:inline">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden p-1 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-700">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Role badge --}}
            <div class="shrink-0 px-4 pb-2">
                <span class="inline-flex items-center rounded-full bg-zinc-100 dark:bg-zinc-700 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:text-zinc-300">
                    @auth
                        {{ auth()->user()->roles->first()?->name ?? 'Sin rol' }}
                    @endauth
                </span>
            </div>

            {{-- Navigation (scrollable) --}}
            <nav class="min-h-0 flex-1 overflow-y-auto px-3 py-2 space-y-1">
                <livewire:nav-sidebar />
            </nav>

            {{-- Desktop User Dropdown (lg+) --}}
            
            <div class="shrink-0 hidden lg:block border-t border-zinc-200 dark:border-zinc-700 p-2">
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false" class="flex w-full items-center gap-2 rounded-lg p-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-zinc-200 dark:bg-zinc-600 text-xs font-semibold text-zinc-600 dark:text-zinc-300">
                            {{ auth()->user()?->initials() ?? '?' }}
                        </span>
                        <span class="flex-1 text-left truncate font-medium">{{ auth()->user()?->name ?? 'Usuario' }}</span>
                        <svg class="w-4 h-4 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                    </button>

                    <div x-show="open" x-cloak class="absolute bottom-full left-0 right-0 mb-1 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 shadow-lg overflow-hidden">
                        <div class="px-3 py-2 text-xs text-zinc-500 dark:text-zinc-400 border-b border-zinc-100 dark:border-zinc-700">
                            {{ auth()->user()?->email ?? '' }}
                        </div>
                        <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ __('Settings') }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                </svg>
                                {{ __('Log out') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </aside>

        {{-- Overlay backdrop (mobile only) --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
        ></div>

        {{-- ============================================ --}}
        {{-- MAIN CONTENT AREA                            --}}
        {{-- ============================================ --}}
        <div class="flex min-w-0 flex-1 flex-col min-h-0 overflow-y-auto">
            {{-- Mobile Top Bar (<lg) --}}
            <header class="sticky top-0 z-30 flex items-center gap-3 border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-4 py-2 lg:hidden">
                <button @click="sidebarOpen = true" class="p-1 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-700">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-1">
                    <x-app-logo-icon class="h-7 w-auto text-accent-content" />
                    <span class="text-sm font-bold">{{ config('app.name', 'Laravel') }}</span>
                </a>

                <div class="flex-1"></div>

                {{-- Mobile User Dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-1 p-1 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-700">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-zinc-200 dark:bg-zinc-600 text-xs font-semibold text-zinc-600 dark:text-zinc-300">
                            {{ auth()->user()?->initials() ?? '?' }}
                        </span>
                        <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 top-full mt-1 w-56 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 shadow-lg overflow-hidden">
                        <div class="px-3 py-2 border-b border-zinc-100 dark:border-zinc-700">
                            <p class="text-sm font-medium truncate">{{ auth()->user()?->name ?? 'Usuario' }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ auth()->user()?->email ?? '' }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ __('Settings') }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                </svg>
                                {{ __('Log out') }}
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 p-6 mx-auto w-full max-w-7xl">
                {{ $slot }}
            </main>

            {{-- Footer --}}
            <x-footer />
        </div>
    </div>

    {{-- Toast System (Alpine) --}}
    <div
        x-data="toast()"
        x-init="init()"
        x-cloak
        class="fixed bottom-4 right-4 z-50 flex flex-col gap-2"
    >
        <template x-for="(t, i) in toasts" :key="i">
            <div
                x-show="t.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="translate-x-full opacity-0"
                class="flex items-center gap-3 rounded-lg border px-4 py-3 shadow-lg text-sm"
                :class="{
                    'bg-green-50 border-green-200 text-green-800 dark:bg-green-900/30 dark:border-green-700 dark:text-green-300': t.type === 'success',
                    'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/30 dark:border-red-700 dark:text-red-300': t.type === 'error',
                    'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900/30 dark:border-blue-700 dark:text-blue-300': t.type === 'info',
                }"
            >
                <span x-text="t.message"></span>
                <button @click="remove(i)" class="ml-2 shrink-0 opacity-60 hover:opacity-100">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </template>
    </div>

    @fluxScripts

    <script>
        function layout() {
            return {
                sidebarOpen: false,
                init() {
                    // Close sidebar on route navigation (Livewire)
                    Livewire?.on('$navigate', () => { this.sidebarOpen = false; });
                }
            };
        }

        function toast() {
            return {
                toasts: [],
                init() {
                    window.addEventListener('toast', (e) => {
                        this.toasts.push({
                            message: e.detail.message,
                            type: e.detail.type || 'info',
                            visible: true,
                        });
                        setTimeout(() => {
                            if (this.toasts.length > 0) {
                                const idx = this.toasts.findIndex(t => t.message === e.detail.message);
                                if (idx !== -1) this.toasts[idx].visible = false;
                                setTimeout(() => { this.toasts = this.toasts.filter(t => t.visible); }, 300);
                            }
                        }, e.detail.duration || 4000);
                    });
                },
                remove(index) {
                    this.toasts[index].visible = false;
                    setTimeout(() => { this.toasts = this.toasts.filter(t => t.visible); }, 300);
                }
            };
        }
    </script>
</body>
</html>
