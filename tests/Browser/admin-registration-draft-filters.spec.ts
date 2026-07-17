import { expect, test } from '@playwright/test';

test.skip(
    !process.env.PLAYWRIGHT_STORAGE_STATE,
    'Set PLAYWRIGHT_STORAGE_STATE to an authenticated admin storage-state file.',
);

test('admin can filter incomplete registrations by the step where users stopped', async ({
    page,
}) => {
    const consoleErrors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            consoleErrors.push(message.text());
        }
    });
    page.on('pageerror', (error) => consoleErrors.push(error.message));

    await page.goto('/admin/registrations');
    await expect(page).not.toHaveURL(/\/admin\/login/);

    const filter = page.getByTestId('abandoned-step-filter');
    await expect(filter).toBeVisible();
    await expect(filter.locator('option')).toHaveText([
        'All Steps',
        'Step 1: Email',
        'Step 2: Information',
        'Step 3: Category',
        'Step 4: Confirmation',
    ]);

    await filter.selectOption('category');
    await page.getByRole('button', { name: 'Apply' }).click();
    await expect(page).toHaveURL(/abandoned_step=category/);
    await expect(filter).toHaveValue('category');

    await page.setViewportSize({ width: 390, height: 844 });
    await expect(page.getByTestId('registrations-filters')).toBeVisible();
    await expect(filter).toBeVisible();

    expect(consoleErrors).toEqual([]);
});
