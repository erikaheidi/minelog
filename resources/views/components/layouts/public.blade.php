@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>{{ filled($title) ? $title.' - '.config('app.name', 'Minelog') : config('app.name', 'Minelog') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJ0jv_A3q6AZ7X/3lVN5nMZmYUx4=" crossorigin=""></script>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <header class="sticky top-0 z-20 border-b border-zinc-200/70 bg-zinc-50/80 backdrop-blur dark:border-zinc-800/70 dark:bg-zinc-950/80">
            <div class="mx-auto flex h-16 max-w-6xl items-center gap-6 px-4 sm:px-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-bold tracking-tight">
                    <span class="inline-block h-6 w-6 rotate-3 rounded-[5px] border-2 border-emerald-900/40 bg-gradient-to-br from-emerald-400 to-emerald-600"></span>
                    <span class="text-lg">Minelog</span>
                </a>
                <nav class="ml-auto flex items-center gap-4 text-sm">
                    <a href="{{ route('home') }}" class="font-medium text-zinc-500 transition hover:text-zinc-900 dark:hover:text-white">{{ __('Explore') }}</a>
                    @auth
                        <flux:button size="sm" :href="route('worlds.index')" wire:navigate>{{ __('My Worlds') }}</flux:button>
                    @else
                        <a href="{{ route('login') }}" class="font-medium text-zinc-500 transition hover:text-zinc-900 dark:hover:text-white">{{ __('Log in') }}</a>
                        @if (Route::has('register'))
                            <flux:button size="sm" variant="primary" :href="route('register')">{{ __('Get started') }}</flux:button>
                        @endif
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="mt-16 border-t border-zinc-200 py-10 text-center text-sm text-zinc-500 dark:border-zinc-800">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                {{ __('Minelog — a travel log for your Minecraft worlds.') }}
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
