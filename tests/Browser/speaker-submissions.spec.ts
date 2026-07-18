import { expect, test, type Page } from '@playwright/test';

const baseURL = process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:8001';
const ctx = process.env.PLAYWRIGHT_CTX || '';
const adminEmail = process.env.PLAYWRIGHT_ADMIN_EMAIL || 'admin@techcorp.com';
const adminPassword = process.env.PLAYWRIGHT_ADMIN_PASSWORD || 'password123';
const eventSlug = process.env.PLAYWRIGHT_SUBMISSION_EVENT_SLUG || '';
const typeSlug = process.env.PLAYWRIGHT_SUBMISSION_TYPE_SLUG || '';
const hasAdminContext = Boolean(process.env.PLAYWRIGHT_STORAGE_STATE || ctx);
const hasPublicDemo = Boolean(eventSlug && typeSlug);

function collectRuntimeErrors(page: Page): string[] {
    const errors: string[] = [];
    page.on('pageerror', (error) => errors.push(error.message));
    page.on('console', (message) => {
        if (message.type() === 'error') errors.push(message.text());
    });

    return errors;
}

async function ensureAdmin(page: Page): Promise<void> {
    if (process.env.PLAYWRIGHT_STORAGE_STATE) {
        await page.goto(`${baseURL}/admin/submissions/dashboard`);
        if (!page.url().includes('/admin/login')) return;
    }

    test.skip(!ctx, 'Set PLAYWRIGHT_CTX or PLAYWRIGHT_STORAGE_STATE for authenticated submission E2E.');
    await page.goto(`${baseURL}/admin/login?ctx=${encodeURIComponent(ctx)}`);
    await page.locator('input[name="email"]').fill(adminEmail);
    await page.locator('input[name="password"]').fill(adminPassword);
    await page.getByRole('button', { name: /sign in|login/i }).click();
    await expect(page).not.toHaveURL(/\/admin\/login/);
}

test.describe('speaker submissions', () => {
    const adminTest = hasAdminContext ? test : test.skip;
    const publicTest = hasPublicDemo ? test : test.skip;

    adminTest('admin dashboard, types, kanban, and reviewers are responsive', async ({ page }) => {
        const errors = collectRuntimeErrors(page);
        await ensureAdmin(page);

        await page.goto(`${baseURL}/admin/submissions/dashboard`);
        await expect(page.getByTestId('submission-dashboard')).toBeVisible();
        await expect(page.getByRole('heading', { name: /submission overview/i })).toBeVisible();

        await page.goto(`${baseURL}/admin/submissions/types`);
        await expect(page.getByRole('heading', { name: /submission types/i })).toBeVisible();

        await page.goto(`${baseURL}/admin/submissions/kanban`);
        await expect(page.locator('body')).toContainText(/submission|review|selected/i);

        await page.goto(`${baseURL}/admin/submissions/reviewers`);
        await expect(page.locator('body')).toContainText(/reviewer/i);

        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto(`${baseURL}/admin/submissions/dashboard`);
        await expect(page.getByTestId('submission-dashboard')).toBeVisible();
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
        expect(overflow).toBeLessThanOrEqual(1);

        expect(errors.filter((error) => !error.toLowerCase().includes('favicon'))).toEqual([]);
    });

    publicTest('public call-for-submissions landing is usable on mobile', async ({ page }) => {
        const errors = collectRuntimeErrors(page);

        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto(`${baseURL}/submissions/events/${encodeURIComponent(eventSlug)}/${encodeURIComponent(typeSlug)}`);

        await expect(page.getByTestId('submission-landing')).toBeVisible();
        await expect(page.getByRole('heading', { name: /share your expertise|submission|proposal/i }).first()).toBeVisible();
        await expect(page.getByRole('link', { name: /sign in to begin/i })).toBeVisible();
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
        expect(overflow).toBeLessThanOrEqual(1);

        expect(errors.filter((error) => !error.toLowerCase().includes('favicon'))).toEqual([]);
    });
});
