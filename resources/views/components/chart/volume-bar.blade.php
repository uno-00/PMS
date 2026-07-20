@props(['title', 'data' => []])

<x-card :title="$title" :hover="false" {{ $attributes }}>
    <div class="h-64">
        <canvas
            x-data
            x-init="window.PmsCharts.volumeBar($el, { data: {{ Js::from($data) }} })"
        ></canvas>
    </div>
</x-card>
