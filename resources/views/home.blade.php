<x-layouts.public
    :title="__('Explore worlds')"
    :description="__('Explore public Minecraft worlds on Minelog — browse waypoints, seeds and maps players have shared, or map your own.')"
    :canonical="route('home')"
    :imageWidth="1200"
    :imageHeight="630"
>
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-mine-line">
        <div class="pointer-events-none absolute inset-0" style="background: linear-gradient(to bottom, rgba(94,168,79,0.14), transparent);"></div>
        <div class="relative mx-auto max-w-6xl px-4 py-20 text-center sm:px-6 sm:py-28">
            <h1 class="mx-auto max-w-3xl text-4xl font-black tracking-tight sm:text-6xl">
                {{ __('Map every place worth remembering <3') }}
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-mine-muted">
                {{ __('Minelog turns your Minecraft adventures into a shareable travel log.') }}
                {{ __('Save coordinates, add notes and screenshots, then map it all.') }}
                <a href="{{ route('how-it-works') }}" class="font-semibold text-mine-green-2 underline-offset-2 hover:underline">{{ __('See how it works →') }}</a>
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                @auth
                    <a href="{{ route('worlds.index') }}" class="rounded-lg bg-mine-green px-5 py-2.5 font-bold text-white transition hover:bg-mine-green-2">{{ __('Go to my worlds') }}</a>
                @else
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="rounded-lg bg-mine-green px-5 py-2.5 font-bold text-white transition hover:bg-mine-green-2">{{ __('Start your log') }}</a>
                    @endif
                @endauth
                <a href="#explore" class="text-sm font-semibold text-mine-muted transition hover:text-mine-text">{{ __('Explore public worlds ↓') }}</a>
            </div>
        </div>
    </section>

    {{-- Explore --}}
    <section id="explore" class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('Public worlds') }}</h2>
                <p class="mt-1 text-mine-muted">{{ __('Browse worlds shared by the community.') }}</p>
            </div>
            <form method="GET" action="{{ route('home') }}" class="flex items-center gap-2">
                <input
                    type="search"
                    name="q"
                    value="{{ $q }}"
                    placeholder="{{ __('Search worlds…') }}"
                    class="rounded-lg border border-mine-line bg-mine-panel-2 px-3 py-2 text-sm text-mine-text outline-none placeholder:text-mine-muted focus:border-mine-green-2"
                />
                <button type="submit" class="rounded-lg bg-mine-green px-4 py-2 text-sm font-bold text-white transition hover:bg-mine-green-2">{{ __('Search') }}</button>
            </form>
        </div>

        @if ($worlds->isEmpty())
            <div class="mt-10 rounded-xl border border-dashed border-mine-line py-16 text-center text-mine-muted">
                {{ $q !== '' ? __('No worlds match your search.') : __('No public worlds yet. Be the first to share one!') }}
            </div>
        @else
            <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($worlds as $world)
                    @php
                        $dimColors = ['overworld' => '#4a8c3f', 'nether' => '#b3312c', 'end' => '#7b6ca8'];
                        $present = $world->dimensionsPresent();
                        $coverTint = $dimColors[$present[0] ?? 'overworld'] ?? '#4a8c3f';
                    @endphp
                    <a
                        href="{{ route('worlds.public', $world) }}"
                        class="group flex flex-col overflow-hidden rounded-2xl border border-mine-line bg-mine-panel shadow-sm transition hover:-translate-y-0.5 hover:border-mine-green-2 hover:shadow-md"
                    >
                        {{-- Cover screenshot, or a dimension-tinted placeholder --}}
                        @if ($world->coverScreenshot)
                            <div class="aspect-video w-full overflow-hidden">
                                <img src="{{ $world->coverScreenshot->url() }}" alt="" class="h-full w-full object-cover transition group-hover:scale-[1.02]" />
                            </div>
                        @else
                            <div class="flex aspect-video w-full items-center justify-center" style="background: linear-gradient(135deg, {{ $coverTint }}55, #0c0f1266);">
                                <span class="text-4xl font-black text-white/80">{{ Str::upper(Str::substr($world->name, 0, 1)) }}</span>
                            </div>
                        @endif

                        <div class="flex flex-1 flex-col p-5">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="text-lg font-bold tracking-tight group-hover:text-mine-green-2">{{ $world->name }}</h3>
                                @if ($world->seed)
                                    <span class="shrink-0 rounded-md bg-mine-panel-2 px-2 py-1 font-mono text-xs text-mine-muted">{{ __('seed') }}</span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-mine-muted">{{ __('by') }} {{ $world->user->name }}</p>
                            @if ($world->description)
                                <p class="mt-3 line-clamp-2 text-sm text-mine-muted">{{ $world->description }}</p>
                            @endif
                            <div class="mt-4 flex flex-wrap items-center gap-2 pt-1">
                                @foreach ($present as $dim)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-mine-panel-2 px-2.5 py-1 text-xs font-medium">
                                        <span class="h-2 w-2 rounded-full" style="background: {{ $dimColors[$dim] ?? '#4a8c3f' }}"></span>
                                        {{ ucfirst($dim) }}
                                    </span>
                                @endforeach
                                <span class="ml-auto text-sm font-semibold text-mine-muted">{{ $world->waypoints_count }} {{ __('waypoints') }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.public>
