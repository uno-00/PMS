<x-card title="1. Agency Information">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label class="{{ $label }}">Name of Procuring Entity</label>
            <input wire:model="procuring_entity" type="text" class="{{ $field }}">
        </div>
        <div>
            <label class="{{ $label }}">End-User / Implementing Unit</label>
            <input wire:model="end_user_unit" type="text" class="{{ $field }}">
        </div>
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
            <label class="{{ $label }}">Name of Representative</label>
            <input wire:model="representative_name" type="text" class="{{ $field }}">
        </div>
        <div>
            <label class="{{ $label }}">Designation of Representative</label>
            <input wire:model="representative_designation" type="text" class="{{ $field }}">
        </div>
    </div>
</x-card>

<x-card title="2. Project Overview">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label class="{{ $label }}">Project Name</label>
            <input wire:model="project_name" type="text" class="{{ $field }}">
            @error('project_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
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
            <label class="{{ $label }}">Estimated Budget (PhP)</label>
            <input wire:model="estimated_budget" type="number" step="0.01" min="0" class="{{ $field }}">
            @error('estimated_budget') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="{{ $label }}">Period of Market Scoping — From</label>
            <input wire:model="period_from" type="date" class="{{ $field }}">
        </div>
        <div>
            <label class="{{ $label }}">Period of Market Scoping — To</label>
            <input wire:model="period_to" type="date" class="{{ $field }}">
        </div>
        <div>
            <label class="{{ $label }}">Expected Date of Delivery</label>
            <input wire:model="expected_delivery" type="date" class="{{ $field }}">
        </div>
    </div>
</x-card>

<x-card title="3. Market Scoping Activity/ies Conducted">
    <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">
        Check all activities conducted in accordance with Section 10 of RA 12009 and its IRR.
    </p>
    <div class="space-y-4">
        @foreach($activityDefinitions as $activity)
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                <label class="flex items-start gap-3">
                    <input type="checkbox" wire:model="activities.{{ $activity['key'] }}.checked" class="mt-1 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-slate-700 dark:text-slate-200">{{ $activity['label'] }}</span>
                </label>
                @if($activity['key'] === 'other')
                    <div class="mt-3">
                        <label class="{{ $label }}">Specify other activity/ies</label>
                        <input wire:model="activities.other.description" type="text" class="{{ $field }}">
                    </div>
                @endif
                <div class="mt-3">
                    <label class="{{ $label }}">Documentation</label>
                    <textarea wire:model="activities.{{ $activity['key'] }}.documentation" rows="2" class="{{ $field }}" placeholder="{{ $activity['documentation_hint'] }}"></textarea>
                </div>
            </div>
        @endforeach
    </div>
</x-card>

<x-card title="4. Market Scoping Results">
    <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">
        Indicate recommendations based on the results of market scoping activities undertaken.
    </p>
    <div class="space-y-4">
        @foreach($parameterDefinitions as $parameter)
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $parameter['letter'] }}. {{ $parameter['label'] }}</p>
                <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="{{ $label }}">Considered? (Yes / No / Not Applicable)</label>
                        <select wire:model="parameters.{{ $parameter['key'] }}.considered" class="{{ $field }}">
                            <option value="">Select&hellip;</option>
                            @foreach($consideredOptions as $value => $optionLabel)
                                <option value="{{ $value }}">{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="{{ $label }}">Recommendations based on the Market Scoping</label>
                        <textarea wire:model="parameters.{{ $parameter['key'] }}.recommendations" rows="2" class="{{ $field }}"></textarea>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-card>

<x-card title="Remarks">
    <textarea wire:model="remarks" rows="3" class="{{ $field }}" placeholder="Optional additional notes"></textarea>
</x-card>
