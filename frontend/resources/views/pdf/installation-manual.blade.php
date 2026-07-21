<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Installation Manual — {{ config('app.name') }}</title>
    <style>
        @page { margin: 70px 45px 60px 45px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; color: #1e293b; line-height: 1.45; }
        h1 { font-size: 20px; color: #1e40af; margin: 0 0 6px; }
        h2 { font-size: 13px; color: #1e40af; border-bottom: 1px solid #bfdbfe; padding-bottom: 4px; margin: 18px 0 8px; page-break-after: avoid; }
        h3 { font-size: 11px; color: #334155; margin: 12px 0 6px; page-break-after: avoid; }
        p { margin: 0 0 8px; }
        ul, ol { margin: 0 0 10px 18px; padding: 0; }
        li { margin-bottom: 4px; }
        table { border-collapse: collapse; width: 100%; margin: 8px 0 12px; font-size: 9.5px; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #eff6ff; color: #1e40af; font-weight: bold; }
        code, pre { font-family: 'DejaVu Sans Mono', monospace; font-size: 9px; background: #f8fafc; }
        pre { border: 1px solid #e2e8f0; padding: 8px; white-space: pre-wrap; word-wrap: break-word; margin: 6px 0 10px; }
        .cover { text-align: center; padding-top: 120px; page-break-after: always; }
        .cover .subtitle { font-size: 13px; color: #475569; margin-top: 8px; }
        .cover .meta { margin-top: 40px; font-size: 10px; color: #64748b; }
        .badge { display: inline-block; background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .muted { color: #64748b; }
        .page-break { page-break-before: always; }
        .toc li { margin-bottom: 6px; }
    </style>
</head>
<body>

<div class="cover">
    <div class="badge">RA 12009 Compliant</div>
    <h1 style="margin-top: 14px;">Enterprise Procurement<br>Management System</h1>
    <p class="subtitle">Complete Installation & Deployment Manual</p>
    <p class="meta">
        Version 1.0 &mdash; Generated {{ $generatedAt }}<br>
        {{ config('app.name') }}
    </p>
</div>

<h2>Table of Contents</h2>
<ol class="toc">
    <li>System Overview</li>
    <li>Hardware & Software Requirements</li>
    <li>Local Development Installation</li>
    <li>Database Setup & Sample Data</li>
    <li>Running the Application</li>
    <li>Docker Installation</li>
    <li>Production Deployment (AWS)</li>
    <li>Test Accounts</li>
    <li>Verification & Testing</li>
    <li>Troubleshooting</li>
    <li>Additional Documentation</li>
</ol>

<h2>1. System Overview</h2>
<p>
    The Enterprise Procurement Management System (PMS) is a full lifecycle platform for Philippine
    Government Agencies, compliant with <strong>RA 12009</strong> (New Government Procurement Act).
    It covers procurement from the DBM General Appropriations Act (GAA) through payment, with complete
    audit trail, configurable workflows, and role-based access control.
</p>
<p><strong>Procurement lifecycle:</strong></p>
<p class="muted" style="font-size: 9px;">
    GAA &rarr; Budget Allocation &rarr; APP &rarr; PPMP &rarr; Purchase Request &rarr; CAF &rarr;
    BAC Procurement &rarr; PhilGEPS Posting &rarr; Bidder Portal &rarr; Bidding &rarr; Award &rarr;
    Notice to Proceed &rarr; Purchase Order &rarr; Delivery &rarr; Inspection &rarr; Acceptance &rarr;
    Payment &rarr; Reports & Analytics
</p>
<p><strong>Technology stack:</strong> Laravel 12, PHP 8.3, Livewire 3, TailwindCSS 4, AlpineJS,
MySQL 8 (SQLite for local sandbox), AWS S3, Laravel Queue & Scheduler, Spatie Permission,
DomPDF, Laravel Excel, UUID primary keys, Service Layer + Event-Driven Architecture.</p>

<h2>2. Hardware & Software Requirements</h2>
<h3>Minimum (local development)</h3>
<ul>
    <li>PHP 8.3+ with extensions: pdo_mysql (or pdo_sqlite), mbstring, bcmath, intl, gd, zip, fileinfo</li>
    <li>Composer 2.x</li>
    <li>Node.js 20+ and npm</li>
    <li>MySQL 8.0+ <em>or</em> SQLite (zero-config sandbox)</li>
    <li>4 GB RAM recommended; 2 GB minimum</li>
</ul>
<h3>Recommended (production)</h3>
<ul>
    <li>MySQL 8.0 Multi-AZ (RDS) or equivalent managed database</li>
    <li>Redis for cache/queue at scale</li>
    <li>AWS S3 private bucket for document storage</li>
    <li>SMTP relay for email notifications (NOA, NTP, CAF, PhilGEPS alerts)</li>
    <li>HTTPS termination (ALB + ACM certificate)</li>
</ul>

<h2>3. Local Development Installation</h2>
<h3>Step 1 — Clone and install dependencies</h3>
<pre>git clone &lt;repo-url&gt; pms-procurement
cd pms-procurement
composer install
npm install</pre>

<h3>Step 2 — Environment configuration</h3>
<pre>cp .env.example .env
php artisan key:generate</pre>
<p>Edit <code>.env</code> and configure at minimum:</p>
<table>
    <tr><th>Variable</th><th>Local sandbox</th><th>MySQL production-like</th></tr>
    <tr><td>DB_CONNECTION</td><td>sqlite</td><td>mysql</td></tr>
    <tr><td>DB_DATABASE</td><td>database/database.sqlite</td><td>pms_procurement</td></tr>
    <tr><td>SESSION_DRIVER</td><td>file</td><td>database or redis</td></tr>
    <tr><td>CACHE_STORE</td><td>file</td><td>database or redis</td></tr>
    <tr><td>DOCUMENTS_DISK_DRIVER</td><td>local</td><td>s3</td></tr>
    <tr><td>APP_URL</td><td>http://localhost:8000</td><td>https://your-agency.gov.ph</td></tr>
</table>
<p class="muted">For SQLite: <code>touch database/database.sqlite</code>. Match APP_URL to your dev-server port (e.g. :8001).</p>

<h3>Step 3 — Build frontend assets</h3>
<pre>php artisan storage:link
npm run build</pre>
<p>For active development with hot reload: <code>npm run dev</code></p>

<h2>4. Database Setup & Sample Data</h2>
<h3>Option A — Migrations & seeders (recommended)</h3>
<pre>php artisan migrate --seed</pre>
<p>This creates all tables and seeds:</p>
<ul>
    <li>19 roles / 102 permissions</li>
    <li>Organizational structure (departments, divisions, offices, cost centers)</li>
    <li>Reference data (modes of procurement, fund sources, UACS, PAPs, holidays)</li>
    <li>18 internal test accounts + 1 Bidder Portal account</li>
    <li>Sample FY2026 procurement walkthrough (GAA &rarr; CAF)</li>
</ul>

<h3>Option B — Restore from SQL dump (SQLite)</h3>
<p>A pre-exported database snapshot is included at <code>database/pms_procurement.sql</code>.</p>
<pre>rm -f database/database.sqlite
sqlite3 database/database.sqlite &lt; database/pms_procurement.sql</pre>
<p class="muted">Note: this SQL dump is SQLite-specific and is not directly importable into MySQL.</p>

<h2>5. Running the Application</h2>
<h3>All-in-one dev stack (recommended)</h3>
<pre>composer run dev</pre>
<p>Starts: web server (:8000), queue worker, log tailer (Pail), and Vite dev server.</p>

<h3>Individual processes</h3>
<pre>php artisan serve
php artisan queue:listen
php artisan schedule:work</pre>

<h3>Access URLs</h3>
<table>
    <tr><th>Portal</th><th>URL</th></tr>
    <tr><td>Internal agency login</td><td>http://localhost:8000/login</td></tr>
    <tr><td>Bidder / Supplier portal</td><td>http://localhost:8000/bidder/login</td></tr>
    <tr><td>Health check</td><td>http://localhost:8000/up</td></tr>
</table>

<h2 class="page-break">6. Docker Installation</h2>
<p>The repository includes a production-shaped Docker Compose stack.</p>
<table>
    <tr><th>Service</th><th>Role</th></tr>
    <tr><td>app</td><td>PHP-FPM 8.3 — migrations on boot, serves application</td></tr>
    <tr><td>nginx</td><td>HTTP termination, proxies to app:9000</td></tr>
    <tr><td>queue</td><td>php artisan queue:work (notifications, exports)</td></tr>
    <tr><td>scheduler</td><td>Runs schedule:run every 60 seconds</td></tr>
    <tr><td>mysql</td><td>MySQL 8.0 primary datastore</td></tr>
    <tr><td>redis</td><td>Optional cache/queue backend</td></tr>
    <tr><td>mailhog</td><td>Local SMTP catcher (UI at :8025)</td></tr>
</table>
<pre>cp .env.example .env
# Set DB_HOST=mysql, MAIL_HOST=mailhog, MAIL_PORT=1025
docker compose build
docker compose up -d
docker compose exec app php artisan db:seed</pre>
<p>Visit <strong>http://localhost:8080</strong> (nginx). MailHog: <strong>http://localhost:8025</strong>.</p>

<h2>7. Production Deployment (AWS)</h2>
<p>Recommended topology (see <code>docs/AWS_DEPLOYMENT.md</code> for full details):</p>
<ul>
    <li><strong>Route 53</strong> — DNS for agency domain</li>
    <li><strong>ALB + ACM</strong> — HTTPS load balancing</li>
    <li><strong>ECS Fargate</strong> — app, queue, and scheduler services (same Docker image)</li>
    <li><strong>RDS MySQL 8.0</strong> — Multi-AZ database</li>
    <li><strong>S3</strong> — private, versioned, encrypted document bucket</li>
    <li><strong>ElastiCache Redis</strong> — optional cache/queue at scale</li>
</ul>
<p>Production <code>.env</code> highlights:</p>
<ul>
    <li><code>APP_ENV=production</code>, <code>APP_DEBUG=false</code></li>
    <li><code>DB_CONNECTION=mysql</code> pointing to RDS</li>
    <li><code>DOCUMENTS_DISK_DRIVER=s3</code> with AWS credentials</li>
    <li><code>QUEUE_CONNECTION=database</code> or <code>redis</code></li>
    <li><code>DEMO_ACCOUNTS_VISIBLE=false</code></li>
</ul>

<h2>8. Test Accounts</h2>
<p>Default password for all seeded accounts: <strong>Passw0rd!2026</strong> (local/UAT only).</p>
<table>
    <tr><th>Role</th><th>Email</th></tr>
    <tr><td>Super Admin</td><td>superadmin@pms.gov.ph</td></tr>
    <tr><td>System Admin</td><td>sysadmin@pms.gov.ph</td></tr>
    <tr><td>BAC Chairperson</td><td>bac.chair@pms.gov.ph</td></tr>
    <tr><td>BAC Secretariat</td><td>bac.secretariat@pms.gov.ph</td></tr>
    <tr><td>Budget Officer</td><td>budget.officer@pms.gov.ph</td></tr>
    <tr><td>Planning Officer</td><td>planning.officer@pms.gov.ph</td></tr>
    <tr><td>HOPE</td><td>hope@pms.gov.ph</td></tr>
    <tr><td>Division Chief</td><td>division.chief@pms.gov.ph</td></tr>
    <tr><td>End User</td><td>end.user@pms.gov.ph</td></tr>
    <tr><td>Cashier</td><td>cashier@pms.gov.ph</td></tr>
    <tr><td>Internal Auditor</td><td>auditor@pms.gov.ph</td></tr>
    <tr><td>Viewer</td><td>viewer@pms.gov.ph</td></tr>
    <tr><td>Bidder / Supplier</td><td>bidder@supplier.com (portal)</td></tr>
</table>
<p class="muted">Demo accounts appear on login pages when DEMO_ACCOUNTS_VISIBLE=true (default outside production).</p>

<h2>9. Verification & Testing</h2>
<pre>composer test
# or: php artisan test</pre>
<p>28+ feature tests cover RBAC, budget integrity (no overallocation), and the full procurement lifecycle
(GAA &rarr; CAF and BAC &rarr; Payment).</p>
<p>Post-install smoke check:</p>
<ol>
    <li>Sign in as Super Admin</li>
    <li>Open Dashboard, GAA, APP, PPMP, Purchase Requests</li>
    <li>Verify sample FY2026 data exists after seeding</li>
    <li>Sign in to Bidder Portal and confirm opportunities list loads</li>
</ol>

<h2>10. Troubleshooting</h2>
<table>
    <tr><th>Symptom</th><th>Solution</th></tr>
    <tr><td>Vite manifest not found</td><td>Run <code>npm run build</code> or keep <code>npm run dev</code> running</td></tr>
    <tr><td>MySQL connection refused</td><td>Start MySQL or switch to SQLite for local dev</td></tr>
    <tr><td>Login hangs / too many attempts</td><td>Use SESSION_DRIVER=file and CACHE_STORE=file with SQLite; run <code>php artisan cache:clear</code></td></tr>
    <tr><td>Redirect to wrong port after login</td><td>Set APP_URL to match dev-server port (e.g. http://localhost:8001)</td></tr>
    <tr><td>Alpine multiple instances warning</td><td>Do not call Alpine.start() in app.js — Livewire handles Alpine</td></tr>
    <tr><td>403 on all pages</td><td>Re-run <code>php artisan db:seed --class=PermissionSeeder</code></td></tr>
    <tr><td>Emails not sending</td><td>Set MAIL_MAILER=log or configure SMTP / MailHog</td></tr>
</table>

<h2>11. Additional Documentation</h2>
<table>
    <tr><th>Document</th><th>Location</th></tr>
    <tr><td>Architecture & ERD</td><td>docs/ARCHITECTURE.md</td></tr>
    <tr><td>Docker Guide</td><td>docs/DOCKER.md</td></tr>
    <tr><td>AWS Deployment</td><td>docs/AWS_DEPLOYMENT.md</td></tr>
    <tr><td>Security</td><td>docs/SECURITY.md</td></tr>
    <tr><td>RBAC Roles & Permissions</td><td>docs/RBAC.md</td></tr>
    <tr><td>Backup & Restore</td><td>docs/BACKUP_RESTORE.md</td></tr>
    <tr><td>API Reference</td><td>docs/API.md</td></tr>
    <tr><td>In-app Help & User Manuals</td><td>Help module (after login)</td></tr>
</table>

<p style="margin-top: 24px; font-size: 9px; color: #94a3b8; text-align: center;">
    &copy; {{ date('Y') }} {{ config('app.name') }}. Proprietary — Philippine Government Agency internal use.
</p>

</body>
</html>
