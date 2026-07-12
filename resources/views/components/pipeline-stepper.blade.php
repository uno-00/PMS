@props(['currentStep' => null])

@php
    $steps = \App\Enums\ProjectProposalPipelineStep::wizardSteps();
    $currentStep = $currentStep ?? \App\Enums\ProjectProposalPipelineStep::ProjectProposal;
    $current = $currentStep instanceof \App\Enums\ProjectProposalPipelineStep
        ? $currentStep
        : \App\Enums\ProjectProposalPipelineStep::from((string) $currentStep);
@endphp

<div {{ $attributes->class('mb-6') }}>

    <nav aria-label="Indicative PPMP pipeline progress">
        <ol class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            @foreach($steps as $index => $step)
                @php
                    $stepEnum = $step['step'];
                    $isComplete = $stepEnum->number() < $current->number() || $current === \App\Enums\ProjectProposalPipelineStep::Completed;
                    $isCurrent = $stepEnum === $current;
                @endphp
                <li class="relative rounded-xl border p-4 transition
                    {{ $isCurrent ? 'border-primary-500 bg-primary-50 dark:border-primary-500 dark:bg-primary-900/20' : '' }}
                    {{ $isComplete && ! $isCurrent ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-900/20' : '' }}
                    {{ ! $isComplete && ! $isCurrent ? 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900' : '' }}">
                    <div class="flex items-start gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold
                            {{ $isComplete ? 'bg-emerald-600 text-white' : '' }}
                            {{ $isCurrent ? 'bg-primary-600 text-white' : '' }}
                            {{ ! $isComplete && ! $isCurrent ? 'bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-300' : '' }}">
                            @if($isComplete && ! $isCurrent)
                                <x-icon name="check" class="h-4 w-4" />
                            @else
                                {{ $index + 1 }}
                            @endif
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $step['title'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $step['description'] }}</p>
                            @if($isCurrent)
                                <p class="mt-1 text-xs font-medium text-primary-700 dark:text-primary-300">Current step</p>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ol>
    </nav>
</div>
