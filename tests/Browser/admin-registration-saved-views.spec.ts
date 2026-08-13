import { expect, test } from '@playwright/test';

test.skip(
    !process.env.PLAYWRIGHT_STORAGE_STATE,
    'Set PLAYWRIGHT_STORAGE_STATE to an authenticated admin storage-state file.',
);

test('admin can customize registration columns and choose sharing', async ({
    page,
}, testInfo) => {
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

    await page.goto('/admin/registrations');
    await expect(page).not.toHaveURL(/\/admin\/login/);

    const bar = page.getByTestId('saved-views-bar');
    await expect(bar).toBeVisible();
    await expect(page.getByTestId('saved-view-select')).toBeVisible();
    await expect(page.getByTestId('active-view-name')).toBeVisible();
    await expect(page.getByTestId('export-saved-view')).toBeVisible();
    await expect(page.getByTestId('export-saved-view')).toHaveAttribute('href', /registrations-views\/export/);

    await page.getByTestId('customize-columns-toggle').click();
    const panel = page.getByTestId('customize-columns-panel');
    await expect(panel).toBeVisible();

    await expect(page.getByTestId('column-std:company')).toBeVisible();
    await expect(page.getByTestId('view-visibility-private')).toBeVisible();
    await expect(page.getByTestId('view-visibility-public')).toBeVisible();
    await expect(page.getByTestId('view-visibility-users')).toBeVisible();
    await expect(page.getByTestId('saved-view-name')).toBeVisible();
    await expect(page.getByTestId('save-view-submit')).toBeVisible();

    await page.getByTestId('view-visibility-users').check();
    await expect(page.getByTestId('view-share-users')).toBeVisible();

    expect(consoleErrors.filter((error) => !error.includes('favicon'))).toEqual([]);
});
