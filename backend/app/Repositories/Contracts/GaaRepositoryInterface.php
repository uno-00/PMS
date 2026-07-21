<?php

namespace App\Repositories\Contracts;

use App\Models\Budget\GeneralAppropriationsAct;

interface GaaRepositoryInterface extends RepositoryInterface
{
    public function forFiscalYear(string $fiscalYearId): ?GeneralAppropriationsAct;
}
