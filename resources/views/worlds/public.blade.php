@php
    $dimColors = ['overworld' => '#4a8c3f', 'nether' => '#b3312c', 'end' => '#7b6ca8'];
    $withCoords = $waypoints->filter(fn ($w) => $w->hasCoords());
    $dominant = $waypoints->groupBy('dimension')->sortByDesc(fn ($g) => $g->count())->keys()->first() ?? 'overworld';
    $accent = $dimColors[$dominant] ?? '#4a8c3f';
    $dimensions = $waypoints->pluck('dimension')->unique()->values();
@endphp

<x-layouts.public :title="$world->name">
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-zinc-200 dark:border-zinc-800">
        <div class="pointer-events-none absolute inset-0" style="background: linear-gradient(to bottom, {{ $accent }}22, transparent);"></div>
        <div class="relative mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <a href="{{ route('home') }}" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 dark:hover:text-white">← {{ __('Explore') }}</a>

            <div class="mt-4 flex flex-wrap items-end justify-between gap-6">
                <div class="max-w-2xl">
                    <div class="flex items-center gap-2">
                        <span class="inline-block h-3 w-3 rounded-full" style="background: {{ $accent }}"></span>
                        <span class="text-sm font-semibold uppercase tracking-wide text-zinc-500">{{ ucfirst($dominant) }}</span>
                    </div>
                    <h1 class="mt-2 text-4xl font-black tracking-tight sm:text-5xl">{{ $world->name }}</h1>
                    <p class="mt-2 text-zinc-500">{{ __('by') }} {{ $world->user->name }}</p>
                    @if ($world->description)
                        <p class="mt-4 text-lg text-zinc-600 dark:text-zinc-400">{{ $world->description }}</p>
                    @endif
                </div>

                {{-- Seed chip --}}
                @if ($world->seed)
                    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900" x-data="{ copied: false }">
                        <div class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Bedrock seed') }}</div>
                        <div class="mt-1 flex items-center gap-3">
                            <span x-ref="seed" class="font-mono text-lg font-bold">{{ $world->seed }}</span>
                            <button
                                type="button"
                                class="rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-semibold transition hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700"
                                x-on:click="navigator.clipboard.writeText($refs.seed.textContent.trim()); copied = true; setTimeout(() => copied = false, 1500)"
                                x-text="copied ? '{{ __('Copied!') }}' : '{{ __('Copy') }}'"
                            >{{ __('Copy') }}</button>
                        </div>
                        <p class="mt-1 text-xs text-zinc-500">{{ __('Enter this when creating a world to reproduce it.') }}</p>
                    </div>
                @endif
            </div>

            {{-- Stat tiles --}}
            <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="text-2xl font-bold">{{ $waypoints->count() }}</div>
                    <div class="text-sm text-zinc-500">{{ __('waypoints') }}</div>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="text-2xl font-bold">{{ $dimensions->count() }}</div>
                    <div class="text-sm text-zinc-500">{{ trans_choice('dimension|dimensions', $dimensions->count()) }}</div>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="font-mono text-2xl font-bold">{{ $withCoords->isNotEmpty() ? $withCoords->min('x').' … '.$withCoords->max('x') : '—' }}</div>
                    <div class="text-sm text-zinc-500">{{ __('X span') }}</div>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="font-mono text-2xl font-bold">{{ $withCoords->isNotEmpty() ? $withCoords->min('z').' … '.$withCoords->max('z') : '—' }}</div>
                    <div class="text-sm text-zinc-500">{{ __('Z span') }}</div>
                </div>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl space-y-10 px-4 py-12 sm:px-6">
        {{-- Map --}}
        @if (! empty($markers))
            <section>
                <h2 class="mb-4 text-2xl font-bold tracking-tight">{{ __('Map') }}</h2>
                <x-waypoint-map :markers="$markers" class="h-[60vh] min-h-[420px]" />
                <div class="mt-4 flex flex-wrap gap-4 text-sm text-zinc-500">
                    @foreach (['overworld' => __('Overworld'), 'nether' => __('Nether'), 'end' => __('End')] as $dim => $label)
                        <span class="flex items-center gap-2"><span class="inline-block h-3 w-3 rounded" style="background: {{ $dimColors[$dim] }}"></span> {{ $label }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Gallery --}}
        <section>
            <h2 class="mb-4 text-2xl font-bold tracking-tight">{{ __('Waypoints') }}</h2>
            @if ($waypoints->isEmpty())
                <div class="rounded-xl border border-dashed border-zinc-300 py-16 text-center text-zinc-500 dark:border-zinc-700">
                    {{ __('This world has no waypoints yet.') }}
                </div>
            @else
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($waypoints as $wp)
                        <div class="flex flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                            @if ($wp->screenshot_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($wp->screenshot_path) }}" alt="" class="aspect-video w-full object-cover" />
                            @endif
                            <div class="flex flex-1 flex-col gap-2 p-5">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-bold">{{ $wp->name ?: __('Unnamed waypoint') }}</h3>
                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold text-white" style="background: {{ $wp->dimensionColor() }}">{{ ucfirst($wp->dimension) }}</span>
                                </div>
                                <div class="font-mono text-sm text-zinc-600 dark:text-zinc-400">{{ $wp->coordString() }}</div>
                                @if ($wp->note)
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $wp->note }}</p>
                                @endif
                                @if (! empty($wp->tags))
                                    <div class="mt-1 flex flex-wrap gap-1.5">
                                        @foreach ($wp->tags as $tag)
                                            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-layouts.public>
