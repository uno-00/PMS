@props(['currentStep' => 1])

@php
    $steps = \App\Enums\PpmpConsolidationStep::wizardSteps();
    $current = max(1, min(7, (int) $currentStep));
@endphp

<div {{ $attributes->class('mb-6') }}>
    <nav aria-label="PPMP Consolidation progress">
        <ol class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
            @foreach($steps as $index => $step)
                @php
                    $stepNum = $step['step']->value;
                    $isComplete = $stepNum < $current;
                    $isCurrent = $stepNum === $current;
                @endphp
                <li class="rounded-xl border p-3 text-xs transition
                    {{ $isCurrent ? 'border-primary-500 bg-primary-50 dark:border-primary-500 dark:bg-primary-900/20' : '' }}
                    {{ $isComplete ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-900/20' : '' }}
                    {{ ! $isComplete && ! $isCurrent ? 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900' : '' }}">
                    <div class="flex items-start gap-2">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[10px] font-bold
                            {{ $isComplete ? 'bg-emerald-600 text-white' : '' }}
                            {{ $isCurrent ? 'bg-primary-600 text-white' : '' }}
                            {{ ! $isComplete && ! $isCurrent ? 'bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-300' : '' }}">
                            @if($isComplete)
                                <x-icon name="check" class="h-3 w-3" />
                            @else
                                {{ $stepNum }}
                            @endif
                        </span>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $step['title'] }}</p>
                            <p class="mt-0.5 text-[10px] leading-tight text-slate-500 dark:text-slate-400">{{ $step['description'] }}</p>
                        </div>
                    </div>
                </li>
            @endforeach
        </ol>
    </nav>
</div>
