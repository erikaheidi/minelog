@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'canonical' => null,
    'type' => 'website',
    'imageWidth' => null,
    'imageHeight' => null,
])

@php
    $resolvedTitle = filled($title) ? $title.' - '.config('app.name', 'Minelog') : config('app.name', 'Minelog');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>{{ $resolvedTitle }}</title>

        <x-seo
            :title="$resolvedTitle"
            :description="$description"
            :image="$image"
            :canonical="$canonical"
            :type="$type"
            :imageWidth="$imageWidth"
            :imageHeight="$imageHeight"
        />

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-mine-bg text-mine-text antialiased">
        <header class="sticky top-0 z-20 border-b border-mine-line bg-mine-panel/90 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-6xl items-center gap-6 px-4 sm:px-6">
                <a href="{{ route('home') }}" class="text-lg">
                    <x-minelog-logo :size="22" />
                </a>
                <nav class="ml-auto flex items-center gap-5 text-sm">
                    <a href="{{ route('home') }}" class="font-semibold text-mine-muted transition hover:text-mine-text">{{ __('Explore') }}</a>
                    <a href="{{ route('how-it-works') }}" class="font-semibold text-mine-muted transition hover:text-mine-text">{{ __('How it Works') }}</a>
                    @auth
                        <a href="{{ route('worlds.index') }}" class="rounded-lg bg-mine-panel-2 px-4 py-1.5 font-semibold text-mine-text ring-1 ring-mine-line transition hover:ring-mine-green-2">{{ __('My Worlds') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-mine-muted transition hover:text-mine-text">{{ __('Log in') }}</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-lg bg-mine-green px-4 py-1.5 font-bold text-white transition hover:bg-mine-green-2">{{ __('Get started') }}</a>
                        @endif
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="mt-16 border-t border-mine-line py-10 text-center text-sm text-mine-muted">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <p>{{ __('Minelog — a travel log for your Minecraft worlds.') }}</p>
                <nav class="mt-3 flex flex-wrap items-center justify-center gap-x-5 gap-y-2">
                    <a href="{{ route('how-it-works') }}" class="font-semibold transition hover:text-mine-text">{{ __('How it Works') }}</a>
                    <a href="{{ route('privacy-policy') }}" class="font-semibold transition hover:text-mine-text">{{ __('Privacy Policy') }}</a>
                    <a href="{{ route('terms-of-service') }}" class="font-semibold transition hover:text-mine-text">{{ __('Terms of Service') }}</a>
                </nav>
            </div>
        </footer>

        @include('partials.analytics')

        @fluxScripts
    </body>
</html>
