@php
    $dimColors = ['overworld' => '#4a8c3f', 'nether' => '#b3312c', 'end' => '#7b6ca8'];
@endphp

<x-layouts.public :title="$world->name.' · '.__('Map')">
    <section class="border-b border-mine-line">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            <a href="{{ route('home') }}" class="text-sm font-semibold text-mine-muted transition hover:text-mine-text">← {{ __('Explore') }}</a>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black tracking-tight">{{ $world->name }}</h1>
                    <p class="mt-1 text-mine-muted">{{ __('by') }} {{ $world->user->name }}</p>
                </div>
                <x-world-tabs :world="$world" active="map" :public="true" />
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        @if (empty($markers))
            <div class="rounded-xl border border-dashed border-mine-line py-16 text-center text-mine-muted">
                {{ __('This world has no waypoints with coordinates yet.') }}
            </div>
        @else
            <x-waypoint-map :markers="$markers" class="h-[70vh] min-h-[440px]" />
            <div class="mt-4 flex flex-wrap gap-4 text-sm text-mine-muted">
                @foreach (['overworld' => __('Overworld'), 'nether' => __('Nether'), 'end' => __('End')] as $dim => $label)
                    <span class="flex items-center gap-2"><span class="inline-block h-3 w-3 rounded" style="background: {{ $dimColors[$dim] }}"></span> {{ $label }}</span>
                @endforeach
                <span class="ml-auto">{{ count($markers) }} {{ __('waypoint(s) with coordinates') }}</span>
            </div>
        @endif
    </div>
</x-layouts.public>
