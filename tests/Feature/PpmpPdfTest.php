<?php

namespace Tests\Feature;

use App\Models\Planning\Ppmp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpmpPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_ppmp_pdf_renders_for_authorized_user(): void
    {
        $ppmp = Ppmp::query()->where('title', 'like', '%FY 2026%')->firstOrFail();
        $user = User::query()->where('email', 'planning.officer@pms.gov.ph')->firstOrFail();

        $this->actingAs($user)
            ->get(route('ppmps.print', $ppmp))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
