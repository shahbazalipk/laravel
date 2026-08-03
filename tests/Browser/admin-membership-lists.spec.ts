import { expect, test } from '@playwright/test';

test.skip(
    !process.env.PLAYWRIGHT_STORAGE_STATE,
    'Set PLAYWRIGHT_STORAGE_STATE to an authenticated admin storage-state file.',
);

test('membership lists support CSV and API sample configuration', async ({
    page,
}, testInfo) => {
    const consoleErrors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            consoleErrors.push(message.text());
        }
    });

    await page.goto('/admin/memberships');
    await expect(page).not.toHaveURL(/\/admin\/login/);
    await expect(page.getByTestId('memberships-page')).toBeVisible();

    await page.getByTestId('memberships-create').click();
    await expect(page.getByTestId('membership-create-page')).toBeVisible();

    const name = `API List ${Date.now()}`;
    await page.getByTestId('membership-name').fill(name);
    await page.getByTestId('membership-identifier-type').selectOption('student_id');
    await page.getByTestId('membership-verification-type').selectOption('third_party_api');
    await expect(page.getByTestId('membership-api-section')).toBeVisible();
    await page.getByTestId('membership-api-endpoint').fill('https://api.example.com/verify-student');
    await page.getByTestId('membership-api-method').selectOption('POST');
    await page.getByTestId('membership-api-sample-request').fill('{"student_id":"{{identifier}}"}');
    await page.getByTestId('membership-api-sample-response').fill('{"valid":true,"status":"active"}');
    await page.getByTestId('membership-submit').click();

    await expect(page.getByTestId('flash-success')).toBeVisible();
    await expect(page.getByTestId('memberships-table')).toContainText(name);
    await expect(page.getByTestId('memberships-table')).toContainText('Student ID');
    await expect(page.getByTestId('memberships-table')).toContainText('Third-party API');

    if (testInfo.project.name.includes('mobile')) {
        await expect(page.getByTestId('memberships-page')).toBeVisible();
    }

    expect(consoleErrors).toEqual([]);
});
