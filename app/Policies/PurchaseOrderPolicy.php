<?php

namespace App\Policies;

class PurchaseOrderPolicy extends BasePolicy
{
    use ModuleCrudPolicy;

    protected string $module = 'purchase-order';
}
