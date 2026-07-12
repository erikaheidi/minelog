@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'canonical' => null,
    'type' => 'website',
    'imageWidth' => null,
    'imageHeight' => null,
])

@php
    $siteName = config('app.name', 'Minelog');
    $resolvedTitle = filled($title) ? $title : $siteName;
    $resolvedDescription = \Illuminate\Support\Str::limit(
        filled($description) ? $description : __('A travel log for your Minecraft worlds — map your waypoints and share them.'),
        200,
    );
    $resolvedImage = filled($image) ? $image : asset('og-image.png');
    $resolvedUrl = filled($canonical) ? $canonical : url()->current();
@endphp

<meta name="description" content="{{ $resolvedDescription }}" />
<link rel="canonical" href="{{ $resolvedUrl }}" />

{{-- Open Graph --}}
<meta property="og:type" content="{{ $type }}" />
<meta property="og:site_name" content="{{ $siteName }}" />
<meta property="og:title" content="{{ $resolvedTitle }}" />
<meta property="og:description" content="{{ $resolvedDescription }}" />
<meta property="og:url" content="{{ $resolvedUrl }}" />
<meta property="og:image" content="{{ $resolvedImage }}" />
@if (filled($imageWidth) && filled($imageHeight))
    <meta property="og:image:width" content="{{ $imageWidth }}" />
    <meta property="og:image:height" content="{{ $imageHeight }}" />
@endif

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $resolvedTitle }}" />
<meta name="twitter:description" content="{{ $resolvedDescription }}" />
<meta name="twitter:image" content="{{ $resolvedImage }}" />
