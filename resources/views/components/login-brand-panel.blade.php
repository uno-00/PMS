@php
    $loginBackgroundUrl = \App\Models\Settings\AgencyProfile::current()->loginBackgroundUrl();
@endphp

<div {{ $attributes->merge(['class' => 'theme-gradient-panel relative hidden w-1/2 flex-col justify-between overflow-hidden p-12 text-white shadow-2xl shadow-primary-950/30 lg:flex']) }}>
    @if ($loginBackgroundUrl)
        <div
            class="absolute inset-0 bg-cover bg-center"
            style="background-image: url('{{ $loginBackgroundUrl }}')"
            role="presentation"
            aria-hidden="true"
        ></div>
        <div class="absolute inset-0 bg-gradient-to-br from-primary-950/85 via-primary-900/75 to-primary-800/80" aria-hidden="true"></div>
    @endif

    <div class="relative z-10 flex min-h-full flex-col justify-between">
        {{ $slot }}
    </div>
</div>
