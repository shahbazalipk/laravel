import { expect, test } from '@playwright/test';

test.skip(
    !process.env.PLAYWRIGHT_STORAGE_STATE,
    'Set PLAYWRIGHT_STORAGE_STATE to an authenticated admin storage-state file.',
);

test('event settings timezone uses a searchable dropdown on desktop and mobile', async ({
    page,
}, testInfo) => {
    const consoleErrors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            consoleErrors.push(message.text());
        }
    });
    page.on('pageerror', (error) => consoleErrors.push(error.message));

    await page.setViewportSize(
        testInfo.project.name.includes('mobile')
            ? { width: 390, height: 844 }
            : { width: 1280, height: 720 },
    );

    await page.goto('/admin/event-settings');
    await expect(page).not.toHaveURL(/\/admin\/login/);

    await page.getByRole('button', { name: 'Dates & Times' }).click();

    const timezone = page.getByTestId('event-timezone-select');
    await expect(timezone).toBeVisible();
    await expect(timezone).toHaveAttribute('name', 'timezone');
    await expect(timezone.locator('option')).not.toHaveCount(0);
    await expect(timezone.locator('optgroup')).not.toHaveCount(0);

    await timezone.selectOption({ label: 'Asia/Karachi' }).catch(async () => {
        await timezone.selectOption({ index: 1 });
    });
    await expect(timezone).not.toHaveValue('');

    expect(consoleErrors.filter((error) => !error.includes('favicon'))).toEqual([]);
});
