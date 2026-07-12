<div>
    <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3 dark:border-slate-800">
        @foreach($entities as $key => $def)
            <button type="button" wire:click="selectEntity('{{ $key }}')"
                    class="rounded-full px-3 py-1.5 text-xs font-medium transition
                        {{ $entity === $key ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
                {{ $def['label'] }}
            </button>
        @endforeach
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">{{ $config['label'] }}</h3>
        <x-button size="sm" wire:click="openCreate">Add Entry</x-button>
    </div>

    <x-table.filter-toolbar class="mt-3">Use the column filters below to search {{ strtolower($config['label']) }} records.</x-table.filter-toolbar>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead>
                <tr class="text-left text-xs uppercase text-slate-400">
                    @foreach($config['fields'] as $key => $field)
                        <th class="py-2 pr-4">{{ $field['label'] }}</th>
                    @endforeach
                    <th class="py-2 pr-4">Date Created</th>
                    <th class="py-2 pr-4"></th>
                </tr>
                <tr class="border-b border-slate-100 dark:border-slate-800">
                    @foreach($config['fields'] as $key => $field)
                        <th class="pb-3 pr-4 pt-1">
                            @if($field['type'] === 'boolean')
                                <x-table.filter-select model="columnFilters.{{ $key }}">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </x-table.filter-select>
                            @elseif($field['type'] === 'select')
                                <x-table.filter-select model="columnFilters.{{ $key }}">
                                    @foreach($options[$key] ?? [] as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </x-table.filter-select>
                            @elseif($field['type'] === 'select-static')
                                <x-table.filter-select model="columnFilters.{{ $key }}">
                                    @foreach($field['options'] as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </x-table.filter-select>
                            @elseif($field['type'] === 'date')
                                <input wire:model.live="columnFilters.{{ $key }}" type="date"
                                       class="w-full rounded border-slate-200 px-2 py-1 text-xs dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            @else
                                <x-table.filter-text model="columnFilters.{{ $key }}" />
                            @endif
                        </th>
                    @endforeach
                    <th class="pb-3 pr-4 pt-1"><x-table.filter-dates /></th>
                    <th class="pb-3 pr-4 pt-1"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($records as $record)
                    <tr wire:key="record-{{ $record->id }}">
                        @foreach($config['fields'] as $key => $field)
                            <td class="py-2.5 pr-4">
                                @if($field['type'] === 'boolean')
                                    @if($record->{$key})
                                        <span class="text-emerald-600">Yes</span>
                                    @else
                                        <span class="text-slate-400">No</span>
                                    @endif
                                @elseif($field['type'] === 'select')
                                    {{ $options[$key][$record->{$key}] ?? '—' }}
                                @elseif($field['type'] === 'select-static')
                                    {{ $field['options'][$record->{$key}] ?? $record->{$key} }}
                                @elseif($field['type'] === 'date')
                                    {{ $record->{$key}?->format('M d, Y') ?? '—' }}
                                @elseif($field['type'] === 'decimal')
                                    {{ $record->{$key} !== null ? '₱'.number_format($record->{$key}, 2) : '—' }}
                                @else
                                    <span class="line-clamp-1">{{ $record->{$key} ?? '—' }}</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="py-2.5 pr-4 text-xs text-slate-500">{{ $record->created_at?->format('M d, Y') }}</td>
                        <td class="py-2.5 pr-4 text-right">
                            <div class="flex justify-end gap-2">
                                <x-button size="sm" variant="secondary" wire:click="openEdit('{{ $record->id }}')">Edit</x-button>
                                <x-button size="sm" variant="danger" wire:click="delete('{{ $record->id }}')" wire:confirm="Remove this entry?" title="Delete"><x-icon name="trash" class="h-4 w-4" /></x-button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($config['fields']) + 2 }}" class="py-10 text-center text-sm text-slate-500">No records match your filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showFormModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" wire:click.self="closeModal">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $editingId ? 'Edit' : 'Add' }} {{ Str::singular($config['label']) }}</h3>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach($config['fields'] as $key => $field)
                        <div class="{{ in_array($field['type'], ['textarea']) ? 'sm:col-span-2' : '' }}">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ $field['label'] }}</label>

                            @if($field['type'] === 'boolean')
                                <label class="mt-2 inline-flex items-center gap-2">
                                    <input type="checkbox" wire:model="form.{{ $key }}" class="rounded border-slate-300 text-primary-600">
                                    <span class="text-sm text-slate-500">Enabled</span>
                                </label>
                            @elseif($field['type'] === 'textarea')
                                <textarea wire:model="form.{{ $key }}" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                            @elseif($field['type'] === 'select')
                                <select wire:model="form.{{ $key }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                    <option value="">— Select —</option>
                                    @foreach($options[$key] ?? [] as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            @elseif($field['type'] === 'select-static')
                                <select wire:model="form.{{ $key }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                    <option value="">— Select —</option>
                                    @foreach($field['options'] as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            @elseif($field['type'] === 'date')
                                <input wire:model="form.{{ $key }}" type="date" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            @elseif(in_array($field['type'], ['number', 'decimal']))
                                <input wire:model="form.{{ $key }}" type="number" step="{{ $field['type'] === 'decimal' ? '0.01' : '1' }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            @else
                                <input wire:model="form.{{ $key }}" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            @endif
                            @error('form.'.$key) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <x-button variant="secondary" wire:click="closeModal">Cancel</x-button>
                    <x-button wire:click="save">Save</x-button>
                </div>
            </div>
        </div>
    @endif
</div>
