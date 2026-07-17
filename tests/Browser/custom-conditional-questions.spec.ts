import { expect, test } from '@playwright/test';

const adminFormId = process.env.PLAYWRIGHT_CUSTOM_FORM_ID;
const informationUrl = process.env.PLAYWRIGHT_REGISTRATION_INFORMATION_URL;

test.describe('custom question builder', () => {
    test.skip(
        !process.env.PLAYWRIGHT_STORAGE_STATE || !adminFormId,
        'Set PLAYWRIGHT_STORAGE_STATE and PLAYWRIGHT_CUSTOM_FORM_ID for an existing form.',
    );

    test('authenticated builder is responsive and exposes all question controls', async ({
        page,
    }) => {
        const errors: string[] = [];
        page.on('console', (message) => {
            if (message.type() === 'error') errors.push(message.text());
        });
        page.on('pageerror', (error) => errors.push(error.message));

        await page.goto(`/admin/custom-forms/${adminFormId}/edit`);
        await expect(page).not.toHaveURL(/\/admin\/login/);
        await expect(page.getByTestId('add-question-panel')).toBeVisible();

        const fields = page.getByTestId('question-fields-new');
        const type = fields.locator('[data-question-type]');
        await expect(type).toBeVisible();

        for (const optionType of ['radio', 'select', 'checkbox']) {
            await type.selectOption(optionType);
            await expect(fields.locator('[data-options-section]')).toBeVisible();
            await expect(fields.locator('[data-options-list] [data-option-row]')).not.toHaveCount(0);
        }

        await type.selectOption('upload');
        await expect(fields.locator('[data-upload-validation]')).toBeVisible();
        await expect(fields.locator('[data-standard-validation]')).toBeHidden();

        const viewportWidth = page.viewportSize()?.width ?? 0;
        const bodyWidth = await page.locator('body').evaluate((element) => element.scrollWidth);
        expect(bodyWidth).toBeLessThanOrEqual(viewportWidth + 1);
        expect(errors.filter((error) => !error.includes('favicon'))).toEqual([]);
    });
});

test.describe('registration conditional questions', () => {
    test.skip(
        !informationUrl,
        'Set PLAYWRIGHT_REGISTRATION_INFORMATION_URL to a seeded draft information-step URL.',
    );

    test('conditional target visibility follows the source answer', async ({ page }) => {
        const sourceKey = process.env.PLAYWRIGHT_CONDITION_SOURCE_KEY ?? 'attending_dinner';
        const targetKey = process.env.PLAYWRIGHT_CONDITION_TARGET_KEY ?? 'decline_reason';
        const showValue = process.env.PLAYWRIGHT_CONDITION_SHOW_VALUE ?? 'no';
        const errors: string[] = [];
        page.on('console', (message) => {
            if (message.type() === 'error') errors.push(message.text());
        });
        page.on('pageerror', (error) => errors.push(error.message));

        await page.goto(informationUrl!);
        const source = page.locator(`[data-custom-question="${sourceKey}"]`);
        const target = page.locator(`[data-custom-question="${targetKey}"]`);
        await expect(source).toBeVisible();
        await expect(target).toBeHidden();

        await source.locator(`input[value="${showValue}"]`).check();
        await expect(target).toBeVisible();
        await expect(target.locator('input, select, textarea').first()).toBeEnabled();

        const otherOption = source.locator(`input:not([value="${showValue}"])`).first();
        if (await otherOption.count()) {
            await otherOption.check();
            await expect(target).toBeHidden();
            await expect(target.locator('input, select, textarea').first()).toBeDisabled();
        }

        expect(errors.filter((error) => !error.includes('favicon'))).toEqual([]);
    });
});
