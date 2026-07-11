<?php

use App\Models\World;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('My Worlds')] class extends Component {
    public string $name = '';

    public string $description = '';

    public string $seed = '';

    public bool $is_public = false;

    public function createWorld(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'seed' => ['nullable', 'string', 'max:64'],
            'is_public' => ['boolean'],
        ]);

        Auth::user()->worlds()->create($validated);

        $this->reset('name', 'description', 'seed', 'is_public');

        Flux::modal('create-world')->close();
        Flux::toast(variant: 'success', text: __('World created.'));
    }

    public function deleteWorld(int $id): void
    {
        $world = World::findOrFail($id);
        $this->authorize('delete', $world);

        $world->delete();

        Flux::toast(variant: 'success', text: __('World deleted.'));
    }

    /**
     * @return \Illuminate\Support\Collection<int, World>
     */
    #[Computed]
    public function worlds()
    {
        return Auth::user()->worlds()->withCount('waypoints')->latest()->get();
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('My Worlds') }}</flux:heading>
            <flux:subheading>{{ __('Each world holds its own waypoints and can be shared publicly.') }}</flux:subheading>
        </div>
        <flux:modal.trigger name="create-world">
            <flux:button variant="primary" icon="plus">{{ __('New world') }}</flux:button>
        </flux:modal.trigger>
    </div>

    @if ($this->worlds->isEmpty())
        <flux:card class="text-center">
            <flux:subheading>{{ __('No worlds yet. Create one to start importing waypoints.') }}</flux:subheading>
        </flux:card>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->worlds as $world)
                <flux:card class="flex flex-col gap-3" wire:key="world-{{ $world->id }}">
                    <div class="flex items-start justify-between gap-2">
                        <flux:heading size="lg">
                            <a href="{{ route('worlds.show', $world) }}" wire:navigate class="hover:underline">{{ $world->name }}</a>
                        </flux:heading>
                        @if ($world->is_public)
                            <flux:badge size="sm" color="green">{{ __('Public') }}</flux:badge>
                        @else
                            <flux:badge size="sm" color="zinc">{{ __('Private') }}</flux:badge>
                        @endif
                    </div>

                    @if ($world->description)
                        <flux:text class="text-sm">{{ Str::limit($world->description, 100) }}</flux:text>
                    @endif

                    <flux:text class="text-sm text-zinc-500">
                        {{ trans_choice(':count waypoint|:count waypoints', $world->waypoints_count, ['count' => $world->waypoints_count]) }}
                        @if ($world->seed)
                            · {{ __('seed') }} <span class="font-mono">{{ $world->seed }}</span>
                        @endif
                    </flux:text>

                    <div class="mt-auto flex flex-wrap gap-2 pt-2">
                        <flux:button size="sm" :href="route('worlds.show', $world)" wire:navigate>{{ __('Manage') }}</flux:button>
                        @if ($world->is_public)
                            <flux:button size="sm" variant="ghost" :href="route('worlds.public', $world)" target="_blank">{{ __('View public') }}</flux:button>
                        @endif
                        <flux:button
                            size="sm"
                            variant="danger"
                            wire:click="deleteWorld({{ $world->id }})"
                            wire:confirm="{{ __('Delete this world and all its waypoints?') }}"
                        >{{ __('Delete') }}</flux:button>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif

    {{-- Create world modal --}}
    <flux:modal name="create-world" class="w-full max-w-lg">
        <form wire:submit="createWorld" class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('New world') }}</flux:heading>

            <flux:input wire:model="name" :label="__('Name')" :placeholder="__('e.g. Survival Realm')" required />
            <flux:textarea wire:model="description" :label="__('Description')" rows="3" :placeholder="__('What is this world about?')" />
            <flux:input wire:model="seed" :label="__('Seed')" :placeholder="__('optional — lets visitors reproduce the world')" />
            <flux:switch wire:model="is_public" :label="__('Public')" :description="__('Anyone with the link can view this world.')" />

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">{{ __('Create world') }}</flux:button>
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
            </div>
        </form>
    </flux:modal>
</div>
