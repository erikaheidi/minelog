@props(['size' => 22])

{{-- Minelog cube + wordmark, matching the original POC branding. --}}
<span {{ $attributes->merge(['class' => 'flex items-center gap-2.5 font-extrabold tracking-wide']) }}>
    <span
        class="inline-block shrink-0 rounded-[3px] border-2"
        style="width: {{ $size }}px; height: {{ $size }}px; border-color: #2c3a26; background: linear-gradient(135deg, #5ea84f 50%, #4a8c3f 50%);"
    ></span>
    <span class="text-mine-text">Minelog</span>
</span>
