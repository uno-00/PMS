<?php

namespace Tests\Feature;

use App\Models\Bac\BacCalendarEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BacCalendarCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_bac_secretariat_can_view_calendar(): void
    {
        $user = User::query()->where('email', 'bac.secretariat@pms.gov.ph')->firstOrFail();

        $this->actingAs($user)
            ->get(route('bac-calendar.index'))
            ->assertOk()
            ->assertSee('BAC Calendar');
    }

    public function test_end_user_cannot_access_calendar(): void
    {
        $user = User::query()->where('email', 'end.user@pms.gov.ph')->firstOrFail();

        $this->actingAs($user)
            ->get(route('bac-calendar.index'))
            ->assertForbidden();
    }

    public function test_secretariat_can_create_a_calendar_event(): void
    {
        $actor = User::query()->where('email', 'bac.secretariat@pms.gov.ph')->firstOrFail();

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Bac\CalendarEventForm::class)
            ->set('activity_type', 'bid_opening')
            ->set('title', 'Bid Opening — Supplies')
            ->set('scheduled_at', now()->addDays(3)->format('Y-m-d\TH:i'))
            ->set('status', 'scheduled')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('bac_calendar_events', [
            'title' => 'Bid Opening — Supplies',
            'activity_type' => 'bid_opening',
            'created_by' => $actor->id,
        ]);
    }

    public function test_secretariat_can_edit_a_calendar_event(): void
    {
        $actor = User::query()->where('email', 'bac.secretariat@pms.gov.ph')->firstOrFail();
        $event = BacCalendarEvent::factory()->create(['status' => 'scheduled']);

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Bac\CalendarEventForm::class, ['calendarEvent' => $event])
            ->set('title', 'Rescheduled Bid Opening')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('Rescheduled Bid Opening', $event->fresh()->title);
    }

    public function test_secretariat_can_delete_a_calendar_event(): void
    {
        $actor = User::query()->where('email', 'bac.secretariat@pms.gov.ph')->firstOrFail();
        $event = BacCalendarEvent::factory()->create();

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Bac\CalendarIndex::class)
            ->call('delete', $event->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('bac_calendar_events', ['id' => $event->id]);
    }
}
