import { expect, test, type Page } from '@playwright/test';

const slug = process.env.PLAYWRIGHT_SINGLE_PAGE_SLUG ?? process.env.PLAYWRIGHT_ONLINE_SLUG ?? 'tech-trip';
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

test.describe.configure({ mode: 'serial' });

test('single-page registration form is complete and responsive', async ({ page }, testInfo) => {
    test.skip(
        !ctx && !process.env.PLAYWRIGHT_ALLOW_WITHOUT_CTX,
        'Set PLAYWRIGHT_EVENT_CTX and a single-page registration URL slug.',
    );
    test.skip(
        !process.env.PLAYWRIGHT_SINGLE_PAGE_SLUG && !process.env.PLAYWRIGHT_ALLOW_WITHOUT_CTX,
        'Set PLAYWRIGHT_SINGLE_PAGE_SLUG to a URL configured as single_page.',
    );

    const consoleErrors = await collectConsoleErrors(page);
    const email = `pw-single-${Date.now()}@example.com`;

    await page.setViewportSize(
        testInfo.project.name.includes('mobile')
            ? { width: 390, height: 844 }
            : { width: 1280, height: 900 },
    );

    await page.goto(onlinePath());
    await expect(page).toHaveURL(new RegExp(`/online/${slug}/form`));
    await expect(page.getByTestId('single-page-form')).toBeVisible();
    await expect(page.getByTestId('wizard-progress')).toHaveCount(0);

    await page.getByTestId('single-page-email').fill(email);
    await page.getByTestId('single-page-first-name').fill('Single');
    await page.getByTestId('single-page-last-name').fill('Page');
    await page.getByTestId('single-page-phone').fill('+971500000002');
    await page.getByTestId('single-page-company-name').fill('One Page Labs');

    const industry = page.getByTestId('single-page-industry');
    if ((await industry.locator('option').count()) > 1) {
        await industry.selectOption({ index: 1 });
    }

    const firstCategory = page.locator('input[data-testid^="single-page-category-"]').first();
    await firstCategory.check();
    await expect(page.getByTestId('single-page-total')).not.toHaveText('—');

    await page.getByTestId('single-page-terms').check();
    await page.getByTestId('single-page-submit').click();

    await expect(page).toHaveURL(/\/register\/confirmation\//);
    expect(consoleErrors.filter((error) => !error.includes('favicon'))).toEqual([]);
});

test('single-page registration can start fresh from an in-progress session', async ({ page }, testInfo) => {
    test.skip(
        !ctx && !process.env.PLAYWRIGHT_ALLOW_WITHOUT_CTX,
        'Set PLAYWRIGHT_EVENT_CTX and a single-page registration URL slug.',
    );
    test.skip(
        !process.env.PLAYWRIGHT_SINGLE_PAGE_SLUG && !process.env.PLAYWRIGHT_ALLOW_WITHOUT_CTX,
        'Set PLAYWRIGHT_SINGLE_PAGE_SLUG to a URL configured as single_page.',
    );

    const consoleErrors = await collectConsoleErrors(page);

    await page.setViewportSize(
        testInfo.project.name.includes('mobile')
            ? { width: 390, height: 844 }
            : { width: 1280, height: 900 },
    );

    await page.goto(onlinePath());
    await expect(page.getByTestId('single-page-form')).toBeVisible();

    // If a previous browser session restored a draft, start fresh clears it.
    if (await page.getByTestId('single-page-resume-banner').isVisible().catch(() => false)) {
        await expect(page.getByTestId('single-page-start-fresh')).toBeVisible();
        await page.getByTestId('single-page-start-fresh').click();
        await expect(page).toHaveURL(new RegExp(`/online/${slug}(/form)?`));
    }

    await page.goto(onlinePath('/new'));
    await expect(page).toHaveURL(new RegExp(`/online/${slug}/form`));
    await expect(page.getByTestId('single-page-resume-banner')).toHaveCount(0);
    await expect(page.getByTestId('single-page-form')).toBeVisible();
    await expect(page.getByTestId('single-page-email')).toHaveValue('');

    expect(consoleErrors.filter((error) => !error.includes('favicon'))).toEqual([]);
});
