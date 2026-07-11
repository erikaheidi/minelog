<x-layouts.public :title="__('Explore worlds')">
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-zinc-200 dark:border-zinc-800">
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-emerald-500/10 to-transparent"></div>
        <div class="relative mx-auto max-w-6xl px-4 py-20 text-center sm:px-6 sm:py-28">
            <h1 class="mx-auto max-w-3xl text-4xl font-black tracking-tight sm:text-6xl">
                {{ __('Map every place worth remembering.') }}
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-zinc-600 dark:text-zinc-400">
                {{ __('Minelog turns your Minecraft Bedrock adventures into a shareable travel log — exact coordinates, saved in-game, browsed as a gallery and an interactive map.') }}
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                @auth
                    <flux:button variant="primary" :href="route('worlds.index')" wire:navigate>{{ __('Go to my worlds') }}</flux:button>
                @else
                    @if (Route::has('register'))
                        <flux:button variant="primary" :href="route('register')">{{ __('Start your log') }}</flux:button>
                    @endif
                @endauth
                <a href="#explore" class="text-sm font-semibold text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">{{ __('Explore public worlds ↓') }}</a>
            </div>

            {{-- How it works --}}
            <div class="mx-auto mt-16 grid max-w-4xl gap-4 text-left sm:grid-cols-3">
                @foreach ([
                    ['1', __('Save in-game'), __('Type !wp save <label> in Minecraft to record your exact position.')],
                    ['2', __('Export & import'), __('Run !wp export, paste the JSON into Minelog. Coordinates are precise.')],
                    ['3', __('Share'), __('Flip a world to public and share its map and seed with anyone.')],
                ] as [$n, $heading, $body])
                    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/15 font-bold text-emerald-600 dark:text-emerald-400">{{ $n }}</div>
                        <h3 class="mt-3 font-semibold">{{ $heading }}</h3>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Explore --}}
    <section id="explore" class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('Public worlds') }}</h2>
                <p class="mt-1 text-zinc-600 dark:text-zinc-400">{{ __('Browse worlds shared by the community.') }}</p>
            </div>
            <form method="GET" action="{{ route('home') }}" class="flex items-center gap-2">
                <input
                    type="search"
                    name="q"
                    value="{{ $q }}"
                    placeholder="{{ __('Search worlds…') }}"
                    class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-900"
                />
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">{{ __('Search') }}</button>
            </form>
        </div>

        @if ($worlds->isEmpty())
            <div class="mt-10 rounded-xl border border-dashed border-zinc-300 py-16 text-center text-zinc-500 dark:border-zinc-700">
                {{ $q !== '' ? __('No worlds match your search.') : __('No public worlds yet. Be the first to share one!') }}
            </div>
        @else
            <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($worlds as $world)
                    <a
                        href="{{ route('worlds.public', $world) }}"
                        class="group flex flex-col rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-400 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-emerald-500"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-lg font-bold tracking-tight group-hover:text-emerald-600 dark:group-hover:text-emerald-400">{{ $world->name }}</h3>
                            @if ($world->seed)
                                <span class="shrink-0 rounded-md bg-zinc-100 px-2 py-1 font-mono text-xs text-zinc-500 dark:bg-zinc-800">{{ __('seed') }}</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-zinc-500">{{ __('by') }} {{ $world->user->name }}</p>
                        @if ($world->description)
                            <p class="mt-3 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $world->description }}</p>
                        @endif
                        <div class="mt-4 flex flex-wrap items-center gap-2 pt-1">
                            @foreach ($world->dimensionsPresent() as $dim)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium dark:bg-zinc-800">
                                    <span class="h-2 w-2 rounded-full" style="background: {{ ['overworld' => '#4a8c3f', 'nether' => '#b3312c', 'end' => '#7b6ca8'][$dim] ?? '#4a8c3f' }}"></span>
                                    {{ ucfirst($dim) }}
                                </span>
                            @endforeach
                            <span class="ml-auto text-sm font-semibold text-zinc-500">{{ $world->waypoints_count }} {{ __('waypoints') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.public>
