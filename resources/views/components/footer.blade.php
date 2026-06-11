<footer class="mt-auto px-6 py-4 bg-footer">
    <div class="mx-auto flex max-w-7xl flex-col items-center gap-3 sm:flex-row sm:justify-between">
        {{-- Copyright --}}
        <p class="text-sm text-white/90">
            &copy; <span x-data x-text="new Date().getFullYear()"></span> Sistema de Control Escolar
        </p>

        {{-- Contacto --}}
        <p class="text-sm text-white/90">
            Contacto: <a href="mailto:contacto@sistemacontrolesc.com" class="text-white underline underline-offset-2 hover:text-white/70">contacto@sistemacontrolesc.com</a>
        </p>

        {{-- Redes sociales --}}
        <div class="flex items-center gap-3">
            {{-- Facebook --}}
            <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="text-white/80 hover:text-white transition-colors" aria-label="Facebook">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                </svg>
            </a>

            {{-- X (Twitter) --}}
            <a href="https://x.com" target="_blank" rel="noopener noreferrer" class="text-white/80 hover:text-white transition-colors" aria-label="X (Twitter)">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                </svg>
            </a>

            {{-- Instagram --}}
            <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="text-white/80 hover:text-white transition-colors" aria-label="Instagram">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 016.11 2.525c.636-.247 1.363-.416 2.427-.465C8.88 2.013 9.235 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                </svg>
            </a>

            {{-- WhatsApp --}}
            <a href="https://wa.me/521234567890" target="_blank" rel="noopener noreferrer" class="text-white/80 hover:text-white transition-colors" aria-label="WhatsApp">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.006 2a10 10 0 00-8.64 15.18l-1.34 4.14a.5.5 0 00.63.63l4.2-1.33A10 10 0 1012.006 2zm0 18.28a8.28 8.28 0 01-4.24-1.21l-.3-.18-2.87.91.94-2.87-.2-.33a8.28 8.28 0 1113.13-9.54 8.28 8.28 0 01-6.46 13.22zm4.78-6.22c-.26-.13-1.54-.76-1.78-.85-.24-.09-.42-.13-.6.13-.18.26-.7.85-.86 1.02-.16.17-.32.2-.58.07-.26-.13-1.1-.4-2.1-1.3-.78-.7-1.3-1.56-1.45-1.82-.16-.26-.02-.4.12-.53.13-.12.26-.32.39-.48.13-.16.17-.27.26-.45.09-.18.04-.34-.02-.47-.07-.13-.6-1.45-.82-1.99-.22-.54-.44-.47-.6-.48-.16 0-.34-.01-.52-.01-.18 0-.48.07-.73.34-.25.27-.96.94-.96 2.29 0 1.35.99 2.66 1.13 2.84.14.18 2.01 3.19 4.84 4.36.68.28 1.2.44 1.62.57.68.22 1.3.19 1.79.12.55-.08 1.7-.7 1.94-1.37.24-.67.24-1.24.17-1.36-.07-.13-.26-.2-.52-.33z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </div>
</footer>
