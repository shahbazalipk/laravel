import { expect, test } from '@playwright/test';

test.skip(
    !process.env.PLAYWRIGHT_STORAGE_STATE,
    'Set PLAYWRIGHT_STORAGE_STATE to an authenticated admin storage-state file.',
);

test('registration listing opens delete for registered and draft rows', async ({
    page,
}) => {
    const consoleErrors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            consoleErrors.push(message.text());
        }
    });

    await page.goto('/admin/registrations?stage=registered');
    await expect(page).not.toHaveURL(/\/admin\/login/);

    const registeredDelete = page
        .locator('[data-testid="open-delete-registration-modal"][data-delete-kind="registration"]')
        .first();
    await expect(registeredDelete).toBeVisible();

    const registeredReference = await registeredDelete.getAttribute(
        'data-registration-reference',
    );
    expect(registeredReference).toBeTruthy();

    await registeredDelete.click();
    const modal = page.getByTestId('delete-registration-modal');
    await expect(modal).toBeVisible();
    await expect(modal).toContainText('Permanently delete registration?');
    await expect(modal).toContainText(registeredReference!);
    await page.getByRole('button', { name: 'Cancel' }).click();
    await expect(modal).toBeHidden();

    await page.goto('/admin/registrations?stage=draft');
    const draftDelete = page
        .locator('[data-testid="open-delete-registration-modal"][data-delete-kind="draft"]')
        .first();

    if (await draftDelete.count()) {
        const draftReference = await draftDelete.getAttribute(
            'data-registration-reference',
        );
        await draftDelete.click();
        await expect(modal).toBeVisible();
        await expect(modal).toContainText('Permanently delete draft?');
        await expect(modal).toContainText(draftReference!);
        await page.getByRole('button', { name: 'Cancel' }).click();
        await expect(modal).toBeHidden();
    }

    expect(consoleErrors).toEqual([]);
});
