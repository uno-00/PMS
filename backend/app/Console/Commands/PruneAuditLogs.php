<?php

namespace App\Console\Commands;

use App\Models\Settings\SystemSetting;
use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

/**
 * Enforces the configurable "Audit Log Retention" security setting
 * (Settings > Security) by pruning activity_log entries older than the
 * configured number of days. Scheduled nightly (see routes/console.php).
 */
class PruneAuditLogs extends Command
{
    protected $signature = 'audit:prune';

    protected $description = 'Delete audit trail entries older than the configured retention period.';

    public function handle(): int
    {
        $days = (int) SystemSetting::get('security', 'audit_log_retention_days', 365);

        $deleted = Activity::query()->where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Pruned {$deleted} audit log entries older than {$days} days.");

        return self::SUCCESS;
    }
}
