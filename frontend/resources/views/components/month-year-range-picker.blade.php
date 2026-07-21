@props([
    'fromMonthModel' => 'schedule_from_month',
    'fromYearModel' => 'schedule_from_year',
    'toMonthModel' => 'schedule_to_month',
    'toYearModel' => 'schedule_to_year',
    'years' => [],
])

@php
    $field = 'mt-1.5 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-base shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm';
    $months = \App\Support\MonthYearSchedule::monthOptions();
    $yearOptions = filled($years) ? $years : \App\Support\MonthYearSchedule::yearOptions();
@endphp

<div {{ $attributes->merge(['class' => 'space-y-3']) }}>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
            <p class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">From</p>
            <div class="grid grid-cols-2 gap-2">
                <select wire:model.live="{{ $fromMonthModel }}" class="{{ $field }}" aria-label="Schedule start month">
                    <option value="">Month</option>
                    @foreach($months as $value => $name)
                        <option value="{{ $value }}">{{ $name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="{{ $fromYearModel }}" class="{{ $field }}" aria-label="Schedule start year">
                    <option value="">Year</option>
                    @foreach($yearOptions as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <p class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">To</p>
            <div class="grid grid-cols-2 gap-2">
                <select wire:model.live="{{ $toMonthModel }}" class="{{ $field }}" aria-label="Schedule end month">
                    <option value="">Month</option>
                    @foreach($months as $value => $name)
                        <option value="{{ $value }}">{{ $name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="{{ $toYearModel }}" class="{{ $field }}" aria-label="Schedule end year">
                    <option value="">Year</option>
                    @foreach($yearOptions as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @error('schedule_from_month')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    @error('schedule_to_month')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
