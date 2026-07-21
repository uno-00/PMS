<?php

namespace Database\Factories\Bac;

use App\Enums\ProcurementCaseStatus;
use App\Models\Bac\Procurement;
use App\Models\Planning\Ppmp;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Procurement>
 */
class ProcurementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'case_no' => 'BAC-'.now()->year.'-'.strtoupper(Str::random(6)),
            'title' => fake()->sentence(5),
            'abc' => fake()->randomFloat(2, 50000, 2000000),
            'status' => ProcurementCaseStatus::Planning,
            'remarks' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Resolve the NOT NULL purchase_request_id foreign key with a real
     * PurchaseRequest built from seeded reference data (fiscal year,
     * division, PPMP), so a Procurement can be created standalone.
     */
    public function withPr(): static
    {
        return $this->state(function () {
            $fy = FiscalYear::query()->where('is_current', true)->first()
                ?? FiscalYear::query()->orderByDesc('year')->first();
            $divisionId = Division::query()->value('id');

            $ppmp = $fy ? Ppmp::query()->firstOrCreate(
                ['fiscal_year_id' => $fy->id, 'title' => 'Factory PPMP '.Str::random(6)],
                ['division_id' => $divisionId, 'status' => 'approved', 'total_abc' => 100000]
            ) : null;

            $pr = PurchaseRequest::query()->create([
                'pr_no' => 'PR-FCT-'.strtoupper(Str::random(6)),
                'fiscal_year_id' => $fy?->id,
                'division_id' => $divisionId,
                'ppmp_id' => $ppmp?->id,
                'purpose' => 'Factory purchase request.',
                'total_amount' => 50000,
                'status' => 'approved',
            ]);

            return ['purchase_request_id' => $pr->id];
        });
    }
}
