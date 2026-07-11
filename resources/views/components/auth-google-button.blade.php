@props(['label' => __('Continue with Google')])

<div class="flex flex-col gap-6">
    <flux:separator :text="__('or')" />

    <flux:button :href="route('google.redirect')" variant="outline" class="w-full" data-test="google-login-button">
        <span class="flex items-center justify-center gap-2">
            <svg class="size-5" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.24 1.4-1.66 4.1-5.5 4.1-3.31 0-6.02-2.74-6.02-6.2S8.69 5.8 12 5.8c1.88 0 3.14.8 3.86 1.49l2.63-2.53C16.85 3.2 14.66 2.2 12 2.2 6.98 2.2 2.9 6.28 2.9 11.3S6.98 20.4 12 20.4c5.78 0 9.6-4.06 9.6-9.78 0-.66-.07-1.16-.16-1.66H12z"/>
            </svg>
            {{ $label }}
        </span>
    </flux:button>
</div>
