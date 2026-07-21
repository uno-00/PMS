<?php

namespace App\Support;

/**
 * Lightweight wrapper so <x-status-badge> can render plain string statuses
 * (e.g. payment_status, delivery/inspection/acceptance results) that are
 * not backed by a Transitionable enum, keeping a single badge component
 * for the whole system.
 */
final class SimpleStatus
{
    private const COLORS = [
        'pending' => 'amber',
        'paid' => 'emerald',
        'delivered' => 'indigo',
        'partial' => 'amber',
        'rejected' => 'red',
        'passed' => 'emerald',
        'failed' => 'red',
        'submitted' => 'blue',
        'opened' => 'indigo',
        'disqualified' => 'red',
        'withdrawn' => 'slate',
        'recommended' => 'emerald',
        'not_recommended' => 'red',
        'scheduled' => 'amber',
        'completed' => 'emerald',
        'cancelled' => 'red',
        'answered' => 'emerald',
        'verified' => 'emerald',
        'suspended' => 'red',
        'processed' => 'indigo',
        'released' => 'emerald',
    ];

    public function __construct(private readonly ?string $value) {}

    public function label(): string
    {
        return $this->value ? ucfirst(str_replace('_', ' ', $this->value)) : '—';
    }

    public function color(): string
    {
        return self::COLORS[$this->value] ?? 'slate';
    }
}
