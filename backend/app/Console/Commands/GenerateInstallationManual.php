<?php

namespace App\Console\Commands;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;

class GenerateInstallationManual extends Command
{
    protected $signature = 'docs:installation-manual
                            {--output=database/INSTALLATION_MANUAL.pdf : Path relative to project root}';

    protected $description = 'Generate the system installation manual as a PDF in the database folder';

    public function handle(): int
    {
        $relativePath = $this->option('output');
        $absolutePath = base_path($relativePath);

        $directory = dirname($absolutePath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $pdf = Pdf::loadView('pdf.installation-manual', [
            'generatedAt' => now()->timezone(config('app.timezone'))->format('F d, Y'),
        ])->setPaper('a4', 'portrait');

        file_put_contents($absolutePath, $pdf->output());

        $sizeKb = round(filesize($absolutePath) / 1024, 1);
        $this->info("Installation manual written to {$relativePath} ({$sizeKb} KB)");

        return self::SUCCESS;
    }
}
