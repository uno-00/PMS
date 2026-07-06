@props([
    'compact' => false,
    'variant' => 'default',
    'href' => null,
    'subtitle' => null,
])

@php
    $agency = \App\Models\Settings\AgencyProfile::current();
    $logoUrl = $agency->logoUrl();
    $name = $agency->displayName();
    $initials = $agency->initials();
    $tagline = $subtitle ?? ($agency->acronym ?: null);

    $isInverse = $variant === 'inverse';

    $wrapperClass = match ($variant) {
        'inverse' => 'text-white',
        default => '',
    };

    $nameClass = match ($variant) {
        'inverse' => 'text-white',
        default => 'text-slate-900 dark:text-white',
    };

    $taglineClass = match ($variant) {
        'inverse' => 'text-white/75',
        default => 'text-slate-400',
    };

    $fallbackClass = match ($variant) {
        'inverse' => 'bg-white/15 text-white ring-1 ring-white/20',
        default => 'bg-primary-700 text-white',
    };

    $logoClass = match ($variant) {
        'inverse' => 'bg-white/95 ring-1 ring-white/30',
        default => 'bg-white ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700',
    };

    $sizeClass = $compact ? 'h-8 w-8 text-xs' : 'h-9 w-9 text-sm';
    $nameSizeClass = $compact ? 'text-sm' : 'text-sm';
    $taglineSizeClass = $compact ? 'text-[11px]' : 'text-xs';
@endphp

@if($href)
    <a {{ $attributes->merge(['class' => "flex min-w-0 items-center gap-3 {$wrapperClass}"]) }} href="{{ $href }}">
@else
    <div {{ $attributes->merge(['class' => "flex min-w-0 items-center gap-3 {$wrapperClass}"]) }}>
@endif
        @if($logoUrl)
            <img wire:key="brand-logo-{{ $agency->updated_at?->timestamp ?? 'default' }}" src="{{ $logoUrl }}" alt="{{ $name }}" class="{{ $sizeClass }} shrink-0 rounded-lg object-contain p-0.5 {{ $logoClass }}">
        @else
            <div class="{{ $sizeClass }} flex shrink-0 items-center justify-center rounded-lg font-bold {{ $fallbackClass }}">
                {{ $initials }}
            </div>
        @endif
        <div class="min-w-0 leading-tight">
            <p class="{{ $nameSizeClass }} truncate font-semibold {{ $nameClass }}">{{ $name }}</p>
            @if($tagline && ! $compact)
                <p class="{{ $taglineSizeClass }} truncate {{ $taglineClass }}">{{ $tagline }}</p>
            @endif
        </div>
@if($href)
    </a>
@else
    </div>
@endif
