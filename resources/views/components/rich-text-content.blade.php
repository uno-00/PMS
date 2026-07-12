@props(['content'])

@if($content)
    <div {{ $attributes->merge(['class' => 'rich-text-content text-sm leading-relaxed text-slate-700 dark:text-slate-200']) }}>
        {!! \App\Support\RichTextSanitizer::clean($content) !!}
    </div>
@else
    <p class="text-sm text-slate-400">—</p>
@endif
