<x-layouts::app.sidebar>
    <div class="flex flex-col items-center justify-center py-20 text-center">
        <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-6 mb-6">
            <svg class="w-12 h-12 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-zinc-700 dark:text-zinc-300">{{ $module ?? 'Módulo' }}</h1>
        <p class="mt-2 text-zinc-500 dark:text-zinc-400">Esta sección está en construcción.</p>
    </div>
</x-layouts::app.sidebar>
