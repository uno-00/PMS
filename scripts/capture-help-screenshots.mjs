/**
 * Captures Help & User Manual screenshots from the running local app.
 * Usage: node scripts/capture-help-screenshots.mjs
 * Requires: npx playwright (installed on first run)
 */
import { chromium } from 'playwright';
import { mkdir } from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, '..');
const OUT = path.join(ROOT, 'public', 'images', 'help');
const BASE = process.env.APP_URL || 'http://127.0.0.1:8000';
const EMAIL = process.env.HELP_SCREENSHOT_EMAIL || 'superadmin@pms.gov.ph';
const PASSWORD = process.env.HELP_SCREENSHOT_PASSWORD || 'Passw0rd!2026';

/** @type {{ module: string, file: string, path: string, wait?: number }[]} */
const shots = [
    // Dashboard
    { module: 'dashboard', file: 'step-01-executive-dashboard.png', path: '/dashboard/executive' },

    // GAA
    { module: 'gaa', file: 'step-01-fiscal-years.png', path: '/settings/fiscal-years' },
    { module: 'gaa', file: 'step-02-gaa-index.png', path: '/gaa' },
    { module: 'gaa', file: 'step-03-gaa-upload.png', path: '/gaa/create' },

    { module: 'gaa', file: 'step-04-gaa-detail.png', path: '/gaa/40f5e4d5-cfd1-429a-860c-8c74591e97c3' },

    // APP
    { module: 'app', file: 'step-01-app-overview.png', path: '/app' },

    // Budget allocation
    { module: 'budget-allocation', file: 'step-01-allocations-index.png', path: '/budget-allocations' },

    // Indicative PPMP pipeline
    { module: 'project-proposal', file: 'step-01-pipeline-index.png', path: '/project-proposals' },
    { module: 'project-proposal', file: 'step-02-proposal-form.png', path: '/project-proposals/create' },

    { module: 'project-proposal', file: 'step-03-market-scoping-index.png', path: '/market-scoping' },

    // PPMP
    { module: 'ppmp', file: 'step-01-ppmp-index.png', path: '/ppmps' },
    { module: 'ppmp', file: 'step-02-ppmp-create.png', path: '/ppmps/create' },

    // Purchase Request
    { module: 'purchase-request', file: 'step-01-pr-index.png', path: '/purchase-requests' },
    { module: 'purchase-request', file: 'step-02-pr-create.png', path: '/purchase-requests/create' },

    { module: 'purchase-request', file: 'step-03-pr-detail.png', path: '/purchase-requests/5ccdc2b9-5a9d-4677-9d8e-c6b4f7fd7fd2' },

    // CAF
    { module: 'caf', file: 'step-01-caf-index.png', path: '/cafs' },

    // BAC
    { module: 'bac', file: 'step-01-procurements-index.png', path: '/procurements' },
    { module: 'bac', file: 'step-02-bac-calendar.png', path: '/bac-calendar' },
    { module: 'bac', file: 'step-03-bac-members.png', path: '/bac-members' },

    // PhilGEPS
    { module: 'philgeps', file: 'step-01-philgeps-index.png', path: '/philgeps' },

    // Bidders (agency admin)
    { module: 'bidder-portal', file: 'step-01-bidders-index.png', path: '/bidders' },

    // Bidder portal (public pages — no login)
    { module: 'bidder-portal', file: 'step-02-bidder-register.png', path: '/bidder/register', guest: true },
    { module: 'bidder-portal', file: 'step-03-bidder-login.png', path: '/bidder/login', guest: true },

    // Purchase Order & Payment
    { module: 'purchase-order', file: 'step-01-po-index.png', path: '/purchase-orders' },
    { module: 'purchase-order', file: 'step-02-payments-index.png', path: '/payments' },

    // Settings & Reports
    { module: 'settings', file: 'step-01-settings-overview.png', path: '/settings' },
    { module: 'settings', file: 'step-02-reference-data.png', path: '/settings/reference-data' },
    { module: 'reports', file: 'step-01-reports-overview.png', path: '/reports' },

    // Help page itself
    { module: 'dashboard', file: 'step-02-help-manuals.png', path: '/help' },
];

async function login(page) {
    await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
    await page.fill('input[type="email"]', EMAIL);
    await page.fill('input[type="password"]', PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });
    await page.waitForTimeout(800);
}

async function capture(page, shot, loggedIn) {
    const dir = path.join(OUT, shot.module);
    await mkdir(dir, { recursive: true });
    const dest = path.join(dir, shot.file);

    if (shot.guest && loggedIn) {
        await page.goto(`${BASE}/logout`, { waitUntil: 'networkidle' }).catch(() => {});
        await page.context().clearCookies();
    }

    await page.goto(`${BASE}${shot.path}`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(shot.wait ?? 1200);
    await page.screenshot({ path: dest, fullPage: true });
    console.log(`  ✓ ${shot.module}/${shot.file}`);
}

async function main() {
    console.log(`Capturing help screenshots from ${BASE} …`);

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 },
        deviceScaleFactor: 1,
    });
    const page = await context.newPage();

    try {
        await login(page);
        for (const shot of shots.filter(s => !s.guest)) {
            await capture(page, shot, true);
        }

        await page.context().clearCookies();
        for (const shot of shots.filter(s => s.guest)) {
            await capture(page, shot, false);
        }

        console.log(`\nDone — ${shots.length} screenshots saved under public/images/help/`);
    } finally {
        await browser.close();
    }
}

main().catch(err => {
    console.error(err);
    process.exit(1);
});
