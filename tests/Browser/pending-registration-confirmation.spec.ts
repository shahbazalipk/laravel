import { expect, test } from '@playwright/test';

const pendingRegistrationHash = process.env.PLAYWRIGHT_PENDING_REGISTRATION_HASH;

test.skip(
    !pendingRegistrationHash,
    'Set PLAYWRIGHT_PENDING_REGISTRATION_HASH to a registration awaiting payment.',
);

test('pending payment is unmistakable on the registration result page', async ({
    page,
}) => {
    const consoleErrors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            consoleErrors.push(message.text());
        }
    });
    page.on('pageerror', (error) => consoleErrors.push(error.message));

    await page.goto(`/register/confirmation/${pendingRegistrationHash}`);

    await expect(page).toHaveTitle(/Payment Verification Pending/);
    await expect(page.getByTestId('payment-pending-heading')).toHaveText(
        'Payment Verification Pending',
    );
    await expect(page.getByTestId('payment-pending-alert')).toBeVisible();
    await expect(page.getByText('We’ll verify your payment and update you')).toBeVisible();
    await expect(
        page.getByText('No further action is required right now.', { exact: false }),
    ).toBeVisible();
    await expect(page.getByText('Registration Confirmed!')).toHaveCount(0);

    expect(consoleErrors.filter((error) => !error.includes('favicon'))).toEqual([]);
});
