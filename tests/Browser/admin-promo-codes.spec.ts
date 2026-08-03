import { expect, test } from '@playwright/test';

test.skip(
    !process.env.PLAYWRIGHT_STORAGE_STATE,
    'Set PLAYWRIGHT_STORAGE_STATE to an authenticated admin storage-state file.',
);

test('promo codes CRUD supports percentage and fixed amounts responsively', async ({
    page,
}, testInfo) => {
    const consoleErrors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            consoleErrors.push(message.text());
        }
    });

    await page.goto('/admin/promo-codes');
    await expect(page).not.toHaveURL(/\/admin\/login/);
    await expect(page.getByTestId('promo-codes-page')).toBeVisible();

    await page.getByTestId('promo-codes-create').click();
    await expect(page.getByTestId('promo-code-create-page')).toBeVisible();

    const code = `PW${Date.now().toString().slice(-6)}`;
    await page.getByTestId('promo-code-input').fill(code);
    await page.getByTestId('promo-name-input').fill('Playwright promo');
    await page.getByTestId('promo-discount-type').selectOption('percentage');
    await page.getByTestId('promo-discount-value').fill('12.5');
    await page.getByTestId('promo-max-total-uses').fill('25');
    await page.getByTestId('promo-max-uses-per-email').fill('1');
    await expect(page.getByTestId('promo-restrict-email-list')).toBeVisible();
    await page.getByTestId('promo-restrict-email-list').check();
    await expect(page.getByTestId('promo-email-list-file')).toBeVisible();
    await expect(page.getByTestId('promo-email-sample-download')).toBeVisible();
    await page.getByTestId('promo-restrict-email-list').uncheck();
    await page.getByTestId('promo-code-submit').click();

    await expect(page.getByTestId('flash-success')).toBeVisible();
    await expect(page.getByTestId('promo-code-show-page')).toBeVisible();
    await expect(page.getByTestId('promo-code-detail-code')).toContainText(code.toUpperCase());

    await page.goto('/admin/promo-codes');
    await expect(page.getByTestId('promo-codes-table')).toContainText(code.toUpperCase());
    await expect(page.getByTestId('promo-codes-table')).toContainText('12.5%');

    await page.getByTestId('promo-codes-create').click();
    const fixedCode = `FX${Date.now().toString().slice(-6)}`;
    await page.getByTestId('promo-code-input').fill(fixedCode);
    await page.getByTestId('promo-discount-type').selectOption('fixed');
    await expect(page.getByTestId('promo-currency')).toBeVisible();
    await page.getByTestId('promo-discount-value').fill('50');
    await page.getByTestId('promo-currency').fill('AED');
    await page.getByTestId('promo-expires-at').fill('2030-12-31T23:59');
    await page.getByTestId('promo-code-submit').click();

    await expect(page.getByTestId('flash-success')).toBeVisible();
    await expect(page.getByTestId('promo-code-show-page')).toBeVisible();
    await expect(page.getByTestId('promo-code-emails-section')).toBeVisible();

    await page.goto('/admin/promo-codes');
    await expect(page.getByTestId('promo-codes-table')).toContainText(fixedCode.toUpperCase());
    await expect(page.getByTestId('promo-codes-table')).toContainText('AED');

    const viewLink = page.locator('[data-testid^="promo-code-view-"]').first();
    await viewLink.click();
    await expect(page.getByTestId('promo-code-show-page')).toBeVisible();
    await expect(page.getByTestId('promo-code-detail-summary')).toBeVisible();
    await expect(page.getByTestId('promo-code-email-search')).toBeVisible();
    await expect(page.getByTestId('promo-code-add-email')).toBeVisible();

    await page.getByTestId('promo-code-add-email').click();
    await expect(page.getByTestId('promo-code-add-email-modal')).toBeVisible();
    await page.getByTestId('promo-code-add-email-input').fill(`pw-${Date.now()}@example.com`);
    await page.getByTestId('promo-code-add-email-submit').click();
    await expect(page.getByTestId('flash-success')).toBeVisible();
    await expect(page.getByTestId('promo-code-emails-table')).toBeVisible();

    if (testInfo.project.name.includes('mobile')) {
        await expect(page.getByTestId('promo-code-show-page')).toBeVisible();
    }

    expect(consoleErrors).toEqual([]);
});
