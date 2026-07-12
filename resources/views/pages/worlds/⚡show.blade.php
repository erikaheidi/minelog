<?php

use App\Models\Waypoint;
use App\Models\WaypointScreenshot;
use App\Models\World;
use App\Services\WaypointImporter;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('World')] class extends Component {
    use WithFileUploads;

    public World $world;

    // Import + filters
    public string $payload = '';

    public string $q = '';

    public string $dimensionFilter = '';

    // Edit-world modal
    public string $worldName = '';

    public string $worldDescription = '';

    public string $worldSeed = '';

    public bool $worldIsPublic = false;

    // Edit-waypoint modal
    public ?int $editingId = null;

    public string $name = '';

    public ?int $x = null;

    public ?int $y = null;

    public ?int $z = null;

    public string $editDimension = 'overworld';

    public string $tags = '';

    public string $note = '';

    public ?\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $newScreenshot = null;

    // Add-waypoint form
    public string $newName = '';

    public ?int $newX = null;

    public ?int $newY = null;

    public ?int $newZ = null;

    public function mount(World $world): void
    {
        $this->authorize('update', $world);
        $this->world = $world;
    }

    public function import(WaypointImporter $importer): void
    {
        $this->validate(['payload' => ['required', 'string']]);

        $result = $importer->importForWorld($this->world, $this->payload);

        $this->payload = '';

        Flux::toast(variant: 'success', text: __('Imported :created, updated :updated, skipped :skipped.', $result));
    }

    public function addWaypoint(): void
    {
        $this->authorize('update', $this->world);

        $validated = $this->validate([
            'newName' => ['required', 'string', 'max:120'],
            'newX' => ['required', 'integer'],
            'newY' => ['required', 'integer'],
            'newZ' => ['required', 'integer'],
        ]);

        $this->world->waypoints()->create([
            'name' => $validated['newName'],
            'x' => $validated['newX'],
            'y' => $validated['newY'],
            'z' => $validated['newZ'],
            'dimension' => 'overworld',
            'status' => 'confirmed',
        ]);

        $this->reset('newName', 'newX', 'newY', 'newZ');

        Flux::toast(variant: 'success', text: __('Waypoint added. Edit it to set dimension, tags or notes.'));
    }

    public function startEditWorld(): void
    {
        $this->worldName = $this->world->name;
        $this->worldDescription = $this->world->description ?? '';
        $this->worldSeed = $this->world->seed ?? '';
        $this->worldIsPublic = $this->world->is_public;

        Flux::modal('edit-world')->show();
    }

    public function saveWorld(): void
    {
        $this->authorize('update', $this->world);

        $validated = $this->validate([
            'worldName' => ['required', 'string', 'max:120'],
            'worldDescription' => ['nullable', 'string', 'max:2000'],
            'worldSeed' => ['nullable', 'string', 'max:64'],
            'worldIsPublic' => ['boolean'],
        ]);

        $this->world->update([
            'name' => $validated['worldName'],
            'description' => $validated['worldDescription'] ?: null,
            'seed' => $validated['worldSeed'] ?: null,
            'is_public' => $validated['worldIsPublic'],
        ]);

        Flux::modal('edit-world')->close();
        Flux::toast(variant: 'success', text: __('World updated.'));
    }

    /**
     * @return \Illuminate\Support\Collection<int, Waypoint>
     */
    #[Computed]
    public function waypoints()
    {
        return $this->world->waypoints()
            ->with('screenshots')
            ->when($this->q !== '', function ($query) {
                $search = trim($this->q);
                $query->where(fn ($q) => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%"));
            })
            ->when(in_array($this->dimensionFilter, Waypoint::DIMENSIONS, true), fn ($query) => $query->where('dimension', $this->dimensionFilter))
            ->latest()
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\WaypointScreenshot>
     */
    #[Computed]
    public function editingScreenshots()
    {
        if ($this->editingId === null) {
            return collect();
        }

        return $this->world->waypoints()->find($this->editingId)?->screenshots()->get() ?? collect();
    }

    public function startEdit(int $id): void
    {
        $waypoint = $this->world->waypoints()->findOrFail($id);
        $this->authorize('update', $waypoint);

        $this->reset('newScreenshot');
        $this->editingId = $waypoint->id;
        $this->name = $waypoint->name ?? '';
        $this->x = $waypoint->x;
        $this->y = $waypoint->y;
        $this->z = $waypoint->z;
        $this->editDimension = $waypoint->dimension;
        $this->tags = implode(', ', $waypoint->tags ?? []);
        $this->note = $waypoint->note ?? '';

        Flux::modal('edit-waypoint')->show();
    }

    public function updatedNewScreenshot(): void
    {
        $waypoint = $this->world->waypoints()->findOrFail($this->editingId);
        $this->authorize('update', $waypoint);

        $this->validate([
            'newScreenshot' => ['required', 'image', 'max:5120'],
        ]);

        if ($waypoint->screenshots()->count() >= 6) {
            $this->reset('newScreenshot');

            Flux::toast(variant: 'warning', text: __('You can add up to 6 screenshots per waypoint.'));

            return;
        }

        // Screenshots are displayed on public world pages, so they must live on a web-servable disk.
        $disk = config('filesystems.screenshots');
        $path = $this->newScreenshot->store('screenshots/'.$waypoint->id, $disk);

        $waypoint->screenshots()->create([
            'disk' => $disk,
            'path' => $path,
        ]);

        $this->reset('newScreenshot');
        unset($this->editingScreenshots);
    }

    public function saveEdit(): void
    {
        $waypoint = $this->world->waypoints()->findOrFail($this->editingId);
        $this->authorize('update', $waypoint);

        $validated = $this->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'x' => ['nullable', 'integer'],
            'y' => ['nullable', 'integer'],
            'z' => ['nullable', 'integer'],
            'editDimension' => ['required', 'in:'.implode(',', Waypoint::DIMENSIONS)],
            'note' => ['nullable', 'string', 'max:2000'],
            'tags' => ['nullable', 'string', 'max:255'],
        ]);

        $tags = collect(explode(',', $validated['tags'] ?? ''))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values()
            ->all();

        $waypoint->update([
            'name' => $validated['name'] ?: null,
            'x' => $validated['x'],
            'y' => $validated['y'],
            'z' => $validated['z'],
            'dimension' => $validated['editDimension'],
            'note' => $validated['note'] ?: null,
            'tags' => $tags,
            'status' => 'confirmed',
        ]);

        Flux::modal('edit-waypoint')->close();
        Flux::toast(variant: 'success', text: __('Waypoint saved.'));
    }

    public function deleteScreenshot(int $screenshotId): void
    {
        $waypoint = $this->world->waypoints()->findOrFail($this->editingId);
        $this->authorize('update', $waypoint);

        $screenshot = $waypoint->screenshots()->findOrFail($screenshotId);
        $screenshot->deleteFile();
        $screenshot->delete();

        // The DB nulls cover_screenshot_id via nullOnDelete; refresh the model so the UI reflects it.
        $this->world->refresh();

        unset($this->editingScreenshots);

        Flux::toast(variant: 'success', text: __('Screenshot removed.'));
    }

    public function setCover(int $screenshotId): void
    {
        $this->authorize('update', $this->world);

        // Ensure the screenshot belongs to one of this world's waypoints.
        $screenshot = WaypointScreenshot::query()
            ->where('id', $screenshotId)
            ->whereHas('waypoint', fn ($query) => $query->where('world_id', $this->world->id))
            ->firstOrFail();

        $this->world->update(['cover_screenshot_id' => $screenshot->id]);

        Flux::toast(variant: 'success', text: __('Cover image set.'));
    }

    public function clearCover(): void
    {
        $this->authorize('update', $this->world);

        $this->world->update(['cover_screenshot_id' => null]);

        Flux::toast(variant: 'success', text: __('Cover image removed.'));
    }

    public function delete(int $id): void
    {
        $waypoint = $this->world->waypoints()->findOrFail($id);
        $this->authorize('delete', $waypoint);

        foreach ($waypoint->screenshots as $screenshot) {
            $screenshot->deleteFile();
        }

        $waypoint->delete();

        Flux::toast(variant: 'success', text: __('Waypoint deleted.'));
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <flux:text class="text-sm"><a href="{{ route('worlds.index') }}" wire:navigate class="text-zinc-500 hover:underline">← {{ __('My Worlds') }}</a></flux:text>
            <div class="mt-1 flex items-center gap-3">
                <flux:heading size="xl">{{ $world->name }}</flux:heading>
                @if ($world->is_public)
                    <flux:badge size="sm" color="green">{{ __('Public') }}</flux:badge>
                @else
                    <flux:badge size="sm" color="zinc">{{ __('Private') }}</flux:badge>
                @endif
            </div>
            @if ($world->description)
                <flux:subheading class="mt-1 max-w-2xl">{{ $world->description }}</flux:subheading>
            @endif
        </div>
        <div class="flex gap-2">
            @if ($world->is_public)
                <flux:button size="sm" variant="ghost" icon="arrow-top-right-on-square" :href="route('worlds.public', $world)" target="_blank">{{ __('View public') }}</flux:button>
            @endif
            <flux:button size="sm" icon="pencil" wire:click="startEditWorld">{{ __('Edit world') }}</flux:button>
        </div>
    </div>

    <x-world-tabs :world="$world" active="log" />

    {{-- Add / Import panel (collapsed by default) --}}
    <div x-data="{ open: false }" class="flex flex-col gap-4">
        <div>
            <flux:button variant="primary" icon="plus" @click="open = ! open">
                <span x-show="! open">{{ __('Add Waypoints') }}</span>
                <span x-show="open" x-cloak>{{ __('Hide forms') }}</span>
            </flux:button>
        </div>

        <flux:card x-show="open" x-cloak>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 md:gap-8">
                {{-- Add a Waypoint --}}
                <form wire:submit="addWaypoint" class="flex flex-col gap-4">
                    <flux:heading size="lg">{{ __('Add a Waypoint') }}</flux:heading>

                    <flux:input wire:model="newName" :label="__('Name')" :placeholder="__('e.g. Diamond cave')" required />

                    <div class="grid grid-cols-3 gap-3">
                        <flux:input wire:model="newX" :label="__('X')" type="number" inputmode="numeric" required />
                        <flux:input wire:model="newY" :label="__('Y')" type="number" inputmode="numeric" required />
                        <flux:input wire:model="newZ" :label="__('Z')" type="number" inputmode="numeric" required />
                    </div>

                    <div>
                        <flux:button type="submit" variant="primary" icon="plus">{{ __('Add waypoint') }}</flux:button>
                    </div>
                </form>

                {{-- Import from Realm --}}
                <form wire:submit="import" class="flex flex-col gap-4 md:border-l md:border-mine-line md:pl-8">
                    <flux:heading size="lg">{{ __('Import from Realm') }}</flux:heading>

                    <flux:textarea
                        wire:model="payload"
                        :label="__('Paste export JSON')"
                        :placeholder="__('Paste the JSON line printed by /wp:export')"
                        rows="4"
                    />
                    <div>
                        <flux:button type="submit" variant="primary" icon="arrow-down-tray">{{ __('Import waypoints') }}</flux:button>
                    </div>
                </form>
            </div>
        </flux:card>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-end gap-3">
        <flux:input wire:model.live.debounce.400ms="q" :label="__('Search')" :placeholder="__('Name or note…')" class="max-w-xs" />
        <flux:select wire:model.live="dimensionFilter" :label="__('Dimension')" class="max-w-[12rem]">
            <flux:select.option value="">{{ __('All dimensions') }}</flux:select.option>
            @foreach (Waypoint::DIMENSIONS as $d)
                <flux:select.option value="{{ $d }}">{{ ucfirst($d) }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- Grid --}}
    @if ($this->waypoints->isEmpty())
        <flux:card class="text-center">
            <flux:subheading>{{ __('No waypoints yet. Save some in-game with /wp:save "<label>", then export and paste above.') }}</flux:subheading>
        </flux:card>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->waypoints as $wp)
                <flux:card class="flex flex-col gap-3" wire:key="wp-{{ $wp->id }}">
                    @if ($wp->screenshots->isNotEmpty())
                        <div class="relative">
                            <img src="{{ $wp->screenshots->first()->url() }}" alt="" class="aspect-video w-full rounded-lg object-cover" />
                            @if ($wp->screenshots->count() > 1)
                                <flux:badge size="sm" class="absolute right-2 top-2" icon="photo">{{ $wp->screenshots->count() }}</flux:badge>
                            @endif
                        </div>
                    @endif

                    <div class="flex items-start justify-between gap-2">
                        <flux:heading size="lg">{{ $wp->name ?: __('Unnamed waypoint') }}</flux:heading>
                        <flux:badge size="sm" style="background-color: {{ $wp->dimensionColor() }}; color: #fff;">{{ ucfirst($wp->dimension) }}</flux:badge>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="font-mono text-sm">{{ $wp->coordString() }}</span>
                        @if ($wp->status === 'draft')
                            <flux:badge size="sm" color="yellow">{{ __('Draft') }}</flux:badge>
                        @endif
                    </div>

                    @if ($wp->note)
                        <flux:text class="text-sm">{{ Str::limit($wp->note, 100) }}</flux:text>
                    @endif

                    @if (! empty($wp->tags))
                        <div class="flex flex-wrap gap-1">
                            @foreach ($wp->tags as $tag)
                                <flux:badge size="sm">{{ $tag }}</flux:badge>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-auto flex gap-2 pt-2">
                        <flux:button size="sm" wire:click="startEdit({{ $wp->id }})">{{ __('Edit') }}</flux:button>
                        <flux:button
                            size="sm"
                            variant="danger"
                            wire:click="delete({{ $wp->id }})"
                            wire:confirm="{{ __('Delete this waypoint?') }}"
                        >{{ __('Delete') }}</flux:button>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif

    {{-- Edit-world modal --}}
    <flux:modal name="edit-world" class="w-full max-w-lg">
        <form wire:submit="saveWorld" class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Edit world') }}</flux:heading>

            <flux:input wire:model="worldName" :label="__('Name')" required />
            <flux:textarea wire:model="worldDescription" :label="__('Description')" rows="3" />
            <flux:input wire:model="worldSeed" :label="__('Seed')" :placeholder="__('optional')" />
            <flux:switch wire:model="worldIsPublic" :label="__('Public')" :description="__('Anyone with the link can view this world.')" />

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
            </div>
        </form>
    </flux:modal>

    {{-- Edit-waypoint modal --}}
    <flux:modal name="edit-waypoint" class="w-full max-w-lg">
        <form wire:submit="saveEdit" class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Edit waypoint') }}</flux:heading>

            <flux:input wire:model="name" :label="__('Name')" :placeholder="__('e.g. Diamond cave')" />

            <div class="grid grid-cols-3 gap-3">
                <flux:input wire:model="x" :label="__('X')" type="number" />
                <flux:input wire:model="y" :label="__('Y')" type="number" />
                <flux:input wire:model="z" :label="__('Z')" type="number" />
            </div>

            <flux:select wire:model="editDimension" :label="__('Dimension')">
                @foreach (Waypoint::DIMENSIONS as $d)
                    <flux:select.option value="{{ $d }}">{{ ucfirst($d) }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="tags" :label="__('Tags')" :placeholder="__('base, mineshaft, loot')" />
            <flux:textarea wire:model="note" :label="__('Note')" rows="3" />

            {{-- Screenshots --}}
            <div class="flex flex-col gap-3">
                <flux:heading size="sm">{{ __('Screenshots') }}</flux:heading>

                @if ($this->editingScreenshots->isNotEmpty())
                    <flux:text class="text-sm text-zinc-500">{{ __('Set a screenshot as the world cover — it appears on the homepage and social share cards.') }}</flux:text>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach ($this->editingScreenshots as $shot)
                            <div class="group relative" wire:key="shot-{{ $shot->id }}">
                                <img src="{{ $shot->url() }}" alt="" class="aspect-video w-full rounded-lg object-cover {{ $world->cover_screenshot_id === $shot->id ? 'ring-2 ring-mine-green-2' : '' }}" />

                                @if ($world->cover_screenshot_id === $shot->id)
                                    <flux:badge size="sm" color="green" icon="star" class="absolute left-1 top-1">{{ __('Cover') }}</flux:badge>
                                    <flux:button
                                        size="xs"
                                        variant="filled"
                                        icon="star"
                                        class="absolute bottom-1 left-1"
                                        wire:click="clearCover"
                                    >{{ __('Unset') }}</flux:button>
                                @else
                                    <flux:button
                                        size="xs"
                                        variant="filled"
                                        icon="star"
                                        class="absolute bottom-1 left-1"
                                        wire:click="setCover({{ $shot->id }})"
                                    >{{ __('Cover') }}</flux:button>
                                @endif

                                <flux:button
                                    size="xs"
                                    variant="danger"
                                    icon="trash"
                                    class="absolute right-1 top-1"
                                    wire:click="deleteScreenshot({{ $shot->id }})"
                                    wire:confirm="{{ __('Remove this screenshot?') }}"
                                />
                            </div>
                        @endforeach
                    </div>
                @endif

                <flux:input
                    type="file"
                    wire:model="newScreenshot"
                    accept="image/*"
                    :label="__('Add a screenshot')"
                    :description="__('PNG, JPG or WebP up to 5 MB. Add them one at a time.')"
                />
                <div wire:loading wire:target="newScreenshot">
                    <flux:text class="text-sm text-zinc-500">{{ __('Uploading…') }}</flux:text>
                </div>
            </div>

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">{{ __('Save waypoint') }}</flux:button>
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
            </div>
        </form>
    </flux:modal>
</div>
