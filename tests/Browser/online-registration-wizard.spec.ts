import { expect, test, type Page } from '@playwright/test';

const slug = process.env.PLAYWRIGHT_ONLINE_SLUG ?? 'tech-trip';
const ctx = process.env.PLAYWRIGHT_EVENT_CTX ?? '';

function onlinePath(path = ''): string {
    const base = `/online/${slug}${path}`;
    return ctx ? `${base}${base.includes('?') ? '&' : '?'}ctx=${encodeURIComponent(ctx)}` : base;
}

async function collectConsoleErrors(page: Page): Promise<string[]> {
    const errors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            errors.push(message.text());
        }
    });
    page.on('pageerror', (error) => {
        errors.push(error.message);
    });
    return errors;
}

async function completeThroughCategory(page: Page, email: string): Promise<void> {
    await page.goto(onlinePath());
    await expect(page).toHaveURL(new RegExp(`/online/${slug}/step/email`));
    await expect(page.getByTestId('wizard-progress')).toBeVisible();
    await expect(page.getByTestId('event-details')).toBeVisible();
    if (await page.getByTestId('registration-contact-footer').count()) {
        await expect(page.getByTestId('registration-contact-footer')).toBeVisible();
    }
    await expect(page.getByTestId('wizard-email-form')).toBeVisible();

    await page.getByTestId('wizard-email-input').fill(email);
    await page.getByTestId('wizard-email-continue').click();

    await expect(page).toHaveURL(new RegExp(`/online/${slug}/[^/]+/step/information`));
    await expect(page.getByTestId('wizard-information-form')).toBeVisible();

    const informationUrl = page.url();
    await page.reload();
    await expect(page).toHaveURL(informationUrl);
    await expect(page.getByTestId('wizard-information-form')).toBeVisible();

    await page.getByTestId('wizard-first-name').fill('Play');
    await page.getByTestId('wizard-last-name').fill('Wright');
    await page.getByTestId('wizard-phone').fill('+971500000001');
    await page.getByTestId('wizard-company-name').fill('Playwright Labs');

    const industry = page.getByTestId('wizard-industry');
    const optionCount = await industry.locator('option').count();
    if (optionCount > 1) {
        await industry.selectOption({ index: 1 });
    }

    await page.getByTestId('wizard-information-continue').click();
    await expect(page).toHaveURL(new RegExp(`/online/${slug}/[^/]+/step/category`));
    await expect(page.getByTestId('wizard-category-list')).toBeVisible();

    await page.reload();
    await expect(page.getByTestId('wizard-category-list')).toBeVisible();

    const firstCategory = page.locator('input[data-testid^="wizard-category-"]').first();
    await firstCategory.check();
    await page.getByTestId('wizard-category-continue').click();

    await expect(page).toHaveURL(new RegExp(`/online/${slug}/[^/]+/step/confirmation`));
    await expect(page.getByTestId('wizard-review')).toBeVisible();
    await expect(page.getByTestId('wizard-total')).toBeVisible();
}

test.describe.configure({ mode: 'serial' });

test('online registration wizard is refresh-safe on desktop and mobile', async ({
    page,
}, testInfo) => {
    test.skip(!ctx && !process.env.PLAYWRIGHT_ALLOW_WITHOUT_CTX, 'Set PLAYWRIGHT_EVENT_CTX for tenant context.');

    const consoleErrors = await collectConsoleErrors(page);
    const email = `pw-wizard-${Date.now()}@example.com`;

    await completeThroughCategory(page, email);

    const confirmationUrl = page.url();
    await expect(confirmationUrl).toMatch(new RegExp(`/online/${slug}/[^/]+/step/confirmation`));

    // Refresh and cookie-less open should still restore via signed reg in the URL.
    await page.reload();
    await expect(page.getByTestId('wizard-confirmation-form')).toBeVisible();

    const cookies = await page.context().cookies();
    const resumeCookie = cookies.find((cookie) => cookie.name === 'registration_resume_token');
    if (resumeCookie) {
        await page.context().addCookies([
            {
                name: resumeCookie.name,
                value: 'invalid-token',
                url: process.env.PLAYWRIGHT_BASE_URL ?? 'http://127.0.0.1:8001',
            },
        ]);
    }
    const confirmationPath = new URL(confirmationUrl).pathname.replace(`/online/${slug}`, '');
    await page.goto(onlinePath(confirmationPath));
    await expect(page).toHaveURL(new RegExp(`/online/${slug}/[^/]+/step/confirmation`));
    await expect(page.getByTestId('wizard-confirmation-form')).toBeVisible();

    await page.getByTestId('wizard-step-information').click();
    await expect(page).toHaveURL(new RegExp(`/online/${slug}/[^/]+/step/information`));
    await expect(page.getByTestId('wizard-first-name')).toHaveValue('Play');

    await page.getByTestId('wizard-step-confirmation').click();
    await expect(page).toHaveURL(new RegExp(`/online/${slug}/[^/]+/step/confirmation`));
    await expect(page.getByTestId('wizard-terms')).toBeVisible();
    await page.setViewportSize(
        testInfo.project.name.includes('mobile')
            ? { width: 390, height: 844 }
            : { width: 1280, height: 720 },
    );
    await expect(page.getByTestId('wizard-panel')).toBeVisible();
    await expect(page.getByTestId('wizard-complete')).toBeVisible();

    await page.getByTestId('wizard-terms').check();
    await page.getByTestId('wizard-complete').click();

    await expect(page).toHaveURL(/\/register\/confirmation\//);
    await expect(page.getByText(/registration/i).first()).toBeVisible();
    if (await page.getByTestId('registration-contact-footer').count()) {
        await expect(page.getByTestId('registration-contact-footer')).toBeVisible();
    }
    await expect(page.getByTestId('start-new-registration')).toBeVisible();

    await page.getByTestId('start-new-registration').click();
    await expect(page).toHaveURL(new RegExp(`/online/${slug}/step/email`));
    await expect(page.getByTestId('wizard-email-form')).toBeVisible();

    expect(consoleErrors.filter((error) => !error.includes('favicon'))).toEqual([]);
});

test('same-browser resume restores the current wizard step', async ({ page, context }) => {
    test.skip(!ctx && !process.env.PLAYWRIGHT_ALLOW_WITHOUT_CTX, 'Set PLAYWRIGHT_EVENT_CTX for tenant context.');

    const email = `pw-resume-${Date.now()}@example.com`;
    await completeThroughCategory(page, email);

    const cookies = await context.cookies();
    expect(cookies.some((cookie) => cookie.name === 'registration_resume_token')).toBe(true);

    await page.goto(onlinePath());
    await expect(page).toHaveURL(new RegExp(`/online/${slug}/[^/]+/step/confirmation`));
    await expect(page.getByTestId('wizard-review')).toBeVisible();
});
