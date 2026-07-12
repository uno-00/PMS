<?php

namespace App\Policies;

use App\Models\Procurement\Payment;
use App\Models\User;

/**
 * Authorization for the Payment monitoring module. View/create map directly
 * to the "payment" permission keys; update/delete are additionally gated on
 * the disbursement not having been released yet, since a released payment is
 * a settled, audited transaction that must not be silently altered.
 */
class PaymentPolicy extends BasePolicy
{
    use ModuleCrudPolicy;

    protected string $module = 'payment';

    public function update(User $user, Payment $payment): bool
    {
        return $this->canUpdate($user) && $payment->status !== 'released';
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $this->canDelete($user) && $payment->status !== 'released';
    }
}
