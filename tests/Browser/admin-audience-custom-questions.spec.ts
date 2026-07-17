import { expect, test } from '@playwright/test';

const registrationCreateUrl = process.env.PLAYWRIGHT_ADMIN_REGISTRATION_CREATE_URL;
const exhibitorCreateUrl = process.env.PLAYWRIGHT_ADMIN_EXHIBITOR_CREATE_URL;
const groupCreateUrl = process.env.PLAYWRIGHT_ADMIN_GROUP_CREATE_URL;

async function assertNoConsoleErrors(page: import('@playwright/test').Page): Promise<string[]> {
    const errors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            errors.push(message.text());
        }
    });
    page.on('pageerror', (error) => errors.push(error.message));
    return errors;
}

test.describe('admin audience custom questions', () => {
    test.skip(
        !process.env.PLAYWRIGHT_STORAGE_STATE,
        'Set PLAYWRIGHT_STORAGE_STATE for an authenticated admin session.',
    );

    test('registration create page renders custom questions responsively', async ({ page }) => {
        test.skip(!registrationCreateUrl, 'Set PLAYWRIGHT_ADMIN_REGISTRATION_CREATE_URL.');
        const errors = await assertNoConsoleErrors(page);

        await page.goto(registrationCreateUrl!);
        await expect(page).not.toHaveURL(/\/admin\/login/);
        await expect(page.getByTestId('admin-registration-custom-questions')).toBeVisible();
        await expect(page.locator('[data-custom-form]').first()).toBeVisible();

        const viewportWidth = page.viewportSize()?.width ?? 0;
        const bodyWidth = await page.locator('body').evaluate((element) => element.scrollWidth);
        expect(bodyWidth).toBeLessThanOrEqual(viewportWidth + 1);
        expect(errors.filter((error) => !error.includes('favicon'))).toEqual([]);
    });

    test('exhibitor create page exposes a custom questions tab', async ({ page }) => {
        test.skip(!exhibitorCreateUrl, 'Set PLAYWRIGHT_ADMIN_EXHIBITOR_CREATE_URL.');
        const errors = await assertNoConsoleErrors(page);

        await page.goto(exhibitorCreateUrl!);
        await expect(page).not.toHaveURL(/\/admin\/login/);
        await page.getByTestId('exhibitor-custom-questions-tab').click();
        await expect(page.getByTestId('admin-exhibitor-custom-questions')).toBeVisible();
        await expect(page.locator('[data-custom-form]').first()).toBeVisible();

        const viewportWidth = page.viewportSize()?.width ?? 0;
        const bodyWidth = await page.locator('body').evaluate((element) => element.scrollWidth);
        expect(bodyWidth).toBeLessThanOrEqual(viewportWidth + 1);
        expect(errors.filter((error) => !error.includes('favicon'))).toEqual([]);
    });

    test('group create page exposes a custom questions tab', async ({ page }) => {
        test.skip(!groupCreateUrl, 'Set PLAYWRIGHT_ADMIN_GROUP_CREATE_URL.');
        const errors = await assertNoConsoleErrors(page);

        await page.goto(groupCreateUrl!);
        await expect(page).not.toHaveURL(/\/admin\/login/);
        await page.getByTestId('group-custom-questions-tab').click();
        await expect(page.getByTestId('admin-group-custom-questions')).toBeVisible();
        await expect(page.locator('[data-custom-form]').first()).toBeVisible();

        const viewportWidth = page.viewportSize()?.width ?? 0;
        const bodyWidth = await page.locator('body').evaluate((element) => element.scrollWidth);
        expect(bodyWidth).toBeLessThanOrEqual(viewportWidth + 1);
        expect(errors.filter((error) => !error.includes('favicon'))).toEqual([]);
    });
});
