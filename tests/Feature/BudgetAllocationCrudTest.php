<?php

namespace Tests\Feature;

use App\Models\Budget\BudgetAllocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BudgetAllocationCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_budget_officer_can_view_allocations(): void
    {
        $user = User::query()->where('email', 'budget.officer@pms.gov.ph')->firstOrFail();

        $this->actingAs($user)
            ->get(route('budget-allocations.index'))
            ->assertOk()
            ->assertSee('Budget Allocation');
    }

    public function test_end_user_cannot_access_allocations(): void
    {
        $user = User::query()->where('email', 'end.user@pms.gov.ph')->firstOrFail();

        $this->actingAs($user)
            ->get(route('budget-allocations.index'))
            ->assertForbidden();
    }

    public function test_budget_officer_can_edit_allocation_amount(): void
    {
        $actor = User::query()->where('email', 'budget.officer@pms.gov.ph')->firstOrFail();
        $allocation = BudgetAllocation::factory()->create([
            'allocated_amount' => 100000,
            'utilized_amount' => 0,
        ]);

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Budget\AllocationForm::class, ['allocation' => $allocation])
            ->set('allocated_amount', 75000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('75000.00', $allocation->fresh()->allocated_amount);
    }

    public function test_edit_refuses_overallocation_against_parent(): void
    {
        $actor = User::query()->where('email', 'budget.officer@pms.gov.ph')->firstOrFail();
        $parent = BudgetAllocation::factory()->create(['allocated_amount' => 100000, 'utilized_amount' => 0]);
        $child = BudgetAllocation::factory()->create([
            'parent_id' => $parent->id,
            'allocated_amount' => 60000,
            'utilized_amount' => 0,
        ]);
        // Parent remaining = 100000 - 60000 = 40000. Child can grow to 40000+60000 = 100000.
        // Asking for 200000 must be refused.
        Livewire::actingAs($actor)
            ->test(\App\Livewire\Budget\AllocationForm::class, ['allocation' => $child])
            ->set('allocated_amount', 200000)
            ->call('save')
            ->assertHasErrors(['allocated_amount']);

        $this->assertEquals('60000.00', $child->fresh()->allocated_amount);
    }

    public function test_cannot_delete_allocation_with_children(): void
    {
        $actor = User::query()->where('email', 'budget.officer@pms.gov.ph')->firstOrFail();
        $parent = BudgetAllocation::factory()->create(['allocated_amount' => 100000, 'utilized_amount' => 0]);
        BudgetAllocation::factory()->create(['parent_id' => $parent->id, 'allocated_amount' => 50000]);

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Budget\AllocationIndex::class)
            ->call('delete', $parent->id)
            ->assertHasNoErrors(); // service refuses softly via session flash

        $this->assertDatabaseHas('budget_allocations', ['id' => $parent->id]);
    }

    public function test_cannot_delete_allocation_with_utilized_funds(): void
    {
        $actor = User::query()->where('email', 'budget.officer@pms.gov.ph')->firstOrFail();
        $allocation = BudgetAllocation::factory()->create([
            'allocated_amount' => 100000,
            'utilized_amount' => 25000,
        ]);

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Budget\AllocationIndex::class)
            ->call('delete', $allocation->id);

        $this->assertDatabaseHas('budget_allocations', ['id' => $allocation->id]);
    }

    public function test_can_delete_leaf_allocation_without_utilization(): void
    {
        $actor = User::query()->where('email', 'budget.officer@pms.gov.ph')->firstOrFail();
        $allocation = BudgetAllocation::factory()->create([
            'allocated_amount' => 100000,
            'utilized_amount' => 0,
        ]);

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Budget\AllocationIndex::class)
            ->call('delete', $allocation->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('budget_allocations', ['id' => $allocation->id]);
    }
}
