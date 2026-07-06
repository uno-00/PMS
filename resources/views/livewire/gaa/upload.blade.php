<div>
    <x-page-header title="Upload General Appropriations Act" subtitle="Upload the DBM-issued Excel template for a fiscal year. Rows are validated against configured departments, PAPs, UACS codes, and fund sources." />

    <x-card class="max-w-2xl">
        <form wire:submit="save" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Fiscal Year</label>
                <select wire:model="fiscal_year_id" class="mt-1.5 block w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm">
                    <option value="">Select fiscal year&hellip;</option>
                    @foreach($fiscalYears as $fy)
                        <option value="{{ $fy->id }}">{{ $fy->year }}</option>
                    @endforeach
                </select>
                @error('fiscal_year_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Reference No.</label>
                <input wire:model="reference_no" type="text" placeholder="e.g. RA-12009-2026"
                       class="mt-1.5 block w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">GAA Excel Template</label>
                <input wire:model="file" type="file" accept=".xlsx,.xls,.csv"
                       class="mt-1.5 block w-full rounded-lg border border-slate-300 text-sm text-slate-600 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                <p class="mt-1 text-xs text-slate-400">Expected columns: Department, Division, PAP, UACS Code, Fund Source, Description, Amount.</p>
                <div wire:loading wire:target="file" class="mt-1 text-xs text-primary-600">Uploading&hellip;</div>
                @error('file') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3">
                <x-button type="submit" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Upload &amp; Parse</span>
                    <span wire:loading wire:target="save">Processing&hellip;</span>
                </x-button>
                <x-button href="{{ route('gaa.index') }}" variant="secondary">Cancel</x-button>
            </div>
        </form>
    </x-card>
</div>
