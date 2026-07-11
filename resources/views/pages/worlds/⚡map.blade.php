<?php

use App\Models\World;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('World map')] class extends Component {
    public World $world;

    public function mount(World $world): void
    {
        $this->authorize('update', $world);
        $this->world = $world;
    }

    /**
     * @return list<array<string, mixed>>
     */
    #[Computed]
    public function markers(): array
    {
        return $this->world->mapMarkers();
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    @assets
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @endassets

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
    </div>

    <x-world-tabs :world="$world" active="map" />

    @if (empty($this->markers))
        <flux:card class="text-center">
            <flux:subheading>{{ __('No waypoints with coordinates yet. Import some to see them on the map.') }}</flux:subheading>
        </flux:card>
    @else
        <x-waypoint-map :markers="$this->markers" class="h-[68vh] min-h-[420px]" />
        <div class="flex flex-wrap gap-4 text-sm text-mine-muted">
            @foreach (['overworld' => __('Overworld'), 'nether' => __('Nether'), 'end' => __('End')] as $dim => $label)
                <span class="flex items-center gap-2"><span class="inline-block h-3 w-3 rounded" style="background: {{ ['overworld' => '#4a8c3f', 'nether' => '#b3312c', 'end' => '#7b6ca8'][$dim] }}"></span> {{ $label }}</span>
            @endforeach
        </div>
    @endif
</div>
