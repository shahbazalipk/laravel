import { expect, test } from '@playwright/test';

test.skip(
    !process.env.PLAYWRIGHT_STORAGE_STATE,
    'Set PLAYWRIGHT_STORAGE_STATE to an authenticated admin storage-state file.',
);

test('event URL stats page is reachable from the URLs list', async ({ page }, testInfo) => {
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

    await page.goto('/admin/event-urls');
    await expect(page).not.toHaveURL(/\/admin\/login/);

    const statsLink = page.locator('[data-testid^="event-url-stats-"]').first();
    test.skip((await statsLink.count()) === 0, 'No event URLs available to open stats.');

    await statsLink.click();
    await expect(page.getByTestId('event-url-stats-page')).toBeVisible();
    await expect(page.getByTestId('event-url-stats-summary')).toBeVisible();
    await expect(page.getByTestId('event-url-funnel')).toBeVisible();

    expect(consoleErrors.filter((error) => !error.includes('favicon'))).toEqual([]);
});
