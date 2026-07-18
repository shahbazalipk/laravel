import { expect, test } from '@playwright/test';

const emailStepUrl = process.env.PLAYWRIGHT_REGISTRATION_EMAIL_URL;

test.describe('registration category-first flow', () => {
    test.skip(
        !emailStepUrl,
        'Set PLAYWRIGHT_REGISTRATION_EMAIL_URL to an active registration email-step URL.',
    );

    test('progresses Email → Category → Information → Confirmation', async ({ page }) => {
        const errors: string[] = [];
        page.on('console', (message) => {
            if (message.type() === 'error') errors.push(message.text());
        });
        page.on('pageerror', (error) => errors.push(error.message));

        await page.goto(emailStepUrl!);

        const progress = page.getByTestId('wizard-progress');
        await expect(progress).toContainText('Email');
        await expect(progress).toContainText('Category');
        await expect(progress).toContainText('Information');
        await expect(progress).toContainText('Confirmation');

        const labels = await progress.locator('li').allTextContents();
        expect(labels.join(' ')).toMatch(/Email.*Category.*Information.*Confirmation/s);

        const uniqueEmail = `category-first-${Date.now()}@example.com`;
        await page.locator('input[name="email"]').fill(uniqueEmail);
        await page.getByRole('button', { name: /continue/i }).click();

        await expect(page).toHaveURL(/\/step\/category/);
        await expect(page.getByTestId('wizard-category-form')).toBeVisible();

        const category = page.locator('.category-radio').first();
        await category.check();
        await page.getByTestId('wizard-category-continue').click();

        await expect(page).toHaveURL(/\/step\/information/);
        await expect(page.getByTestId('wizard-information-form')).toBeVisible();
        await expect(page.getByRole('link', { name: 'Back' })).toHaveAttribute(
            'href',
            /\/step\/category/,
        );

        await page.getByTestId('wizard-first-name').fill('Playwright');
        await page.getByTestId('wizard-last-name').fill('Category First');
        await page.getByTestId('wizard-phone').fill('+923001234567');
        await page.getByTestId('wizard-company-name').fill('Test Company');
        await page.getByTestId('wizard-industry').selectOption({ index: 1 });

        const additionalRequired = page
            .getByTestId('wizard-information-form')
            .locator('input[required]:visible, select[required]:visible, textarea[required]:visible');
        for (let index = 0; index < (await additionalRequired.count()); index++) {
            const field = additionalRequired.nth(index);
            if (await field.inputValue()) continue;

            const tag = await field.evaluate((element) => element.tagName.toLowerCase());
            const type = (await field.getAttribute('type')) ?? '';
            if (tag === 'select') {
                await field.selectOption({ index: 1 });
            } else if (type === 'url') {
                await field.fill('https://example.com/profile');
            } else if (type !== 'file') {
                await field.fill('Playwright test value');
            }
        }

        await page.getByTestId('wizard-information-continue').click();
        await expect(page).toHaveURL(/\/step\/confirmation/);
        await expect(page.getByTestId('wizard-review')).toContainText('Playwright');
        await expect(page.getByTestId('wizard-review')).toContainText('Test Company');
        await expect(page.getByRole('link', { name: 'Back' })).toHaveAttribute(
            'href',
            /\/step\/information/,
        );

        await page.setViewportSize({ width: 390, height: 844 });
        const bodyWidth = await page.locator('body').evaluate((element) => element.scrollWidth);
        expect(bodyWidth).toBeLessThanOrEqual(391);
        expect(errors.filter((error) => !error.includes('favicon'))).toEqual([]);
    });
});
