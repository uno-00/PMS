<?php

namespace Tests\Feature;

use App\Models\Settings\AgencyProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AgencyBrandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');
    }

    public function test_internal_layout_displays_agency_name_in_sidebar(): void
    {
        $user = User::query()->where('email', 'superadmin@pms.gov.ph')->firstOrFail();
        $agency = AgencyProfile::current();

        $this->actingAs($user)
            ->get(route('dashboard.executive'))
            ->assertOk()
            ->assertSee($agency->displayName(), false)
            ->assertSee($agency->acronym ?? '', false);
    }

    public function test_login_page_displays_agency_name(): void
    {
        $agency = AgencyProfile::current();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee($agency->displayName(), false);
    }

    public function test_agency_logo_upload_is_saved_and_served_from_public_storage(): void
    {
        $user = User::query()->where('email', 'superadmin@pms.gov.ph')->firstOrFail();
        $profileId = AgencyProfile::query()->value('id');
        $logo = UploadedFile::fake()->image('agency-logo.png', 120, 120);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\AgencyProfileTab::class)
            ->set('logo', $logo)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('settings.index', ['tab' => 'profile']));

        $profile = AgencyProfile::query()->findOrFail($profileId);
        AgencyProfile::resetCached();

        $this->assertNotNull($profile->logo_path);
        Storage::disk('public')->assertExists($profile->logo_path);
        $this->assertSame('/storage/'.$profile->logo_path, $profile->logoUrl());
    }
}
