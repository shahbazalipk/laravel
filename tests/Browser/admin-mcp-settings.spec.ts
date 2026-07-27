import { expect, test } from '@playwright/test';

test.skip(
    !process.env.PLAYWRIGHT_STORAGE_STATE,
    'Set PLAYWRIGHT_STORAGE_STATE to an authenticated admin storage-state file.',
);

test('MCP settings page shows URL and can generate a token', async ({
    page,
}, testInfo) => {
    const consoleErrors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            consoleErrors.push(message.text());
        }
    });

    await page.goto('/admin/mcp');
    await expect(page).not.toHaveURL(/\/admin\/login/);

    await expect(page.getByTestId('mcp-settings-page')).toBeVisible();
    await expect(page.getByTestId('mcp-url-input')).toBeVisible();
    await expect(page.getByTestId('mcp-url-input')).toHaveValue(/\/mcp\/event-operations$/);
    await expect(page.getByTestId('copy-mcp-url')).toBeVisible();
    await expect(page.getByTestId('mcp-generate-form')).toBeVisible();

    const tokenName = `Playwright ${Date.now()}`;
    await page.getByTestId('mcp-token-name').fill(tokenName);
    await page.getByTestId('mcp-generate-submit').click();

    await expect(page.getByTestId('mcp-new-token-alert')).toBeVisible();
    await expect(page.getByTestId('mcp-new-token-value')).toContainText('evt_mcp_');
    await expect(page.getByTestId('flash-success')).toBeVisible();
    await expect(page.getByText(tokenName)).toBeVisible();

    if (testInfo.project.name.includes('mobile')) {
        await expect(page.getByTestId('mcp-generate-card')).toBeVisible();
    }

    expect(consoleErrors).toEqual([]);
});
