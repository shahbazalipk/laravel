import { expect, test } from '@playwright/test';

const baseURL = process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:8001';
const ctx = process.env.PLAYWRIGHT_CTX || '';
const adminEmail = process.env.PLAYWRIGHT_ADMIN_EMAIL || 'admin@techcorp.com';
const adminPassword = process.env.PLAYWRIGHT_ADMIN_PASSWORD || 'password123';

async function ensureLoggedIn(page: import('@playwright/test').Page) {
    if (process.env.PLAYWRIGHT_STORAGE_STATE) {
        await page.goto(`${baseURL}/admin/finance`);
        if (!page.url().includes('/admin/login')) return;
    }

    test.skip(!ctx, 'Set PLAYWRIGHT_CTX (or PLAYWRIGHT_STORAGE_STATE) for authenticated Finance E2E.');

    await page.goto(`${baseURL}/admin/login?ctx=${encodeURIComponent(ctx)}`);
    await page.locator('input[name="email"]').fill(adminEmail);
    await page.locator('input[name="password"]').fill(adminPassword);
    await page.getByRole('button', { name: /sign in|login/i }).click();
    await expect(page).not.toHaveURL(/\/admin\/login/);
}

test.describe('finance foundation UI', () => {
    test('renders dashboard, lists and consistent forms on desktop and mobile', async ({ page }) => {
        const errors: string[] = [];
        page.on('pageerror', (error) => errors.push(error.message));
        page.on('console', (message) => {
            if (message.type() === 'error') errors.push(message.text());
        });

        await ensureLoggedIn(page);

        await page.goto(`${baseURL}/admin/finance`);
        await expect(page.getByTestId('finance-dashboard-metrics')).toBeVisible();
        await expect(page.locator('nav[aria-label="Finance sections"]')).toHaveCount(0);
        await expect(page.getByRole('heading', { name: /financial overview/i })).toBeVisible();

        await page.goto(`${baseURL}/admin/finance/accounts`);
        await expect(page.getByTestId('finance-account-form')).toBeVisible();
        await expect(page.locator('#account-name')).toBeVisible();

        await page.goto(`${baseURL}/admin/finance/income/create`);
        await expect(page.getByTestId('finance-income-form')).toBeVisible();
        await expect(page.locator('#income-title')).toBeVisible();

        await page.goto(`${baseURL}/admin/finance/expenses/create`);
        await expect(page.getByTestId('finance-expense-form')).toBeVisible();
        await expect(page.locator('#expense-title')).toBeVisible();

        await page.goto(`${baseURL}/admin/finance/invoices/create`);
        await expect(page.getByTestId('finance-invoice-form')).toBeVisible();

        await page.goto(`${baseURL}/admin/finance/bills/create`);
        await expect(page.getByTestId('finance-bill-form')).toBeVisible();

        await page.goto(`${baseURL}/admin/finance/payments`);
        await expect(page.getByTestId('finance-payments-list')).toBeVisible();

        await page.goto(`${baseURL}/admin/finance/budgets`);
        await expect(page.getByTestId('finance-budgets-list')).toBeVisible();

        await page.goto(`${baseURL}/admin/finance/reconciliation`);
        await expect(page.getByTestId('finance-reconciliation-list')).toBeVisible();
        await page.getByText('Import statement CSV', { exact: true }).click();
        await expect(page.getByTestId('finance-statement-form')).toBeVisible();

        await page.goto(`${baseURL}/admin/finance/reports`);
        await expect(page.getByTestId('finance-report-metrics')).toBeVisible();

        await page.setViewportSize({ width: 390, height: 844 });
        for (const path of [
            '/admin/finance',
            '/admin/finance/accounts',
            '/admin/finance/income/create',
            '/admin/finance/expenses/create',
            '/admin/finance/invoices/create',
            '/admin/finance/bills/create',
            '/admin/finance/payments',
            '/admin/finance/budgets',
            '/admin/finance/reconciliation',
            '/admin/finance/reports',
        ]) {
            await page.goto(`${baseURL}${path}`);
            const bodyWidth = await page.locator('body').evaluate((element) => element.scrollWidth);
            expect(bodyWidth, `${path} should not overflow the mobile viewport`).toBeLessThanOrEqual(391);
        }

        expect(errors.filter((error) => !error.includes('favicon'))).toEqual([]);
    });
});
