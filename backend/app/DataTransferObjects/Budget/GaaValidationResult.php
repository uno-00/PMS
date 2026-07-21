<?php

namespace App\DataTransferObjects\Budget;

/**
 * Immutable result of running the "Validate Budget" function over an
 * uploaded GAA Excel template. Keeping this as a DTO (rather than an
 * associative array) gives services and Livewire components a typed,
 * self-documenting contract.
 */
final readonly class GaaValidationResult
{
    public function __construct(
        public bool $passed,
        public float $totalAmount,
        public int $lineItemCount,
        public array $errors = [],
    ) {}

    public static function failed(array $errors): self
    {
        return new self(passed: false, totalAmount: 0, lineItemCount: 0, errors: $errors);
    }

    public static function passed(float $totalAmount, int $lineItemCount): self
    {
        return new self(passed: true, totalAmount: $totalAmount, lineItemCount: $lineItemCount);
    }
}
