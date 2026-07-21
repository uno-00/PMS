<?php

namespace App\Console\Commands;

use App\Support\SqliteToMysqlExporter;
use Illuminate\Console\Command;

class ExportMysqlDatabase extends Command
{
    protected $signature = 'db:export-mysql
                            {--source= : Path to the SQLite database file}
                            {--output= : Output .sql file path}
                            {--database=pms_procurement : Target MySQL database name}';

    protected $description = 'Export the database to MySQL-compatible SQL in the database folder';

    public function handle(): int
    {
        $source = $this->option('source') ?: database_path('database.sqlite');
        $output = $this->option('output') ?: database_path('pms_procurement.sql');
        $database = (string) $this->option('database');

        if (! is_file($source)) {
            $this->error("Source database not found: {$source}");

            return self::FAILURE;
        }

        $this->info("Exporting from {$source}...");

        $sql = (new SqliteToMysqlExporter($source, $database))->export();

        if (! is_dir(dirname($output))) {
            mkdir(dirname($output), 0755, true);
        }

        file_put_contents($output, $sql);

        $lines = substr_count($sql, "\n") + 1;
        $size = number_format(strlen($sql) / 1024, 1);

        $this->info("MySQL dump saved to {$output}");
        $this->line("  {$lines} lines, {$size} KB");

        return self::SUCCESS;
    }
}
