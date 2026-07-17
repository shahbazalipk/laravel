import { expect, test } from '@playwright/test';

const parameters = [
    'sponsors',
    'partners',
    'registration-statuses',
    'personas',
    'category-types',
    'product-types',
    'exhibitor-tags',
    'booth-types',
    'exhibitor-types',
    'business-activities',
    'group-types',
];

test.skip(
    !process.env.PLAYWRIGHT_STORAGE_STATE,
    'Set PLAYWRIGHT_STORAGE_STATE to an authenticated admin storage-state file.',
);

for (const parameter of parameters) {
    test(`${parameter} exposes a responsive bulk import modal`, async ({ page }) => {
        const consoleErrors: string[] = [];
        page.on('console', (message) => {
            if (message.type() === 'error') {
                consoleErrors.push(message.text());
            }
        });

        await page.goto(`/admin/${parameter}`);

        await expect(page).not.toHaveURL(/\/admin\/login/);
        await expect(page.getByTestId('bulk-import-button')).toBeVisible();

        await page.getByTestId('bulk-import-button').click();

        const modal = page.getByTestId('bulk-import-modal');
        await expect(modal).toBeVisible();
        await expect(page.getByTestId('bulk-import-names')).toBeVisible();
        await expect(page.getByTestId('bulk-import-file')).toBeVisible();
        await expect(page.getByTestId('bulk-import-submit')).toBeVisible();

        if (parameter === 'sponsors' || parameter === 'partners') {
            await expect(modal.getByLabel('Type *')).toBeVisible();
            await expect(
                modal.getByLabel(
                    parameter === 'sponsors' ? 'Sponsorship Label *' : 'Partnership Label *',
                ),
            ).toBeVisible();
        }

        await expect(modal.locator('form')).toHaveCSS('overflow-x', 'visible');
        expect(consoleErrors).toEqual([]);
    });
}
