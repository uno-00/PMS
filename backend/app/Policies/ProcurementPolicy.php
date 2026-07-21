<?php

namespace App\Policies;

class ProcurementPolicy extends BasePolicy
{
    use ModuleCrudPolicy;

    protected string $module = 'bac-calendar';
}
