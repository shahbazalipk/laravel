import { expect, test } from '@playwright/test';

const slug = process.env.PLAYWRIGHT_ONLINE_SLUG ?? 'tech-trip';
const ctx = process.env.PLAYWRIGHT_EVENT_CTX ?? '';

function onlineEmailPath(): string {
    const base = `/online/${slug}/step/email`;
    return ctx ? `${base}?ctx=${encodeURIComponent(ctx)}` : base;
}

test('registration email page exposes social and SEO meta tags', async ({ page }) => {
    test.skip(!ctx && !process.env.PLAYWRIGHT_ALLOW_WITHOUT_CTX, 'Set PLAYWRIGHT_EVENT_CTX for tenant context.');

    const consoleErrors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            consoleErrors.push(message.text());
        }
    });
    page.on('pageerror', (error) => consoleErrors.push(error.message));

    await page.goto(onlineEmailPath());
    await expect(page.getByTestId('wizard-email-form')).toBeVisible();

    const ogTitle = page.locator('meta[property="og:title"]');
    const ogImage = page.locator('meta[property="og:image"]');
    const twitterCard = page.locator('meta[name="twitter:card"]');
    const canonical = page.locator('link[rel="canonical"]');
    const jsonLd = page.locator('script[type="application/ld+json"]');

    await expect(ogTitle).toHaveCount(1);
    await expect(ogTitle).not.toHaveAttribute('content', '');
    await expect(ogImage.first()).toHaveAttribute('content', /https?:\/\//);
    await expect(twitterCard).toHaveAttribute('content', /summary/);
    await expect(canonical).toHaveAttribute('href', new RegExp(`/online/${slug}/step/email`));
    await expect(jsonLd.first()).toContainText('"@type"');

    expect(consoleErrors.filter((error) => !error.includes('favicon'))).toEqual([]);
});
