@php
    $field = 'mt-1.5 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-base shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm';
    $label = 'block text-sm font-medium text-slate-700 dark:text-slate-300';
    $hint = 'mt-1 text-xs text-slate-500 dark:text-slate-400';
    $sections = \App\Livewire\Planning\ProjectProposalForm::SECTIONS;
    $cancelUrl = $projectProposal ? route('project-proposals.show', $projectProposal) : route('project-proposals.index');
@endphp

<div class="pb-28 lg:pb-0">
    <x-page-header
        :title="$projectProposal ? 'Step 1: Project Proposal' : 'Step 1: New Project Proposal'"
        subtitle="NMP-PP-01 — complete the project proposal, then continue to Market Scoping."
    >
        @if($projectProposal)
            <x-slot:actions>
                <x-button href="{{ route('project-proposals.show', $projectProposal) }}" variant="secondary" size="sm" class="hidden sm:inline-flex">Back</x-button>
            </x-slot:actions>
        @endif
    </x-page-header>

    <x-pipeline-stepper :current-step="$pipelineStep" />

    @error('ai')
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">{{ $message }}</div>
    @enderror

    @if(session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    <x-card class="mb-5 border-violet-200 bg-violet-50/60 dark:border-violet-800 dark:bg-violet-900/10">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="{{ $label }}">Generate Context from AI</span>
                <p class="mt-1 text-xs text-violet-700 dark:text-violet-300">
                    Complete Basic Information first (title, type, division, total cost), then generate narrative context for Sections II–VI.
                    @if($aiConfigured)
                        Using OpenAI ({{ config('services.openai.model') }}).
                    @else
                        OpenAI is not configured — using built-in smart templates. Set <code class="rounded bg-violet-100 px-1 dark:bg-violet-900">OPENAI_API_KEY</code> for live AI drafting.
                    @endif
                </p>
            </div>
            @include('livewire.planning.partials.project-proposal-ai-draft-button', ['label' => 'Generate Context from AI'])
        </div>
    </x-card>

    {{-- Mobile section progress --}}
    <div class="mb-4 lg:hidden">
        <div class="mb-2 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
            <span>Section {{ $focusedSection + 1 }} of {{ count($sections) }}</span>
            <span class="font-medium text-slate-700 dark:text-slate-200">{{ $sections[$focusedSection]['short'] }}</span>
        </div>
        <div class="h-1.5 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
            <div
                class="h-full rounded-full bg-primary-600 transition-all duration-300"
                style="width: {{ (($focusedSection + 1) / count($sections)) * 100 }}%"
            ></div>
        </div>
    </div>

    {{-- Mobile section tabs --}}
    <div class="mb-4 -mx-1 flex gap-2 overflow-x-auto px-1 pb-1 lg:hidden" role="tablist" aria-label="Proposal sections">
        @foreach($sections as $index => $section)
            <button
                type="button"
                wire:click="focusSection({{ $index }})"
                role="tab"
                aria-selected="{{ $focusedSection === $index ? 'true' : 'false' }}"
                class="shrink-0 rounded-full border px-3 py-2 text-sm transition
                    {{ $focusedSection === $index
                        ? 'border-primary-600 bg-primary-50 font-semibold text-primary-800 dark:border-primary-500 dark:bg-primary-900/30 dark:text-primary-200'
                        : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}"
            >
                <span class="block whitespace-nowrap">{{ $section['short'] }}</span>
            </button>
        @endforeach
    </div>

    <form wire:submit="save" class="space-y-5 sm:space-y-6">
        {{-- Section I: Basic Information --}}
        <x-card
            title="I. Basic Information"
            class="{{ $focusedSection === 0 ? 'block' : 'hidden lg:block' }}"
        >
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="{{ $label }}">Division</label>
                    <select wire:model="division_id" class="{{ $field }}">
                        <option value="">Select&hellip;</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                        @endforeach
                    </select>
                    @error('division_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">Fiscal Year</label>
                    <select wire:model="fiscal_year_id" class="{{ $field }}">
                        <option value="">Select&hellip;</option>
                        @foreach($fiscalYears as $fy)
                            <option value="{{ $fy->id }}">{{ $fy->year }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}">Document Reference</label>
                    <input type="text" wire:model="document_ref" class="{{ $field }}" readonly>
                </div>
                <div>
                    <label class="{{ $label }}">With Enclosures</label>
                    <input type="text" wire:model="with_enclosures" class="{{ $field }}" placeholder="e.g. Market Scoping Checklist">
                </div>
                <div>
                    <label class="{{ $label }}">Type of Project</label>
                    <input type="text" wire:model="project_type" class="{{ $field }}" placeholder="e.g. Procurement Project">
                    @error('project_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="{{ $label }}">Title</label>
                    <input type="text" wire:model="title" class="{{ $field }}" placeholder="Project title">
                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="{{ $label }}">Schedule</label>
                    <x-month-year-range-picker :years="$scheduleYearOptions" class="mt-1.5" />
                    @if($schedule)
                        <p class="{{ $hint }}">Selected: {{ $schedule }}</p>
                    @else
                        <p class="{{ $hint }}">Select month and year for the project schedule (optional end date).</p>
                    @endif
                </div>
                <div>
                    <label class="{{ $label }}">Venue / Area</label>
                    <input type="text" wire:model="venue_area" class="{{ $field }}" placeholder="Implementing unit or location">
                </div>
                <div>
                    <label class="{{ $label }}">Total Cost (PhP)</label>
                    <input type="number" step="0.01" min="0" inputmode="decimal" wire:model="total_cost" class="{{ $field }}">
                    @error('total_cost') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">Fund Source</label>
                    <input type="text" wire:model="fund_source_text" class="{{ $field }}" placeholder="Brief fund source">
                </div>
                <div class="md:col-span-2">
                    <label class="{{ $label }}">Proponent</label>
                    <input
                        type="text"
                        wire:model="proponent"
                        class="{{ $field }} bg-slate-50 dark:bg-slate-800/60"
                        placeholder="Name of proponent"
                        readonly
                    >
                    <p class="{{ $hint }}">Auto-filled from the logged-in user account.</p>
                </div>
            </div>

            <div class="mt-4 border-t border-slate-100 pt-4 lg:hidden dark:border-slate-800">
                <button type="button" wire:click="nextSection" class="w-full rounded-lg bg-primary-600 py-3 text-sm font-semibold text-white dark:bg-primary-500">
                    Continue to Rationale &darr;
                </button>
            </div>
        </x-card>

        {{-- Section II: Rationale --}}
        <x-card
            title="II. Rationale"
            class="{{ $focusedSection === 1 ? 'block' : 'hidden lg:block' }}"
        >
            <div class="mb-3 flex justify-end">
                @include('livewire.planning.partials.project-proposal-ai-draft-button', ['section' => 'rationale'])
            </div>
            <x-rich-text-editor model="rationale" placeholder="Describe the rationale for this project…" />
            @include('livewire.planning.partials.project-proposal-section-nav', ['index' => 1, 'prev' => 'Basic Info', 'next' => 'Objectives'])
        </x-card>

        {{-- Section III: Objectives --}}
        <x-card
            title="III. Objectives"
            class="{{ $focusedSection === 2 ? 'block' : 'hidden lg:block' }}"
        >
            <div class="mb-3 flex justify-end">
                @include('livewire.planning.partials.project-proposal-ai-draft-button', ['section' => 'objectives'])
            </div>
            <x-rich-text-editor model="objectives" placeholder="List the project objectives…" />
            @include('livewire.planning.partials.project-proposal-section-nav', ['index' => 2, 'prev' => 'Rationale', 'next' => 'Target Schedule'])
        </x-card>

        {{-- Section IV: Target Schedule --}}
        <x-card
            title="IV. Target Schedule for the Project"
            class="{{ $focusedSection === 3 ? 'block' : 'hidden lg:block' }}"
        >
            <div class="mb-3 flex justify-end">
                @include('livewire.planning.partials.project-proposal-ai-draft-button', ['section' => 'target_schedule'])
            </div>
            <x-rich-text-editor model="target_schedule" placeholder="e.g. Q1 2026 — procurement; Q2 2026 — delivery…" />
            @include('livewire.planning.partials.project-proposal-section-nav', ['index' => 3, 'prev' => 'Objectives', 'next' => 'Budgetary Requirement'])
        </x-card>

        {{-- Section V: Budgetary Requirement --}}
        <x-card
            title="V. Budgetary Requirement"
            class="{{ $focusedSection === 4 ? 'block' : 'hidden lg:block' }}"
        >
            <div class="mb-3 flex justify-end">
                @include('livewire.planning.partials.project-proposal-ai-draft-button', ['section' => 'budgetary_requirement'])
            </div>
            <x-rich-text-editor model="budgetary_requirement" placeholder="Itemize or summarize budgetary requirements…" />
            @include('livewire.planning.partials.project-proposal-section-nav', ['index' => 4, 'prev' => 'Target Schedule', 'next' => 'Fund Source'])
        </x-card>

        {{-- Section VI: Fund Source --}}
        <x-card
            title="VI. Fund Source"
            class="{{ $focusedSection === 5 ? 'block' : 'hidden lg:block' }}"
        >
            <div class="mb-3 flex justify-end">
                @include('livewire.planning.partials.project-proposal-ai-draft-button', ['section' => 'fund_source_narrative'])
            </div>
            <x-rich-text-editor model="fund_source_narrative" placeholder="Describe the fund source in detail…" />
            @include('livewire.planning.partials.project-proposal-section-nav', ['index' => 5, 'prev' => 'Budgetary Requirement', 'next' => null])
        </x-card>

        {{-- Desktop actions --}}
        <div class="hidden items-center justify-between gap-4 lg:flex">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Total Cost <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $this->formattedTotalCost }}</span>
            </p>
            <div class="flex items-center gap-3">
                <x-button href="{{ route('project-proposals.index') }}" variant="secondary">Cancel</x-button>
                <x-button type="button" wire:click="save" variant="secondary">Save Draft</x-button>
                <x-button type="button" wire:click="saveAndContinue">Next: Market Scoping &rarr;</x-button>
            </div>
        </div>

        {{-- Mobile sticky save bar --}}
        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 px-4 py-3 shadow-[0_-4px_20px_rgba(0,0,0,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 lg:hidden">
            <div class="mx-auto flex max-w-3xl items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Total Cost</p>
                    <p class="truncate text-base font-bold text-slate-900 dark:text-white">{{ $this->formattedTotalCost }}</p>
                </div>
                <div class="flex shrink-0 gap-2">
                    @if($focusedSection > 0)
                        <button type="button" wire:click="previousSection" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 dark:border-slate-600 dark:text-slate-200">
                            &larr;
                        </button>
                    @endif
                    @if($focusedSection < count($sections) - 1)
                        <button type="button" wire:click="nextSection" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 dark:border-slate-600 dark:text-slate-200">
                            &rarr;
                        </button>
                    @endif
                    <x-button href="{{ route('project-proposals.index') }}" variant="secondary" size="sm">Cancel</x-button>
                    <x-button type="button" wire:click="saveAndContinue" size="sm">Next &rarr;</x-button>
                </div>
            </div>
        </div>
    </form>
</div>
