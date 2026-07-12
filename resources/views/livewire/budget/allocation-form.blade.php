@php
    $field = 'mt-1.5 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-base shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm';
    $label = 'block text-sm font-medium text-slate-700 dark:text-slate-300';
    $isEdit = isset($allocation) && $allocation && $allocation->exists;
@endphp

<div class="pb-28 lg:pb-0">
    <x-page-header :title="$isEdit ? 'Edit Budget Allocation' : 'New Budget Allocation'" :subtitle="$isEdit ? ('Adjust the allocation amount or remarks. No overallocation is permitted.') : ('Create a top-level budget pool for a department, division, office, or cost center.')">
        <x-slot:actions>
            <x-button href="{{ route('budget-allocations.index') }}" variant="secondary" size="sm">Back</x-button>
        </x-slot:actions>
    </x-page-header>

    @if($isEdit && $allocation->parent_id)
        <div class="mx-4 mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 sm:mx-6">
            This is a sub-allocation. Increasing the amount is capped by the parent's remaining balance (plus what this node already holds).
        </div>
    @endif

    <form wire:submit="save" class="space-y-5 sm:space-y-6">
        <x-card title="Allocation details">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @if(!$isEdit)
                    <div>
                        <label for="fiscal_year_id" class="{{ $label }}">Fiscal Year</label>
                        <select id="fiscal_year_id" wire:model="fiscal_year_id" class="{{ $field }}">
                            <option value="">Select fiscal year…</option>
                            @foreach($fiscalYears as $fy)
                                <option value="{{ $fy->id }}">FY {{ $fy->year }}</option>
                            @endforeach
                        </select>
                        @error('fiscal_year_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="level" class="{{ $label }}">Level</label>
                        <select id="level" wire:model="level" class="{{ $field }}">
                            <option value="department">Department</option>
                            <option value="division">Division</option>
                            <option value="office">Office</option>
                            <option value="cost_center">Cost Center</option>
                        </select>
                        @error('level') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                @else
                    <div>
                        <label class="{{ $label }}">Fiscal Year</label>
                        <p class="mt-1.5 text-slate-700 dark:text-slate-200">FY {{ $allocation->fiscalYear?->year ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="{{ $label }}">Level</label>
                        <p class="mt-1.5 text-slate-700 dark:text-slate-200">{{ ucfirst(str_replace('_', ' ', $allocation->level)) }}</p>
                    </div>
                @endif

                <div>
                    <label for="department_id" class="{{ $label }}">Department</label>
                    <select id="department_id" wire:model="department_id" class="{{ $field }}">
                        <option value="">—</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="division_id" class="{{ $label }}">Division</label>
                    <select id="division_id" wire:model="division_id" class="{{ $field }}">
                        <option value="">—</option>
                        @foreach($divisions as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="office_id" class="{{ $label }}">Office</label>
                    <select id="office_id" wire:model="office_id" class="{{ $field }}">
                        <option value="">—</option>
                        @foreach($offices as $o)
                            <option value="{{ $o->id }}">{{ $o->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="cost_center_id" class="{{ $label }}">Cost Center</label>
                    <select id="cost_center_id" wire:model="cost_center_id" class="{{ $field }}">
                        <option value="">—</option>
                        @foreach($costCenters as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="pap_id" class="{{ $label }}">PAP</label>
                    <select id="pap_id" wire:model="pap_id" class="{{ $field }}">
                        <option value="">—</option>
                        @foreach($paps as $p)
                            <option value="{{ $p->id }}">{{ $p->code }} — {{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="fund_source_id" class="{{ $label }}">Fund Source</label>
                    <select id="fund_source_id" wire:model="fund_source_id" class="{{ $field }}">
                        <option value="">—</option>
                        @foreach($fundSources as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="uacs_code_id" class="{{ $label }}">UACS Code</label>
                    <select id="uacs_code_id" wire:model="uacs_code_id" class="{{ $field }}">
                        <option value="">—</option>
                        @foreach($uacsCodes as $u)
                            <option value="{{ $u->id }}">{{ $u->code }} — {{ $u->description }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="allocated_amount" class="{{ $label }}">Allocated Amount (₱)</label>
                    <input id="allocated_amount" type="number" step="0.01" min="0.01" wire:model="allocated_amount" class="{{ $field }}" />
                    @error('allocated_amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="remarks" class="{{ $label }}">Remarks</label>
                    <textarea id="remarks" wire:model="remarks" rows="2" class="{{ $field }}"></textarea>
                </div>
            </div>
        </x-card>

        <div class="flex justify-end gap-2">
            <x-button href="{{ route('budget-allocations.index') }}" variant="secondary">Cancel</x-button>
            <x-button type="submit">Save Allocation</x-button>
        </div>
    </form>
</div>
