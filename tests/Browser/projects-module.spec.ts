import { expect, test } from '@playwright/test';

const baseURL = process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:8001';
const ctx = process.env.PLAYWRIGHT_CTX || '';
const adminEmail = process.env.PLAYWRIGHT_ADMIN_EMAIL || 'admin@techcorp.com';
const adminPassword = process.env.PLAYWRIGHT_ADMIN_PASSWORD || 'password123';

async function ensureLoggedIn(page: import('@playwright/test').Page) {
    if (process.env.PLAYWRIGHT_STORAGE_STATE) {
        await page.goto(`${baseURL}/admin/projects`);
        if (!page.url().includes('/admin/login')) return;
    }

    test.skip(!ctx, 'Set PLAYWRIGHT_CTX (or PLAYWRIGHT_STORAGE_STATE) for authenticated Projects E2E.');

    await page.goto(`${baseURL}/admin/login?ctx=${encodeURIComponent(ctx)}`);
    await page.locator('input[name="email"]').fill(adminEmail);
    await page.locator('input[name="password"]').fill(adminPassword);
    await page.getByRole('button', { name: /sign in|login/i }).click();
    await expect(page).not.toHaveURL(/\/admin\/login/);
}

test.describe('projects foundation UI', () => {
    test('exposes responsive navigation, dashboard, list and project form', async ({ page }) => {
        const errors: string[] = [];
        page.on('pageerror', (error) => errors.push(error.message));
        page.on('console', (message) => {
            if (message.type() === 'error') errors.push(message.text());
        });

        await ensureLoggedIn(page);

        await page.goto(`${baseURL}/admin/projects`);
        await expect(page.getByTestId('projects-dashboard-metrics')).toBeVisible();
        await expect(page.getByRole('heading', { name: /project command center/i })).toBeVisible();
        await expect(page.locator('nav[aria-label="Project sections"]')).toHaveCount(0);
        await expect(page.locator('.desktop-menu > .dropdown > button').filter({ hasText: /^Categories$/ })).toHaveCount(0);
        await expect(page.locator('.desktop-menu > .dropdown > button').filter({ hasText: /^Parameters$/ })).toHaveCount(0);
        await expect(page.locator('.desktop-menu details summary').filter({ hasText: 'Categories' })).toHaveCount(1);
        await expect(page.locator('.desktop-menu details summary').filter({ hasText: 'Parameters' })).toHaveCount(1);

        await page.goto(`${baseURL}/admin/projects/projects`);
        await expect(page.getByTestId('projects-list')).toBeVisible();
        const projectLink = page.getByTestId('projects-list').locator('a').first();
        let financePath: string | null = null;
        if (await projectLink.count()) {
            await projectLink.click();
            const financials = page.getByRole('link', { name: 'Financials' });
            if (await financials.count()) {
                financePath = new URL(await financials.getAttribute('href') || '', baseURL).pathname;
                await financials.click();
                await expect(page.getByTestId('project-finance')).toBeVisible();
                await expect(page.getByTestId('project-finance-metrics')).toBeVisible();
            }
        }

        await page.goto(`${baseURL}/admin/projects/projects/create`);
        await expect(page.getByTestId('project-form')).toBeVisible();
        await expect(page.locator('#project-name')).toBeVisible();

        await page.goto(`${baseURL}/admin/projects/teams`);
        await expect(page.getByTestId('project-teams-list')).toBeVisible();
        await expect(page.getByTestId('project-team-form')).toBeVisible();

        await page.goto(`${baseURL}/admin/projects/reports`);
        await expect(page.getByTestId('project-report-metrics')).toBeVisible();

        await page.goto(`${baseURL}/admin/projects/calendar`);
        await expect(page.getByTestId('project-calendar')).toBeVisible();

        await page.goto(`${baseURL}/admin/projects/timeline`);
        await expect(page.getByTestId('project-timeline')).toBeVisible();

        await page.goto(`${baseURL}/admin/projects/templates`);
        await expect(page.getByTestId('project-templates')).toBeVisible();

        await page.goto(`${baseURL}/admin/projects/guests`);
        await expect(page.getByTestId('project-guests')).toBeVisible();

        await page.goto(`${baseURL}/admin/projects/controls`);
        await expect(page.getByTestId('project-controls')).toBeVisible();
        await expect(page.getByTestId('project-control-metrics')).toBeVisible();

        await page.setViewportSize({ width: 390, height: 844 });
        for (const path of [
            '/admin/projects',
            '/admin/projects/projects',
            '/admin/projects/projects/create',
            '/admin/projects/teams',
            '/admin/projects/reports',
            '/admin/projects/calendar',
            '/admin/projects/timeline',
            '/admin/projects/templates',
            '/admin/projects/guests',
            '/admin/projects/controls',
            ...(financePath ? [financePath] : []),
        ]) {
            await page.goto(`${baseURL}${path}`);
            const bodyWidth = await page.locator('body').evaluate((element) => element.scrollWidth);
            expect(bodyWidth, `${path} should not overflow the mobile viewport`).toBeLessThanOrEqual(391);
        }

        expect(errors.filter((error) => !error.includes('favicon'))).toEqual([]);
    });
});
