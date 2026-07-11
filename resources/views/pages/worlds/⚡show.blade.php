<?php

use App\Models\Waypoint;
use App\Models\World;
use App\Services\WaypointImporter;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('World')] class extends Component {
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
     * @return list<array<string, mixed>>
     */
    #[Computed]
    public function markers(): array
    {
        return $this->world->mapMarkers();
    }

    public function startEdit(int $id): void
    {
        $waypoint = $this->world->waypoints()->findOrFail($id);
        $this->authorize('update', $waypoint);

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

    public function delete(int $id): void
    {
        $waypoint = $this->world->waypoints()->findOrFail($id);
        $this->authorize('delete', $waypoint);

        if ($waypoint->screenshot_path) {
            Storage::disk('public')->delete($waypoint->screenshot_path);
        }

        $waypoint->delete();

        Flux::toast(variant: 'success', text: __('Waypoint deleted.'));
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    @assets
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJ0jv_A3q6AZ7X/3lVN5nMZmYUx4=" crossorigin=""></script>
    @endassets

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

    {{-- Import panel --}}
    <flux:card>
        <form wire:submit="import" class="flex flex-col gap-4">
            <flux:textarea
                wire:model="payload"
                :label="__('Import from Realm')"
                :placeholder="__('Paste the JSON line printed by !wp export')"
                rows="4"
            />
            <div>
                <flux:button type="submit" variant="primary" icon="arrow-down-tray">{{ __('Import waypoints') }}</flux:button>
            </div>
        </form>
    </flux:card>

    {{-- Map --}}
    @if (! empty($this->markers))
        <x-waypoint-map :markers="$this->markers" class="h-[52vh] min-h-[360px]" />
    @endif

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
            <flux:subheading>{{ __('No waypoints yet. Save some in-game with !wp save <label>, then export and paste above.') }}</flux:subheading>
        </flux:card>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->waypoints as $wp)
                <flux:card class="flex flex-col gap-3" wire:key="wp-{{ $wp->id }}">
                    @if ($wp->screenshot_path)
                        <img src="{{ Storage::url($wp->screenshot_path) }}" alt="" class="aspect-video w-full rounded-lg object-cover" />
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

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">{{ __('Save waypoint') }}</flux:button>
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
            </div>
        </form>
    </flux:modal>
</div>
