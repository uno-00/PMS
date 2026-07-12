<?php

namespace Tests\Unit;

use App\Services\Planning\ProjectProposalAiDraftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProjectProposalAiDraftServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['services.openai.api_key' => null]);
    }

    public function test_template_drafter_generates_all_narrative_sections(): void
    {
        $service = app(ProjectProposalAiDraftService::class);

        $drafts = $service->draftAll([
            'agency_name' => 'National Museum',
            'division_name' => 'Systems Development Division',
            'fiscal_year' => 2026,
            'title' => 'Desktop Computer Refresh FY 2026',
            'project_type' => 'Goods',
            'schedule' => 'March 2026 – April 2026',
            'venue_area' => 'Systems Development Division',
            'total_cost' => 1100000,
            'fund_source_text' => 'MOOE',
            'proponent' => 'Maria Clara Santos',
        ]);

        $this->assertArrayHasKey('rationale', $drafts);
        $this->assertStringContainsString('Desktop Computer Refresh FY 2026', $drafts['rationale']);
        $this->assertStringContainsString('<table>', $drafts['budgetary_requirement']);
        $this->assertStringContainsString('MOOE', $drafts['fund_source_narrative']);
        $this->assertFalse($service->isAiConfigured());
    }

    public function test_openai_drafter_parses_json_response(): void
    {
        config(['services.openai.api_key' => 'test-key']);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'rationale' => '<p>AI rationale paragraph.</p>',
                            'objectives' => '<ul><li>Objective one</li></ul>',
                            'target_schedule' => '<p>Q1 2026 procurement</p>',
                            'budgetary_requirement' => '<p>₱100,000.00</p>',
                            'fund_source_narrative' => '<p>MOOE charge</p>',
                        ]),
                    ],
                ]],
            ], 200),
        ]);

        $service = app(ProjectProposalAiDraftService::class);

        $drafts = $service->draftAll([
            'title' => 'Sample Project',
            'project_type' => 'Goods',
            'total_cost' => 100000,
        ]);

        $this->assertSame('<p>AI rationale paragraph.</p>', $drafts['rationale']);
        $this->assertTrue($service->isAiConfigured());
        $this->assertFalse($service->usedTemplateFallback());
    }

    public function test_openai_connection_failure_falls_back_to_templates(): void
    {
        config(['services.openai.api_key' => 'test-key']);

        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('cURL error 6: Could not resolve host: api.openai.com');
        });

        $service = app(ProjectProposalAiDraftService::class);

        $drafts = $service->draftAll([
            'title' => 'Sample Project',
            'project_type' => 'Goods',
            'total_cost' => 100000,
        ]);

        $this->assertStringContainsString('Sample Project', $drafts['rationale']);
        $this->assertTrue($service->usedTemplateFallback());
    }

    public function test_openai_api_error_falls_back_to_templates(): void
    {
        config(['services.openai.api_key' => 'test-key']);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'error' => ['message' => 'You exceeded your current quota, please check your plan and billing details.'],
            ], 429),
        ]);

        $service = app(ProjectProposalAiDraftService::class);

        $drafts = $service->draftAll([
            'title' => 'Quota Fallback Project',
            'project_type' => 'Goods',
            'total_cost' => 100000,
        ]);

        $this->assertStringContainsString('Quota Fallback Project', $drafts['rationale']);
        $this->assertTrue($service->usedTemplateFallback());
    }
}
