import { expect, test } from '@playwright/test';

test.skip(
    !process.env.PLAYWRIGHT_STORAGE_STATE,
    'Set PLAYWRIGHT_STORAGE_STATE to an authenticated admin storage-state file.',
);

test('reports menu opens category, payments, and custom questions reports', async ({ page }, testInfo) => {
    const consoleErrors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            consoleErrors.push(message.text());
        }
    });

    await page.setViewportSize(
        testInfo.project.name.includes('mobile')
            ? { width: 390, height: 844 }
            : { width: 1280, height: 900 },
    );

    await page.goto('/admin/reports');
    await expect(page).not.toHaveURL(/\/admin\/login/);
    await expect(page.getByTestId('reports-index-page')).toBeVisible();
    await expect(page.getByTestId('reports-card-questions')).toBeVisible();

    await page.getByTestId('reports-card-categories').click();
    await expect(page.getByTestId('reports-categories-page')).toBeVisible();
    await expect(page.getByTestId('reports-categories-summary')).toBeVisible();

    await page.goto('/admin/reports/payments');
    await expect(page.getByTestId('reports-payments-page')).toBeVisible();
    await expect(page.getByTestId('reports-payments-summary')).toBeVisible();

    await page.goto('/admin/reports/payment-status');
    await expect(page.getByTestId('reports-payment-status-page')).toBeVisible();
    await expect(page.getByTestId('reports-payment-status-summary')).toBeVisible();

    await page.goto('/admin/reports/questions');
    await expect(page.getByTestId('reports-questions-page')).toBeVisible();
    await expect(page.getByTestId('reports-questions-summary')).toBeVisible();

    expect(consoleErrors.filter((error) => !error.includes('favicon'))).toEqual([]);
});

test('registrations list does not show Progress column', async ({ page }, testInfo) => {
    await page.setViewportSize(
        testInfo.project.name.includes('mobile')
            ? { width: 390, height: 844 }
            : { width: 1280, height: 900 },
    );

    await page.goto('/admin/registrations');
    await expect(page).not.toHaveURL(/\/admin\/login/);

    const table = page.getByTestId('registrations-table');
    if (await table.count()) {
        await expect(table.locator('thead')).not.toContainText('Progress');
    }
});
