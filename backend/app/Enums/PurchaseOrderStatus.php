<?php

namespace App\Enums;

use App\Support\Workflow\Transitionable;

enum PurchaseOrderStatus: string implements Transitionable
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Delivered = 'delivered';
    case Inspected = 'inspected';
    case Accepted = 'accepted';
    case Invoiced = 'invoiced';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Approved, self::Cancelled],
            self::Approved => [self::Delivered, self::Cancelled],
            self::Delivered => [self::Inspected],
            self::Inspected => [self::Accepted],
            self::Accepted => [self::Invoiced],
            self::Invoiced => [self::Paid],
            self::Paid, self::Cancelled => [],
        };
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'slate',
            self::Approved, self::Delivered, self::Inspected, self::Accepted, self::Invoiced => 'amber',
            self::Paid => 'emerald',
            self::Cancelled => 'red',
        };
    }
}
