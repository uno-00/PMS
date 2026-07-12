<?php

namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

trait InteractsWithTableFilters
{
    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public function updating($property): void
    {
        if ($property !== 'page') {
            $this->resetPage();
        }
    }

    /** @param  array<int, string>  $properties */
    protected function resetTableFilters(array $properties): void
    {
        $this->reset(...array_merge($properties, ['dateFrom', 'dateTo']));
    }

    protected function applyCreatedAtFilter(Builder $query, string $column = 'created_at'): Builder
    {
        return $query
            ->when($this->dateFrom !== '', fn (Builder $q) => $q->whereDate($column, '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $q) => $q->whereDate($column, '<=', $this->dateTo));
    }

    protected function applyLikeFilter(Builder $query, string $column, ?string $value): Builder
    {
        if ($value === null || $value === '') {
            return $query;
        }

        return $query->where($column, 'like', '%'.$value.'%');
    }

    protected function applyExactFilter(Builder $query, string $column, mixed $value): Builder
    {
        if ($value === null || $value === '') {
            return $query;
        }

        return $query->where($column, $value);
    }

    protected function applyAmountFilter(Builder $query, string $column, ?string $value): Builder
    {
        if ($value === null || $value === '') {
            return $query;
        }

        $amount = (float) str_replace(',', '', $value);

        if ($amount <= 0) {
            return $query;
        }

        return $query->where($column, $amount);
    }
}
