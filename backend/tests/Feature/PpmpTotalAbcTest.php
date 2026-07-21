<?php

namespace Tests\Feature;

use App\Enums\PpmpDocumentType;
use App\Enums\PpmpStatus;
use App\Livewire\Planning\PpmpForm;
use App\Models\Planning\Ppmp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PpmpTotalAbcTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_indicative_ppmp_index_shows_sum_of_line_item_abc(): void
    {
        $user = User::query()->where('email', 'planning.officer@pms.gov.ph')->firstOrFail();

        $ppmp = Ppmp::query()
            ->where('document_type', PpmpDocumentType::Indicative)
            ->has('items', '>=', 2)
            ->first();

        if (! $ppmp) {
            $ppmp = Ppmp::query()
                ->where('document_type', PpmpDocumentType::Indicative)
                ->firstOrFail();

            $ppmp->items()->create([
                'item_no' => 2,
                'item_name' => 'Second line item',
                'unit' => 'Lot',
                'quantity' => 1,
                'estimated_unit_cost' => 50000,
                'abc' => 50000,
            ]);
        }

        $ppmp->update(['total_abc' => 999999]);
        $expected = round($ppmp->fresh()->items->sum(fn ($item) => $item->lineAbc()), 2);

        $this->actingAs($user)
            ->get(route('ppmps.indicative'))
            ->assertOk()
            ->assertSee('₱'.number_format($expected, 2), false);
    }

    public function test_saving_ppmp_items_recalculates_total_abc_from_line_items(): void
    {
        $user = User::query()->where('email', 'planning.officer@pms.gov.ph')->firstOrFail();

        $ppmp = Ppmp::query()
            ->where('document_type', PpmpDocumentType::Indicative)
            ->whereIn('status', [PpmpStatus::Draft, PpmpStatus::ReturnedForRevision])
            ->first();

        if (! $ppmp) {
            $this->markTestSkipped('No editable indicative PPMP in seed data.');
        }

        $items = $ppmp->items()->orderBy('item_no')->get()->map(fn ($item) => [
            'id' => $item->id,
            'expense_class' => $item->expense_class?->value ?? 'mooe',
            'project_type' => $item->project_type?->value ?? 'goods',
            'item_name' => $item->item_name,
            'description' => $item->description,
            'specification' => $item->specification,
            'unit' => $item->unit,
            'quantity' => '2',
            'estimated_unit_cost' => '100000',
            'schedule_start' => optional($item->schedule_start)->format('Y-m-d'),
            'schedule_end' => optional($item->schedule_end)->format('Y-m-d'),
            'mode_of_procurement_id' => $item->mode_of_procurement_id,
            'pre_procurement_conference' => $item->pre_procurement_conference?->value ?? '',
            'budget_allocation_id' => $item->budget_allocation_id,
            'remarks' => $item->remarks,
        ])->all();

        Livewire::actingAs($user)
            ->test(PpmpForm::class, ['ppmp' => $ppmp])
            ->set('items', $items)
            ->call('save')
            ->assertHasNoErrors();

        $ppmp->refresh();
        $expected = round(collect($items)->sum(fn (array $row) => PpmpForm::lineAbc($row)), 2);

        $this->assertSame($expected, (float) $ppmp->total_abc);
    }
}
