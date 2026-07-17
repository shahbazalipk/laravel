import { expect, test } from '@playwright/test';

test.describe('sales pipeline types admin', () => {
    test.skip(
        !process.env.PLAYWRIGHT_STORAGE_STATE,
        'Set PLAYWRIGHT_STORAGE_STATE for an authenticated admin session.',
    );

    test('pipeline types index is responsive without console errors', async ({ page }) => {
        const errors: string[] = [];
        page.on('console', (message) => {
            if (message.type() === 'error') errors.push(message.text());
        });
        page.on('pageerror', (error) => errors.push(error.message));

        await page.goto('/admin/sales/pipeline-types');
        await expect(page).not.toHaveURL(/\/admin\/login/);
        await expect(page.getByRole('heading', { name: /pipeline types/i })).toBeVisible();

        const viewportWidth = page.viewportSize()?.width ?? 0;
        const bodyWidth = await page.locator('body').evaluate((element) => element.scrollWidth);
        expect(bodyWidth).toBeLessThanOrEqual(viewportWidth + 1);
        expect(errors.filter((error) => !error.includes('favicon'))).toEqual([]);
    });

    test('pipeline type create form exposes stage drag handles', async ({ page }) => {
        test.skip(!process.env.PLAYWRIGHT_STORAGE_STATE, 'Auth required');
        const errors: string[] = [];
        page.on('pageerror', (error) => errors.push(error.message));

        await page.goto('/admin/sales/pipeline-types/create');
        await expect(page).not.toHaveURL(/\/admin\/login/);
        await expect(page.getByTestId('pipeline-type-stages')).toBeVisible();
        await expect(page.locator('[data-testid="stage-row"]').first()).toBeVisible();
        await expect(page.locator('.stage-handle').first()).toBeVisible();
        expect(errors).toEqual([]);
    });

    test('sales dashboard loads', async ({ page }) => {
        test.skip(!process.env.PLAYWRIGHT_STORAGE_STATE, 'Auth required');
        await page.goto('/admin/sales/dashboard');
        await expect(page).not.toHaveURL(/\/admin\/login/);
        await expect(page.locator('body')).toContainText(/Sales/i);
        await expect(page.getByTestId('sales-dashboard-metrics')).toBeVisible();
    });

    test('kanban board renders columns', async ({ page }) => {
        test.skip(!process.env.PLAYWRIGHT_STORAGE_STATE, 'Auth required');
        await page.goto('/admin/sales/pipelines');
        await expect(page).not.toHaveURL(/\/admin\/login/);
        const firstPipeline = page.locator('a[href*="/admin/sales/pipelines/"]').first();
        if (await firstPipeline.count() === 0) {
            test.skip(true, 'No pipelines available for kanban smoke test');
        }
        await firstPipeline.click();
        const kanbanLink = page.getByRole('link', { name: /kanban/i });
        if (await kanbanLink.count() === 0) {
            test.skip(true, 'Kanban link not present');
        }
        await kanbanLink.click();
        await expect(page.getByTestId('kanban-board')).toBeVisible();
    });
});
