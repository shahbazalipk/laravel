import { expect, test } from '@playwright/test';

/**
 * Full Sales module E2E with dummy data.
 *
 * Prerequisites:
 *   1. App running at PLAYWRIGHT_BASE_URL (default http://127.0.0.1:8001)
 *   2. Event context + admin session via PLAYWRIGHT_STORAGE_STATE, OR
 *      set PLAYWRIGHT_CTX + PLAYWRIGHT_ADMIN_EMAIL + PLAYWRIGHT_ADMIN_PASSWORD
 *      to log in before each test.
 */

const baseURL = process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:8001';
const ctx = process.env.PLAYWRIGHT_CTX || '';
const adminEmail = process.env.PLAYWRIGHT_ADMIN_EMAIL || 'admin@techcorp.com';
const adminPassword = process.env.PLAYWRIGHT_ADMIN_PASSWORD || 'password123';

async function ensureLoggedIn(page: import('@playwright/test').Page) {
    if (process.env.PLAYWRIGHT_STORAGE_STATE) {
        await page.goto(`${baseURL}/admin/sales/dashboard`);
        if (!page.url().includes('/admin/login')) return;
    }

    test.skip(!ctx, 'Set PLAYWRIGHT_CTX (or PLAYWRIGHT_STORAGE_STATE) for authenticated Sales E2E.');

    await page.goto(`${baseURL}/admin/login?ctx=${encodeURIComponent(ctx)}`);
    await page.locator('input[name="email"]').fill(adminEmail);
    await page.locator('input[name="password"]').fill(adminPassword);
    await page.getByRole('button', { name: /sign in|login/i }).click();
    await expect(page).not.toHaveURL(/\/admin\/login/);
}

test.describe('sales module end-to-end', () => {
    test('walks dashboard → types → pipelines → kanban → deals → forms → submissions → public form', async ({ page }) => {
        const errors: string[] = [];
        page.on('pageerror', (error) => errors.push(error.message));
        page.on('console', (message) => {
            if (message.type() === 'error') errors.push(message.text());
        });

        await ensureLoggedIn(page);

        await page.goto(`${baseURL}/admin/sales/dashboard`);
        await expect(page.getByTestId('sales-dashboard-metrics')).toBeVisible();
        await expect(page.locator('body')).toContainText(/EUR\s[\d,]+/);
        await expect(page.locator('body')).not.toContainText(/\$\d/);

        await page.goto(`${baseURL}/admin/sales/pipeline-types`);
        await expect(page.getByRole('heading', { name: /pipeline types/i })).toBeVisible();

        await page.goto(`${baseURL}/admin/sales/pipeline-types/create`);
        await expect(page.getByTestId('pipeline-type-stages')).toBeVisible();
        await expect(page.locator('.stage-handle').first()).toBeVisible();
        const before = await page.locator('[data-testid="stage-row"]').count();
        await page.locator('#add-stage').click();
        await expect(page.locator('[data-testid="stage-row"]')).toHaveCount(before + 1);

        await page.goto(`${baseURL}/admin/sales/pipelines`);
        await expect(page.locator('body')).toContainText(/Q3 Sponsorship Drive|pipeline/i);

        const pipelineLink = page.locator('a[href*="/admin/sales/pipelines/"]').filter({ hasText: /Q3 Sponsorship|Drive/i }).first();
        if (await pipelineLink.count()) {
            await pipelineLink.click();
            await expect(page.locator('body')).toContainText(/EUR\s[\d,]+/);
            const kanban = page.getByRole('link', { name: /kanban/i });
            if (await kanban.count()) {
                await kanban.click();
                await expect(page.getByTestId('kanban-board')).toBeVisible();
                await expect(page.locator('body')).toContainText(/EUR\s[\d,]+/);
            }
        }

        await page.goto(`${baseURL}/admin/sales/deals`);
        await expect(page.locator('body')).toContainText(/deal/i);
        await expect(page.locator('body')).toContainText(/EUR\s[\d,]+/);

        await page.goto(`${baseURL}/admin/sales/inquiry-forms`);
        await expect(page.locator('body')).toContainText(/Sponsorship Inquiry|inquiry/i);

        await page.goto(`${baseURL}/admin/sales/submissions`);
        await expect(page.getByTestId('export-submissions')).toBeVisible();

        await page.goto(`${baseURL}/sales/f/sponsorship-inquiry`);
        await expect(page.locator('body')).toContainText(/Become a Sponsor|Sponsorship/i);

        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto(`${baseURL}/admin/sales/dashboard`);
        const bodyWidth = await page.locator('body').evaluate((el) => el.scrollWidth);
        expect(bodyWidth).toBeLessThanOrEqual(391);

        expect(errors.filter((e) => !e.includes('favicon'))).toEqual([]);
    });
});
