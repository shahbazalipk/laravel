import { expect, test } from '@playwright/test';

const eventUrlId = process.env.PLAYWRIGHT_EVENT_URL_ID ?? '6';

test.skip(
    !process.env.PLAYWRIGHT_STORAGE_STATE,
    'Set PLAYWRIGHT_STORAGE_STATE to an authenticated admin storage-state file.',
);

test('event URL editor exposes responsive linked-content controls', async ({
    page,
}) => {
    const consoleErrors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            consoleErrors.push(message.text());
        }
    });
    page.on('pageerror', (error) => consoleErrors.push(error.message));

    await page.goto(`/admin/event-urls/${eventUrlId}/edit`);
    await expect(page).not.toHaveURL(/\/admin\/login/);

    await expect(page.getByTestId('event-url-page-content')).toBeVisible();
    await expect(page.getByTestId('event-url-sponsors')).toBeVisible();
    await expect(page.getByTestId('event-url-partners')).toBeVisible();
    await expect(page.getByTestId('event-url-custom-html')).toBeVisible();

    await page.getByTestId('event-url-custom-html').fill(
        '<h2>Registration notes</h2><p>Bring a valid ID.</p>',
    );
    await expect(page.getByTestId('event-url-custom-html')).toHaveValue(
        '<h2>Registration notes</h2><p>Bring a valid ID.</p>',
    );

    expect(consoleErrors.filter((error) => !error.includes('favicon'))).toEqual([]);
});
