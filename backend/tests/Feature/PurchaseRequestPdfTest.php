<?php

namespace Tests\Feature;

use App\Models\Procurement\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseRequestPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_purchase_request_pdf_renders_for_authorized_user(): void
    {
        $pr = PurchaseRequest::query()->firstOrFail();
        $user = User::query()->where('email', 'planning.officer@pms.gov.ph')->firstOrFail();

        $this->actingAs($user)
            ->get(route('purchase-requests.print', $pr))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
