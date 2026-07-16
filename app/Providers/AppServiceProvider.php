<?php

namespace App\Providers;

use App\Models\Bac\BacCalendarEvent;
use App\Models\Bac\PhilgepsPosting;
use App\Models\Bac\Procurement;
use App\Models\Budget\BudgetAllocation;
use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\Planning\AnnualProcurementPlan;
use App\Models\Planning\MarketScoping;
use App\Models\Planning\Ppmp;
use App\Models\Planning\PpmpConsolidation;
use App\Models\Planning\ProjectProposal;
use App\Models\Procurement\CertificateOfAvailabilityOfFunds;
use App\Models\Procurement\Payment;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Settings\SystemSetting;
use App\Models\Supplier\Bidder;
use App\Policies\AnnualProcurementPlanPolicy;
use App\Policies\BacCalendarEventPolicy;
use App\Policies\BidderPolicy;
use App\Policies\BudgetAllocationPolicy;
use App\Policies\CafPolicy;
use App\Policies\GaaPolicy;
use App\Policies\MarketScopingPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PhilgepsPostingPolicy;
use App\Policies\PpmpConsolidationPolicy;
use App\Policies\PpmpPolicy;
use App\Policies\ProjectProposalPolicy;
use App\Policies\ProcurementPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\PurchaseRequestPolicy;
use App\Support\NavAccess;
use App\Support\Roles;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Explicit policy map. Model namespaces (App\Models\Budget\*,
     * App\Models\Planning\*, ...) don't match Laravel's default
     * "{Model}Policy" auto-discovery convention, so every policy is wired
     * here for clarity and to keep discovery deterministic.
     */
    public array $policies = [
        GeneralAppropriationsAct::class => GaaPolicy::class,
        AnnualProcurementPlan::class => AnnualProcurementPlanPolicy::class,
        Ppmp::class => PpmpPolicy::class,
        PpmpConsolidation::class => PpmpConsolidationPolicy::class,
        MarketScoping::class => MarketScopingPolicy::class,
        ProjectProposal::class => ProjectProposalPolicy::class,
        PurchaseRequest::class => PurchaseRequestPolicy::class,
        CertificateOfAvailabilityOfFunds::class => CafPolicy::class,
        Procurement::class => ProcurementPolicy::class,
        Bidder::class => BidderPolicy::class,
        PurchaseOrder::class => PurchaseOrderPolicy::class,
        Payment::class => PaymentPolicy::class,
        BacCalendarEvent::class => BacCalendarEventPolicy::class,
        BudgetAllocation::class => BudgetAllocationPolicy::class,
        PhilgepsPosting::class => PhilgepsPostingPolicy::class,
    ];

    public function register(): void {}

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Cross-cutting gates that don't map to a single Eloquent model.
        Gate::define('access-settings', fn ($user) => $user->canAny([
            'settings.manage',
            'users.manage-roles',
            'users.create',
            'users.edit',
        ]));
        Gate::define('view-audit-trail', fn ($user) => $user->can('audit-trail.view'));
        Gate::define('manage-users', fn ($user) => $user->can('users.manage-roles'));
        Gate::define('users.reset-password', fn ($user) => $user->hasRole(Roles::SUPER_ADMIN));
        Gate::define('view-executive-dashboard', fn ($user) => $user->can('dashboard.view-executive'));

        Gate::before(function ($user, string $ability) {
            return $user->hasRole(Roles::SUPER_ADMIN) ? true : null;
        });

        Blade::if('canNav', fn (string $permission) => NavAccess::can($permission));
        Blade::if('cananyNav', function (...$permissions) {
            if (count($permissions) === 1 && is_array($permissions[0])) {
                $permissions = $permissions[0];
            }

            return NavAccess::canAny($permissions);
        });

        // Module activation is enforced in User::hasPermissionTo() rather
        // than a Gate callback: Spatie's Gate::before resolves permissions
        // before any application Gate::before/after can run, so the override
        // is the only point that can actually deny a deactivated module's
        // permission. See App\Support\ModuleRegistry + ModuleState.

        $this->applyRuntimeSettings();
        $this->ensurePublicStorageLink();
    }

    /**
     * Agency logos and other public uploads are served from
     * public/storage → storage/app/public. Create the symlink automatically
     * when missing so uploads work without a manual storage:link step.
     */
    protected function ensurePublicStorageLink(): void
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (file_exists($link) || ! is_dir($target)) {
            return;
        }

        try {
            symlink($target, $link);
        } catch (\Throwable) {
            // Some environments block symlinks; run `php artisan storage:link`.
        }
    }

    /**
     * Settings > Integrations lets a Super Admin override SMTP/AWS
     * connection details without a redeploy. We layer the saved
     * SystemSetting values on top of the .env-driven defaults on every
     * boot, so a value left blank in Settings simply falls back to .env.
     */
    protected function applyRuntimeSettings(): void
    {
        try {
            if (! Schema::hasTable('system_settings')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $mail = SystemSetting::group('mail');
        if (! empty($mail['host'] ?? null)) {
            config([
                'mail.mailers.smtp.host' => $mail['host'],
                'mail.mailers.smtp.port' => $mail['port'] ?? 587,
                'mail.mailers.smtp.username' => $mail['username'] ?? null,
                'mail.mailers.smtp.password' => $mail['password'] ?? null,
                'mail.mailers.smtp.encryption' => $mail['encryption'] ?? 'tls',
                'mail.from.address' => $mail['from_address'] ?? config('mail.from.address'),
                'mail.from.name' => $mail['from_name'] ?? config('mail.from.name'),
            ]);
        }

        $aws = SystemSetting::group('aws');
        if (! empty($aws['bucket'] ?? null)) {
            config([
                'filesystems.disks.s3.key' => $aws['access_key_id'] ?? config('filesystems.disks.s3.key'),
                'filesystems.disks.s3.secret' => $aws['secret_access_key'] ?? config('filesystems.disks.s3.secret'),
                'filesystems.disks.s3.region' => $aws['region'] ?? config('filesystems.disks.s3.region'),
                'filesystems.disks.s3.bucket' => $aws['bucket'],
                'filesystems.disks.s3.url' => $aws['url'] ?? config('filesystems.disks.s3.url'),
            ]);
        }
    }
}
