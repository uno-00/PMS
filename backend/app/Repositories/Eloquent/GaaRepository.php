<?php

namespace App\Repositories\Eloquent;

use App\Models\Budget\GeneralAppropriationsAct;
use App\Repositories\Contracts\GaaRepositoryInterface;

class GaaRepository extends BaseRepository implements GaaRepositoryInterface
{
    protected array $relations = ['fiscalYear', 'uploader', 'approver'];

    public function __construct(GeneralAppropriationsAct $model)
    {
        parent::__construct($model);
    }

    public function forFiscalYear(string $fiscalYearId): ?GeneralAppropriationsAct
    {
        return $this->query()->where('fiscal_year_id', $fiscalYearId)->first();
    }
}
