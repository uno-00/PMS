<?php

namespace Tests\Feature;

use App\Livewire\Gaa\Upload;
use App\Models\Settings\FiscalYear;
use App\Models\User;
use App\Services\Budget\GaaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class GaaUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('documents');
    }

    public function test_gaa_service_uploads_and_parses_template_file(): void
    {
        $user = User::query()->where('email', 'budget.officer@pms.gov.ph')->firstOrFail();
        $fiscalYear = FiscalYear::factory()->create(['year' => 2099, 'is_current' => false]);

        $path = storage_path('framework/cache/test-gaa-upload-service.xlsx');
        @mkdir(dirname($path), 0777, true);
        file_put_contents($path, Excel::raw(new \App\Exports\GaaTemplateExport(), \Maatwebsite\Excel\Excel::XLSX));

        $file = new UploadedFile($path, 'gaa-template.xlsx', null, null, true);

        $gaa = app(GaaService::class)->upload($fiscalYear, $file, $user, 'RA-TEST-2026');

        $this->assertSame('RA-TEST-2026', $gaa->reference_no);
        $this->assertCount(2, $gaa->lineItems);
        $this->assertSame(1_050_000.0, (float) $gaa->total_amount);
    }

    public function test_upload_component_shows_friendly_error_for_invalid_json_upload_response(): void
    {
        $user = User::query()->where('email', 'budget.officer@pms.gov.ph')->firstOrFail();
        $fiscalYear = FiscalYear::query()->firstOrFail();

        Livewire::actingAs($user)
            ->test(Upload::class)
            ->call('_uploadErrored', 'file', '<br /><b>Warning</b>: file too large', false)
            ->assertHasErrors(['file']);
    }
}
