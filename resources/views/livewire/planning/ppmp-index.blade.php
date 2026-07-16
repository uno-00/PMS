<div>
    <x-page-header title="Project Procurement Management Plans" subtitle="Indicative PPMPs are auto-generated from approved Project Proposals. Manual PPMP creation is for Final PPMPs only.">
        <x-slot:actions>
            @can('create', \App\Models\Planning\Ppmp::class)
                <x-button href="{{ route('ppmps.create') }}"><x-icon name="plus" class="h-4 w-4" /> New Final PPMP</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <x-table.filter-toolbar>Use the column filters below to search PPMP records.</x-table.filter-toolbar>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-400">
                        <th class="py-2 pr-4">Control No.</th>
                        <th class="py-2 pr-4">Title</th>
                        <th class="py-2 pr-4">PPMP Type</th>
                        <th class="py-2 pr-4">Division</th>
                        <th class="py-2 pr-4">FY</th>
                        <th class="py-2 pr-4 text-right">Total ABC</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Date Created</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterControlNo" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterTitle" /></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterDocumentType">
                                @foreach($documentTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterDivisionId">
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterFiscalYearId">
                                @foreach($fiscalYears as $fiscalYear)
                                    <option value="{{ $fiscalYear->id }}">{{ $fiscalYear->year }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterTotalAbc" placeholder="Amount…" type="text" inputmode="decimal" align="right" /></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="status">
                                @foreach(\App\Enums\PpmpStatus::cases() as $case)
                                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-dates /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($ppmps as $ppmp)
                        <tr wire:key="ppmp-{{ $ppmp->id }}">
                            <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $ppmp->control_no }}</td>
                            <td class="py-3 pr-4 font-medium text-slate-700 dark:text-slate-200">{{ $ppmp->title }}</td>
                            <td class="py-3 pr-4">
                                @php
                                    $ppmpType = $ppmp->document_type?->label() ?? 'Indicative';
                                    $ppmpTypeClass = ($ppmp->document_type?->value ?? 'indicative') === 'final'
                                        ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                                        : 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300';
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $ppmpTypeClass }}">{{ $ppmpType }}</span>
                            </td>
                            <td class="py-3 pr-4">{{ $ppmp->division?->name }}</td>
                            <td class="py-3 pr-4">{{ $ppmp->fiscalYear?->year }}</td>
                            <td class="py-3 pr-4 text-right">₱{{ number_format($ppmp->totalLineAbc(), 2) }}</td>
                            <td class="py-3 pr-4"><x-status-badge :status="$ppmp->status" /></td>
                            <td class="py-3 pr-4 text-xs text-slate-500">{{ $ppmp->created_at?->format('M d, Y') }}</td>
                            <td class="py-3 pr-4 text-right"><x-button href="{{ route('ppmps.show', $ppmp) }}" variant="secondary" size="sm">View</x-button></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                No records match your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ppmps->hasPages())
            <div class="mt-4">{{ $ppmps->links() }}</div>
        @endif
    </x-card>
</div>
