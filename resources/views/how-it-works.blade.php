<x-layouts.public :title="__('How it Works')">
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-mine-line">
        <div class="pointer-events-none absolute inset-0" style="background: linear-gradient(to bottom, rgba(94,168,79,0.14), transparent);"></div>
        <div class="relative mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 sm:py-24">
            <h1 class="mx-auto max-w-3xl text-4xl font-black tracking-tight sm:text-5xl">
                {{ __('How Minelog works') }}
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-mine-muted">
                {{ __('Add waypoints by hand right in Minelog, or install the optional behavior pack to capture your exact coordinates in-game and import them as a line of JSON. Here\'s the whole loop, step by step.') }}
            </p>
        </div>
    </section>

    <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6">
        {{-- The add-on is optional --}}
        <div class="rounded-xl border border-mine-green-2/40 bg-mine-green/10 p-5">
            <p class="font-semibold text-mine-text">{{ __('The add-on is optional.') }}</p>
            <p class="mt-1 text-sm text-mine-muted">
                {{ __('You can add waypoints by hand at any time — open a world in Minelog and use the Add a Waypoint form to enter a name and coordinates. The Minelog Waypoints behavior pack below is the fastest way to capture lots of exact positions while you play, but it is not required to use Minelog.') }}
            </p>
        </div>

        {{-- Overview --}}
        <section class="mt-10 grid gap-4 sm:grid-cols-3">
            @foreach ([
                ['1', __('Install the add-on'), __('Optional: import the Minelog Waypoints behavior pack to capture coordinates in-game.')],
                ['2', __('Save & export'), __('Use !wp commands in chat, then export your log as JSON.')],
                ['3', __('Add & share'), __('Add waypoints by hand or paste the export, then browse the map and share it.')],
            ] as [$n, $heading, $body])
                <div class="rounded-xl border border-mine-line bg-mine-panel p-5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-mine-green/20 font-bold text-mine-green-2">{{ $n }}</div>
                    <h3 class="mt-3 font-semibold">{{ $heading }}</h3>
                    <p class="mt-1 text-sm text-mine-muted">{{ $body }}</p>
                </div>
            @endforeach
        </section>

        {{-- Step 1 --}}
        <section class="mt-16">
            <h2 class="text-2xl font-bold tracking-tight">
                <span class="text-mine-green-2">1.</span> {{ __('Install the Minecraft add-on (optional)') }}
            </h2>
            <p class="mt-3 text-mine-muted">
                {{ __('Skip this if you\'d rather add waypoints by hand — jump to step 4. Otherwise, Minelog ships with a Bedrock behavior pack called Minelog Waypoints. It records your exact position with a label and stores the log inside your world, so it survives restarts and works on Realms.') }}
            </p>

            <ol class="mt-6 space-y-5">
                <li class="flex gap-4">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-mine-panel-2 text-sm font-bold text-mine-green-2 ring-1 ring-mine-line">1</span>
                    <div>
                        <p class="font-semibold">{{ __('Download the add-on') }}</p>
                        <p class="mt-1 text-sm text-mine-muted">
                            {{ __('Grab the latest minelog.mcpack from the') }}
                            <a href="https://github.com/erikaheidi/minelog/releases/latest" target="_blank" rel="noopener" class="font-semibold text-mine-green-2 underline">{{ __('Releases page') }}</a>{{ __(' on GitHub. No tools required — just download the file.') }}
                        </p>
                        <p class="mt-2 text-sm text-mine-muted">{{ __('Prefer to build it yourself? Zip the contents of the addon/ directory so manifest.json sits at the zip root:') }}</p>
                        <pre class="mt-3 overflow-x-auto rounded-lg border border-mine-line bg-mine-panel-2 p-3 text-sm"><code>cd addon &amp;&amp; zip -r ../minelog.mcpack . -x 'README.md' &amp;&amp; cd ..</code></pre>
                    </div>
                </li>
                <li class="flex gap-4">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-mine-panel-2 text-sm font-bold text-mine-green-2 ring-1 ring-mine-line">2</span>
                    <div>
                        <p class="font-semibold">{{ __('Import it into Minecraft') }}</p>
                        <p class="mt-1 text-sm text-mine-muted">{{ __('Double-click minelog.mcpack (or open it with Minecraft). Bedrock imports it as an available behavior pack.') }}</p>
                    </div>
                </li>
                <li class="flex gap-4">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-mine-panel-2 text-sm font-bold text-mine-green-2 ring-1 ring-mine-line">3</span>
                    <div>
                        <p class="font-semibold">{{ __('Enable it on your world') }}</p>
                        <p class="mt-1 text-sm text-mine-muted">{{ __('For a local world: Create/Edit World → Behavior Packs → activate Minelog Waypoints. For a Realm: Realm settings → World → Behavior Packs. If your version prompts for it, turn on the Scripting / Beta APIs experiment.') }}</p>
                    </div>
                </li>
            </ol>

            <div class="mt-5 rounded-lg border border-mine-line bg-mine-panel p-4 text-sm text-mine-muted">
                <span class="font-semibold text-mine-text">{{ __('Requirements:') }}</span>
                {{ __('Minecraft Bedrock 1.21+. The add-on uses the @minecraft/server scripting API, which is Bedrock-only (it does not work on Java Edition).') }}
            </div>
        </section>

        {{-- Step 2 --}}
        <section class="mt-16">
            <h2 class="text-2xl font-bold tracking-tight">
                <span class="text-mine-green-2">2.</span> {{ __('Save waypoints while you play') }}
            </h2>
            <p class="mt-3 text-mine-muted">
                {{ __('Open the in-game chat and type a command. When you save, the add-on records your exact coordinates, the dimension you\'re in, and the label you gave it.') }}
            </p>

            <div class="mt-6 overflow-x-auto rounded-xl border border-mine-line">
                <table class="w-full text-left text-sm">
                    <thead class="bg-mine-panel-2 text-mine-text">
                        <tr>
                            <th class="px-4 py-3 font-semibold">{{ __('Command') }}</th>
                            <th class="px-4 py-3 font-semibold">{{ __('What it does') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-mine-line">
                        @foreach ([
                            ['!wp save <label>', __('Save your current position with a label.')],
                            ['!wp list', __('List every saved waypoint with its number.')],
                            ['!wp remove <n>', __('Remove waypoint number n (from !wp list).')],
                            ['!wp export', __('Print the full log as JSON to copy into Minelog.')],
                            ['!wp clear confirm', __('Delete all saved waypoints.')],
                            ['!wp help', __('Show the list of commands.')],
                        ] as [$cmd, $desc])
                            <tr class="bg-mine-panel">
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-mine-green-2">{{ $cmd }}</td>
                                <td class="px-4 py-3 text-mine-muted">{{ $desc }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="mt-5 text-sm text-mine-muted">
                {{ __('Example: standing at your base, type') }}
                <code class="rounded bg-mine-panel-2 px-1.5 py-0.5 font-mono text-mine-text">!wp save Main base</code>
                {{ __('and Minelog confirms the saved coordinates in chat.') }}
            </p>
        </section>

        {{-- Step 3 --}}
        <section class="mt-16">
            <h2 class="text-2xl font-bold tracking-tight">
                <span class="text-mine-green-2">3.</span> {{ __('Export the log from the game') }}
            </h2>
            <p class="mt-3 text-mine-muted">
                {{ __('When you\'re ready to bring your waypoints into Minelog, run:') }}
            </p>
            <pre class="mt-3 overflow-x-auto rounded-lg border border-mine-line bg-mine-panel-2 p-3 text-sm"><code>!wp export</code></pre>
            <p class="mt-3 text-mine-muted">
                {{ __('The add-on prints your whole log as a single JSON line in chat. Select and copy that line.') }}
            </p>

            <div class="mt-5 rounded-lg border border-mine-line bg-mine-panel p-4 text-sm text-mine-muted">
                <span class="font-semibold text-mine-text">{{ __('Playing on Switch or a console?') }}</span>
                {{ __('Some consoles can\'t copy chat text. Join the same Realm from a PC or phone Bedrock client (with the pack enabled), run !wp export there, and copy the line. Your waypoints live inside the world, so they\'re available from any device on that Realm.') }}
            </div>

            <div class="mt-4 rounded-lg border border-mine-line bg-mine-panel p-4 text-sm text-mine-muted">
                <span class="font-semibold text-mine-text">{{ __('A note on limits:') }}</span>
                {{ __('The log is stored in a single world property capped at about 250–270 waypoints. The add-on warns you in chat as you approach the limit — export and import into Minelog before it fills up.') }}
            </div>
        </section>

        {{-- Step 4 --}}
        <section class="mt-16">
            <h2 class="text-2xl font-bold tracking-tight">
                <span class="text-mine-green-2">4.</span> {{ __('Add waypoints in Minelog') }}
            </h2>
            <ol class="mt-6 space-y-5">
                <li class="flex gap-4">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-mine-panel-2 text-sm font-bold text-mine-green-2 ring-1 ring-mine-line">1</span>
                    <div>
                        <p class="font-semibold">{{ __('Create a world') }}</p>
                        <p class="mt-1 text-sm text-mine-muted">{{ __('In My Worlds, create a world to hold your waypoints. Add a name, an optional description, and the world seed so others can reproduce it.') }}</p>
                    </div>
                </li>
                <li class="flex gap-4">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-mine-panel-2 text-sm font-bold text-mine-green-2 ring-1 ring-mine-line">2</span>
                    <div>
                        <p class="font-semibold">{{ __('Add by hand, or paste an export') }}</p>
                        <p class="mt-1 text-sm text-mine-muted">{{ __('Open the world and press Add Waypoints. Use the "Add a Waypoint" form to type in a name and coordinates — no add-on needed — or, if you used the add-on, paste the exported line into "Import from Realm" and press Import waypoints. Re-importing is safe: Minelog updates existing waypoints instead of duplicating them.') }}</p>
                    </div>
                </li>
                <li class="flex gap-4">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-mine-panel-2 text-sm font-bold text-mine-green-2 ring-1 ring-mine-line">3</span>
                    <div>
                        <p class="font-semibold">{{ __('Enrich & browse') }}</p>
                        <p class="mt-1 text-sm text-mine-muted">{{ __('Add notes, tags, and screenshots to each waypoint, filter by dimension, and switch to the interactive map to see everything in place.') }}</p>
                    </div>
                </li>
                <li class="flex gap-4">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-mine-panel-2 text-sm font-bold text-mine-green-2 ring-1 ring-mine-line">4</span>
                    <div>
                        <p class="font-semibold">{{ __('Share it') }}</p>
                        <p class="mt-1 text-sm text-mine-muted">{{ __('Flip a world to Public and anyone with the link can browse its gallery, map, and seed — no account needed.') }}</p>
                    </div>
                </li>
            </ol>
        </section>

        {{-- CTA --}}
        <section class="mt-16 rounded-2xl border border-mine-line bg-mine-panel p-8 text-center">
            <h2 class="text-2xl font-bold tracking-tight">{{ __('Ready to map your world?') }}</h2>
            <p class="mx-auto mt-2 max-w-xl text-mine-muted">{{ __('Create a free account, spin up a world, and paste in your first export.') }}</p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                @auth
                    <a href="{{ route('worlds.index') }}" class="rounded-lg bg-mine-green px-5 py-2.5 font-bold text-white transition hover:bg-mine-green-2">{{ __('Go to my worlds') }}</a>
                @else
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="rounded-lg bg-mine-green px-5 py-2.5 font-bold text-white transition hover:bg-mine-green-2">{{ __('Start your log') }}</a>
                    @endif
                @endauth
                <a href="{{ route('home') }}" class="text-sm font-semibold text-mine-muted transition hover:text-mine-text">{{ __('Explore public worlds ↓') }}</a>
            </div>
        </section>
    </div>
</x-layouts.public>
