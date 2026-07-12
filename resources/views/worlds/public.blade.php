@php
    $dimColors = ['overworld' => '#4a8c3f', 'nether' => '#b3312c', 'end' => '#7b6ca8'];
    $withCoords = $waypoints->filter(fn ($w) => $w->hasCoords());
    $dominant = $waypoints->groupBy('dimension')->sortByDesc(fn ($g) => $g->count())->keys()->first() ?? 'overworld';
    $accent = $dimColors[$dominant] ?? '#4a8c3f';
    $dimensions = $waypoints->pluck('dimension')->unique()->values();
@endphp

<x-layouts.public
    :title="$world->name"
    :description="$world->description ?: __('Explore :name, a Minecraft world by :author — :count waypoints mapped on Minelog.', ['name' => $world->name, 'author' => $world->user->name, 'count' => $waypoints->count()])"
    :image="$world->coverScreenshot?->url()"
    :canonical="route('worlds.public', $world)"
    type="article"
>
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-mine-line">
        <div class="pointer-events-none absolute inset-0" style="background: linear-gradient(to bottom, {{ $accent }}33, transparent);"></div>
        <div class="relative mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <a href="{{ route('home') }}" class="text-sm font-semibold text-mine-muted transition hover:text-mine-text">← {{ __('Explore') }}</a>

            <div class="mt-4 flex flex-wrap items-end justify-between gap-6">
                <div class="max-w-2xl">
                    <div class="flex items-center gap-2">
                        <span class="inline-block h-3 w-3 rounded-full" style="background: {{ $accent }}"></span>
                        <span class="text-sm font-semibold uppercase tracking-wide text-mine-muted">{{ ucfirst($dominant) }}</span>
                    </div>
                    <h1 class="mt-2 text-4xl font-black tracking-tight sm:text-5xl">{{ $world->name }}</h1>
                    <p class="mt-2 text-mine-muted">{{ __('by') }} {{ $world->user->name }}</p>
                    @if ($world->description)
                        <p class="mt-4 text-lg text-mine-muted">{{ $world->description }}</p>
                    @endif
                </div>

                {{-- Seed chip --}}
                @if ($world->seed)
                    <div class="rounded-2xl border border-mine-line bg-mine-panel p-4 shadow-sm" x-data="{ copied: false }">
                        <div class="text-xs font-semibold uppercase tracking-wide text-mine-muted">{{ __('Bedrock seed') }}</div>
                        <div class="mt-1 flex items-center gap-3">
                            <span x-ref="seed" class="font-mono text-lg font-bold">{{ $world->seed }}</span>
                            <button
                                type="button"
                                class="rounded-lg bg-mine-panel-2 px-2.5 py-1 text-xs font-semibold text-mine-text transition hover:bg-mine-line"
                                x-on:click="navigator.clipboard.writeText($refs.seed.textContent.trim()); copied = true; setTimeout(() => copied = false, 1500)"
                                x-text="copied ? '{{ __('Copied!') }}' : '{{ __('Copy') }}'"
                            >{{ __('Copy') }}</button>
                        </div>
                        <p class="mt-1 text-xs text-mine-muted">{{ __('Enter this when creating a world to reproduce it.') }}</p>
                    </div>
                @endif
            </div>

            {{-- Stat tiles --}}
            <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ([
                    [$waypoints->count(), __('waypoints')],
                    [$dimensions->count(), trans_choice('dimension|dimensions', $dimensions->count())],
                    [$withCoords->isNotEmpty() ? $withCoords->min('x').' … '.$withCoords->max('x') : '—', __('X span')],
                    [$withCoords->isNotEmpty() ? $withCoords->min('z').' … '.$withCoords->max('z') : '—', __('Z span')],
                ] as [$value, $label])
                    <div class="rounded-xl border border-mine-line bg-mine-panel p-4">
                        <div class="font-mono text-2xl font-bold">{{ $value }}</div>
                        <div class="text-sm text-mine-muted">{{ $label }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl space-y-8 px-4 py-12 sm:px-6">
        <x-world-tabs :world="$world" active="log" :public="true" />

        {{-- Gallery --}}
        <section x-data="{ open: false, wp: null }" @keydown.escape.window="open = false">
            <h2 class="mb-4 text-2xl font-bold tracking-tight">{{ __('Waypoints') }}</h2>
            @if ($waypoints->isEmpty())
                <div class="rounded-xl border border-dashed border-mine-line py-16 text-center text-mine-muted">
                    {{ __('This world has no waypoints yet.') }}
                </div>
            @else
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($waypoints as $wp)
                        @php
                            $wpData = [
                                'name' => $wp->name ?: __('Unnamed waypoint'),
                                'coords' => $wp->coordString(),
                                'dimension' => ucfirst($wp->dimension),
                                'color' => $wp->dimensionColor(),
                                'note' => $wp->note,
                                'tags' => $wp->tags ?? [],
                                'shots' => $wp->screenshots->map(fn ($s) => $s->url())->all(),
                            ];
                        @endphp
                        <div
                            role="button"
                            tabindex="0"
                            @click="wp = @js($wpData); open = true"
                            @keydown.enter="wp = @js($wpData); open = true"
                            @keydown.space.prevent="wp = @js($wpData); open = true"
                            class="flex cursor-pointer flex-col overflow-hidden rounded-2xl border border-mine-line bg-mine-panel shadow-sm transition hover:border-mine-muted hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-mine-muted"
                        >
                            {{-- Screenshot, or a dimension-tinted placeholder header --}}
                            @if ($wp->screenshots->isNotEmpty())
                                <div class="relative aspect-video w-full">
                                    <img src="{{ $wp->screenshots->first()->url() }}" alt="" class="h-full w-full object-cover" />
                                    <span class="absolute left-3 top-3 rounded-full px-2 py-0.5 text-xs font-semibold text-white" style="background: {{ $wp->dimensionColor() }}">{{ ucfirst($wp->dimension) }}</span>
                                    @if ($wp->screenshots->count() > 1)
                                        <span class="absolute right-3 top-3 rounded-full bg-black/60 px-2 py-0.5 text-xs font-semibold text-white">{{ $wp->screenshots->count() }} {{ __('shots') }}</span>
                                    @endif
                                </div>
                            @else
                                <div class="relative flex aspect-video w-full items-center justify-center" style="background: linear-gradient(135deg, {{ $wp->dimensionColor() }}55, #0c0f1266);">
                                    <span class="font-mono text-sm text-white/80">{{ $wp->coordString() }}</span>
                                    <span class="absolute left-3 top-3 rounded-full px-2 py-0.5 text-xs font-semibold text-white" style="background: {{ $wp->dimensionColor() }}">{{ ucfirst($wp->dimension) }}</span>
                                </div>
                            @endif
                            <div class="flex flex-1 flex-col gap-2 p-5">
                                <h3 class="font-bold">{{ $wp->name ?: __('Unnamed waypoint') }}</h3>
                                <div class="font-mono text-sm text-mine-muted">{{ $wp->coordString() }}</div>
                                @if ($wp->note)
                                    <p class="text-sm text-mine-muted">{{ Str::limit($wp->note, 100) }}</p>
                                @endif
                                @if (! empty($wp->tags))
                                    <div class="mt-1 flex flex-wrap gap-1.5">
                                        @foreach ($wp->tags as $tag)
                                            <span class="rounded-full bg-mine-panel-2 px-2 py-0.5 text-xs text-mine-muted">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Waypoint detail modal --}}
            <div
                x-show="open"
                x-cloak
                @click.self="open = false"
                class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/70 p-4 backdrop-blur-sm sm:items-center"
                style="display: none;"
            >
                <template x-if="wp">
                    <div class="relative my-8 w-full max-w-2xl overflow-hidden rounded-2xl border border-mine-line bg-mine-panel shadow-xl" @click.stop>
                        <button
                            type="button"
                            @click="open = false"
                            class="absolute right-4 top-4 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-black/50 text-lg text-white transition hover:bg-black/70"
                            aria-label="{{ __('Close') }}"
                        >&times;</button>

                        <div class="max-h-[85vh] overflow-y-auto">
                            {{-- Header --}}
                            <div class="flex flex-col gap-3 p-6">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold text-white" :style="'background: ' + wp.color" x-text="wp.dimension"></span>
                                    <span class="font-mono text-sm text-mine-muted" x-text="wp.coords"></span>
                                </div>
                                <h3 class="text-2xl font-bold tracking-tight" x-text="wp.name"></h3>
                                <p class="text-mine-muted" x-show="wp.note" x-text="wp.note"></p>
                                <div class="flex flex-wrap gap-1.5" x-show="wp.tags.length">
                                    <template x-for="tag in wp.tags" :key="tag">
                                        <span class="rounded-full bg-mine-panel-2 px-2 py-0.5 text-xs text-mine-muted" x-text="tag"></span>
                                    </template>
                                </div>
                            </div>

                            {{-- Screenshots --}}
                            <div class="flex flex-col gap-3 px-6 pb-6" x-show="wp.shots.length">
                                <h4 class="text-sm font-semibold uppercase tracking-wide text-mine-muted">
                                    {{ __('Screenshots') }} (<span x-text="wp.shots.length"></span>)
                                </h4>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <template x-for="(url, i) in wp.shots" :key="i">
                                        <a :href="url" target="_blank" rel="noopener" class="block overflow-hidden rounded-xl border border-mine-line">
                                            <img :src="url" alt="" class="aspect-video w-full object-cover transition hover:scale-[1.02]" />
                                        </a>
                                    </template>
                                </div>
                            </div>

                            <div class="px-6 pb-6 text-sm text-mine-muted" x-show="! wp.shots.length">
                                {{ __('No screenshots for this waypoint yet.') }}
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </section>
    </div>
</x-layouts.public>
