<?php

namespace Tests\Feature;

use App\Exports\GaaTemplateExport;
use App\Imports\GaaLineItemsImport;
use App\Imports\GaaWorkbookImport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class GaaTemplateDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_budget_officer_can_download_the_gaa_template(): void
    {
        $user = User::query()->where('email', 'budget.officer@pms.gov.ph')->firstOrFail();

        $response = $this->actingAs($user)
            ->get(route('gaa.template'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('GAA-Excel-Template', $response->headers->get('content-disposition'));
    }

    public function test_viewer_cannot_download_the_gaa_template(): void
    {
        $user = User::query()->where('email', 'viewer@pms.gov.ph')->firstOrFail();

        $this->actingAs($user)
            ->get(route('gaa.template'))
            ->assertForbidden();
    }

    public function test_template_line_items_sheet_has_the_exact_headers_the_importer_reads(): void
    {
        $sheets = (new GaaTemplateExport())->sheets();
        $lineItemsSheet = $sheets[0];

        $reflection = new \ReflectionMethod($lineItemsSheet, 'array');
        $rows = $reflection->invoke($lineItemsSheet);

        $this->assertSame(
            ['department_code', 'division_code', 'pap_code', 'uacs_code', 'fund_source_code', 'description', 'amount'],
            $rows[0],
            'Template row-1 headers must match GaaLineItemsImport expectations.'
        );

        $this->assertCount(3, $rows);
    }

    public function test_downloaded_template_imports_sample_rows_without_missing_required_errors(): void
    {
        $path = storage_path('framework/cache/test-gaa-template-import.xlsx');
        @mkdir(dirname($path), 0777, true);
        file_put_contents($path, Excel::raw(new GaaTemplateExport(), \Maatwebsite\Excel\Excel::XLSX));

        $import = new GaaLineItemsImport;
        Excel::import(new GaaWorkbookImport($import), $path);

        $this->assertCount(2, $import->rows, 'Sample template rows should import successfully.');
        $this->assertEmpty($import->errors, 'Template upload should not report missing required values: '.json_encode($import->errors));
        $this->assertSame(1_050_000.0, $import->totalAmount);
    }
}
