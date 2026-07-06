<?php

namespace Tests\Feature;

use App\Enums\BacRosterRole;
use App\Enums\TwGCategory;
use App\Enums\TwGDesignationType;
use App\Models\Bac\BacMember;
use App\Models\Bac\BacTwGAssignment;
use App\Models\User;
use App\Services\Bac\BacMemberService;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BacMemberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_bac_secretariat_can_view_bac_members_page(): void
    {
        $user = User::query()->where('email', 'bac.secretariat@pms.gov.ph')->firstOrFail();

        $this->actingAs($user)
            ->get(route('bac-members.index'))
            ->assertOk()
            ->assertSee('BAC Members & TWG')
            ->assertSee('TWG Primary — Infrastructure');
    }

    public function test_end_user_cannot_access_bac_members_page(): void
    {
        $user = User::query()->where('email', 'end.user@pms.gov.ph')->firstOrFail();

        $this->actingAs($user)
            ->get(route('bac-members.index'))
            ->assertForbidden();
    }

    public function test_secretariat_can_create_bac_member_with_primary_twg_assignment(): void
    {
        $actor = User::query()->where('email', 'bac.secretariat@pms.gov.ph')->firstOrFail();
        $candidate = User::query()->where('email', 'end.user@pms.gov.ph')->firstOrFail();

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Bac\MemberIndex::class)
            ->call('openCreate')
            ->set('user_id', $candidate->id)
            ->set('bac_role', BacRosterRole::Member->value)
            ->set('designation', 'TWG Alternate — Services')
            ->set('twg_assignments.services', TwGDesignationType::Alternate->value)
            ->call('save')
            ->assertHasNoErrors();

        $member = BacMember::query()->where('user_id', $candidate->id)->first();
        $this->assertNotNull($member);
        $this->assertSame(TwGDesignationType::Alternate, $member->twgDesignationFor(TwGCategory::Services));
        $this->assertTrue($candidate->fresh()->hasRole(Roles::BAC_MEMBER));
    }

    public function test_only_one_active_chairperson_is_allowed(): void
    {
        $actor = User::query()->where('email', 'bac.secretariat@pms.gov.ph')->firstOrFail();
        $candidate = User::query()->where('email', 'planning.officer@pms.gov.ph')->firstOrFail();

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Bac\MemberIndex::class)
            ->call('openCreate')
            ->set('user_id', $candidate->id)
            ->set('bac_role', BacRosterRole::Chairperson->value)
            ->call('save')
            ->assertHasErrors(['bac_role']);
    }

    public function test_seeder_populates_primary_and_alternate_for_each_twg_category(): void
    {
        foreach (TwGCategory::cases() as $category) {
            $this->assertSame(1, BacTwGAssignment::query()
                ->where('category', $category->value)
                ->where('designation_type', TwGDesignationType::Primary->value)
                ->count(), "Missing primary TWG for {$category->value}");

            $this->assertSame(1, BacTwGAssignment::query()
                ->where('category', $category->value)
                ->where('designation_type', TwGDesignationType::Alternate->value)
                ->count(), "Missing alternate TWG for {$category->value}");
        }

        $roster = app(BacMemberService::class)->twgRosterByCategory();
        $this->assertSame('twg.infra.primary@pms.gov.ph', $roster['infrastructure']['primary']->user->email);
        $this->assertSame('twg.services.alt@pms.gov.ph', $roster['services']['alternate']->user->email);
    }

    public function test_assigning_new_primary_replaces_previous_holder(): void
    {
        $actor = User::query()->where('email', 'bac.secretariat@pms.gov.ph')->firstOrFail();
        $candidate = User::query()->where('email', 'planning.officer@pms.gov.ph')->firstOrFail();

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Bac\MemberIndex::class)
            ->call('openCreate')
            ->set('user_id', $candidate->id)
            ->set('bac_role', BacRosterRole::Member->value)
            ->set('twg_assignments.infrastructure', TwGDesignationType::Primary->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(1, BacTwGAssignment::query()
            ->where('category', TwGCategory::Infrastructure->value)
            ->where('designation_type', TwGDesignationType::Primary->value)
            ->count());

        $this->assertSame(
            $candidate->id,
            BacTwGAssignment::query()
                ->where('category', TwGCategory::Infrastructure->value)
                ->where('designation_type', TwGDesignationType::Primary->value)
                ->first()
                ->bacMember
                ->user_id
        );
    }
}
