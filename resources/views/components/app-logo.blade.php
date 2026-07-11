@props([
    'sidebar' => false,
])

@php
    $cube = 'inline-block h-6 w-6 shrink-0 rounded-[3px] border-2';
    $cubeStyle = 'border-color: #2c3a26; background: linear-gradient(135deg, #5ea84f 50%, #4a8c3f 50%);';
@endphp

@if($sidebar)
    <flux:sidebar.brand name="Minelog" {{ $attributes }}>
        <x-slot name="logo">
            <span class="{{ $cube }}" style="{{ $cubeStyle }}"></span>
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Minelog" {{ $attributes }}>
        <x-slot name="logo">
            <span class="{{ $cube }}" style="{{ $cubeStyle }}"></span>
        </x-slot>
    </flux:brand>
@endif
