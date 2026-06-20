<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php $title = __('Welcome'); @endphp
        @include('partials.head')
    </head>
    <body class="bg-[#ecece5] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center min-h-screen flex-col">
        <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden border-b-2 border-[#185c7a] pb-4">
            
            @if (Route::has('login'))
                <nav class="flex items-center justify-between gap-4">
                    <h1 class="font-bold text-lg">Sistema de Control Escolar</h1>
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
                        >
                            Dashboard
                        </a>
                    @else
                        <flux:modal.trigger name="login-modal">
                            <flux:button variant="ghost" class="hover:#14532D px-5 py-1.5! text-sm! leading-normal! rounded-sm!">
                                Iniciar sesión
                            </flux:button>
                               
                        </flux:modal.trigger>

                        @if (Route::has('register'))
                            {{-- <a
                                href="{{ route('register') }}"
                                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">
                                Registrar
                            </a> --}}
                        @endif
                    @endauth
                </nav>
            @endif
        </header>
        <div class="flex flex-1 items-center justify-center w-full transition-opacity opacity-100 duration-750 starting:opacity-0">
            <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row">
                <div x-data="{
                    current: 0,
                    images: ['c1','c2','c3','c4','c5','c6','c7','c8','c9'],
                    timer: null,
                    init() {
                        this.timer = setInterval(() => {
                            this.current = (this.current + 1) % this.images.length
                        }, 4000)
                    },
                    destroy() {
                        clearInterval(this.timer)
                    }
                }" class="w-full relative overflow-hidden rounded-xl bg-gray-200 h-64 lg:h-[500px] shadow-xl">
                    <template x-for="(img, i) in images" :key="i">
                        <img :src="`/heroes/carrusel1/${img}.png`"
                             :class="{ 'opacity-100': current === i, 'opacity-0': current !== i }"
                             class="w-full h-64 lg:h-[500px] object-cover absolute inset-0 transition-opacity duration-700"
                             :alt="`Slide ${i + 1}`" />
                    </template>

                    <!-- Dots -->
                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                        <template x-for="(img, i) in images" :key="i">
                            <button @click="current = i"
                                    :class="current === i ? 'bg-white' : 'bg-white/50'"
                                    class="w-2.5 h-2.5 rounded-full transition-all duration-300"></button>
                        </template>
                    </div>

                    <!-- Flecha izquierda -->
                    <button @click="current = (current - 1 + images.length) % images.length"
                            class="absolute left-2 top-1/2 -translate-y-1/2 z-10 bg-black/30 hover:bg-black/50 text-white p-2 rounded-full transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    <!-- Flecha derecha -->
                    <button @click="current = (current + 1) % images.length"
                            class="absolute right-2 top-1/2 -translate-y-1/2 z-10 bg-black/30 hover:bg-black/50 text-white p-2 rounded-full transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </main>
        </div>

        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif

        <flux:modal name="login-modal" :show="$errors->has('email')" focusable class="max-w-lg md:w-[512px]">
            <div class="flex flex-col gap-6">
                {{-- School icon --}}
                <div class="flex justify-center">
                    <img src="{{ asset('heroes/icon/ico1.png') }}" alt="Icono escolar" class="size-11 rounded-full object-cover">
                </div>

                <x-auth-header :title="__('Inicio de sesión')" :description="__('Ingresa tu correo y contraseña')" />

                <x-auth-session-status class="text-center" :status="session('status')" />

                <x-passkey-verify />

                <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
                    @csrf

                    <flux:input
                        name="email"
                        :label="__('Correo electronico:')"
                        :value="old('email')"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="email@example.com"
                    />

                    <div class="relative">
                        <flux:input
                            name="password"
                            :label="__('Contraseña:')"
                            type="password"
                            required
                            autocomplete="current-password"
                            :placeholder="__('*******')"
                            viewable
                        />

                        @if (Route::has('password.request'))
                            {{-- <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                                {{ __('Olvidaste tu contraseña?') }}
                            </flux:link> --}}
                        @endif
                    </div>

                    <flux:checkbox name="remember" :label="__('Recordar')" :checked="old('remember')" />

                    <div class="flex items-center justify-end">
                        <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                            {{ __('Iniciar sesión') }}
                        </flux:button>
                    </div>
                </form>

                {{-- <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                    <span>{{ __("No tienes cuenta?") }}</span>
                    <flux:link :href="route('register')" wire:navigate>{{ __('Registrase') }}</flux:link>
                </div> --}}
            </div>
        </flux:modal>

        

        @fluxScripts
    </body>
    <x-footer />
</html>
