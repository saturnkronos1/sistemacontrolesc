@props(['images' => []])

<div
    x-data="{
        current: 0,
        interval: null,
        get total() { return @js($images).length },
        next() { this.current = (this.current + 1) % this.total },
        prev() { this.current = (this.current - 1 + this.total) % this.total },
        go(i) { this.current = i },
        start() { this.interval = setInterval(() => this.next(), 5000) },
        stop() { clearInterval(this.interval); this.interval = null },
    }"
    x-init="start()"
    @mouseenter="stop()"
    @mouseleave="start()"
    class="relative h-full w-full overflow-hidden"
    role="region"
    aria-label="Carrusel de imágenes"
>
    {{-- Slides — todos renderizados, solo el activo visible con transición --}}
    <template x-for="(image, i) in @js($images)" :key="i">
        <div
            class="absolute inset-0 transition-opacity duration-700"
            :class="current === i ? 'opacity-100 z-10' : 'opacity-0 z-0'"
        >
            <img
                :src="'{{ asset('') }}' + image"
                :alt="'Slide ' + (i + 1)"
                class="h-full w-full object-cover"
            />
        </div>
    </template>

    {{-- Prev button --}}
    <button
        @click="prev()"
        class="absolute left-3 top-1/2 -translate-y-1/2 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-black/30 text-white backdrop-blur-sm transition-colors hover:bg-black/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
        aria-label="Anterior"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
        </svg>
    </button>

    {{-- Next button --}}
    <button
        @click="next()"
        class="absolute right-3 top-1/2 -translate-y-1/2 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-black/30 text-white backdrop-blur-sm transition-colors hover:bg-black/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
        aria-label="Siguiente"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
        </svg>
    </button>

    {{-- Dots --}}
    <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-20 flex gap-2">
        <template x-for="(image, i) in @js($images)" :key="i">
            <button
                @click="go(i)"
                class="h-2.5 rounded-full transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                :class="current === i ? 'w-6 bg-white' : 'w-2.5 bg-white/50 hover:bg-white/70'"
                :aria-label="'Ir al slide ' + (i + 1)"
            ></button>
        </template>
    </div>
</div>
