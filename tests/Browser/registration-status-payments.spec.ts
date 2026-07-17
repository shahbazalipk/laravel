import { expect, test } from '@playwright/test';

const registrationHash =
    process.env.PLAYWRIGHT_REGISTRATION_HASH ?? 'pYqST6ppBph185V19Prnlb3CCtAvBavl';
const registrationPath = `/admin/registrations/${registrationHash}`;

test.skip(
    !process.env.PLAYWRIGHT_STORAGE_STATE,
    'Set PLAYWRIGHT_STORAGE_STATE to an authenticated admin storage-state file.',
);

test('registration detail exposes status and payment workflows on desktop and mobile', async ({
    page,
}, testInfo) => {
    const consoleErrors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            consoleErrors.push(message.text());
        }
    });

    await page.goto(registrationPath);

    await expect(page).not.toHaveURL(/\/admin\/login/);
    await expect(page.getByTestId('registration-status-card')).toBeVisible();
    await expect(page.getByTestId('payment-summary-card')).toBeVisible();
    await expect(page.getByTestId('payment-balance-hero')).toBeVisible();
    await expect(page.getByTestId('payment-progress')).toBeVisible();
    await expect(page.getByTestId('payment-progress')).toHaveAttribute('role', 'progressbar');
    await expect(page.getByTestId('payment-details')).toBeVisible();
    await expect(page.getByTestId('payment-history-card')).toBeVisible();
    await expect(page.getByTestId('registration-status-select')).toBeVisible();
    await expect(page.getByTestId('registration-status-save')).toBeVisible();
    await expect(page.getByTestId('current-registration-status')).toBeVisible();
    await expect(page.getByTestId('registration-status-select').locator('option')).not.toHaveCount(0);

    await page.getByTestId('open-delete-registration-modal').click();
    await expect(page.getByTestId('delete-registration-modal')).toBeVisible();
    await expect(page.getByTestId('delete-registration-confirmation')).toBeVisible();
    await expect(page.getByTestId('delete-registration-submit')).toBeVisible();
    await page.getByRole('button', { name: 'Cancel' }).last().click();
    await expect(page.getByTestId('delete-registration-modal')).toBeHidden();

    await page.getByTestId('open-log-payment-modal').click();
    const modal = page.getByTestId('log-payment-modal');
    await expect(modal).toBeVisible();
    await expect(page.getByTestId('payment-amount')).toBeVisible();
    await expect(page.getByTestId('payment-method')).toBeVisible();
    await expect(page.getByTestId('payment-status')).toBeVisible();

    await page.getByTestId('payment-amount').fill('0');
    const amountValid = await page.getByTestId('payment-amount').evaluate(
        (input: HTMLInputElement) => input.checkValidity(),
    );
    expect(amountValid).toBe(false);

    await page.getByTestId('payment-amount').fill('25.50');
    await page.getByTestId('payment-method').fill('Playwright Card');
    await page.getByTestId('payment-reference').fill(`PW-${Date.now()}`);
    await page.getByTestId('payment-status').selectOption('succeeded');
    await page.getByTestId('payment-notes').fill('Logged via Playwright');
    await page.getByTestId('submit-log-payment').click();

    await expect(page.getByTestId('flash-success')).toBeVisible();
    await expect(page.getByTestId('payment-history-card')).toContainText('Playwright Card');
    await expect(page.getByTestId('payment-gross-paid')).toBeVisible();

    if (testInfo.project.name.includes('mobile')) {
        await expect(page.getByTestId('payment-history-cards')).toBeVisible();
    } else {
        await expect(page.getByTestId('payment-history-table')).toBeVisible();
    }

    expect(consoleErrors).toEqual([]);
});
