@props(['world', 'active' => 'log', 'public' => false])

@php
    $logUrl = $public ? route('worlds.public', $world) : route('worlds.show', $world);
    $mapUrl = $public ? route('worlds.public.map', $world) : route('worlds.map', $world);
    $base = 'rounded-md px-4 py-1.5 font-semibold transition';
    $on = 'bg-mine-green text-white';
    $off = 'text-mine-muted hover:text-mine-text';
@endphp

<nav class="inline-flex gap-1 rounded-lg border border-mine-line bg-mine-panel p-1 text-sm">
    <a href="{{ $logUrl }}" @unless($public) wire:navigate @endunless class="{{ $base }} {{ $active === 'log' ? $on : $off }}">{{ __('Log') }}</a>
    <a href="{{ $mapUrl }}" @unless($public) wire:navigate @endunless class="{{ $base }} {{ $active === 'map' ? $on : $off }}">{{ __('Map') }}</a>
</nav>
