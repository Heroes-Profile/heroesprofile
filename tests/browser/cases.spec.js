import { test, expect } from '@playwright/test';
import { preparePage, watchPage, waitForLoaders, readFilters, pressFilter } from './helpers.js';

/*
 * Flows the crawl can't express. When an exploratory sweep finds a new kind of bug, add a case here,
 * or a general rule to the crawl when it holds on every page.
 */

async function open(page, url) {
    await preparePage(page);
    const watch = watchPage(page);
    await page.goto(url);
    await waitForLoaders(page, watch.problems, `opening ${url}`);
    return watch;
}

async function optionCount(page, text) {
    const box = page.locator(`[data-filter="${text}"]`).first();
    await box.locator(':scope > div').first().click();
    const count = await box.locator('input[type=radio], input[type=checkbox]').count();
    await page.evaluate(() => document.body.click());
    return count;
}

async function pickOption(page, text, label) {
    const box = page.locator(`[data-filter="${text}"]`).first();
    await box.locator(':scope > div').first().click();
    await box.locator('label', { hasText: label }).first().click();
    await page.evaluate(() => document.body.click());
}

test('Drafter: the filters lock once a draft starts, and Reset draft unlocks them', async ({ page }) => {
    const watch = await open(page, '/Drafter');

    const filterBar = page.locator('[data-filter-kind]').first();
    await expect(filterBar).toBeVisible();
    expect(await filterBar.evaluate(el => !!el.closest('[inert]'))).toBe(false);

    await page.locator('button.rounded-full', { has: page.locator('img') }).first().click();
    await expect.poll(() => filterBar.evaluate(el => !!el.closest('[inert]'))).toBe(true);

    await page.getByText('Reset draft', { exact: true }).click();
    await expect.poll(() => filterBar.evaluate(el => !!el.closest('[inert]'))).toBe(false);

    expect(watch.problems).toEqual([]);
});

test('Leaderboard: Type decides which dropdowns show', async ({ page }) => {
    const watch = await open(page, '/Global/Leaderboard?type=Hero');
    let filters = (await readFilters(page)).map(f => f.text);
    expect(filters).toContain('Heroes');

    await page.goto('/Global/Leaderboard?type=Player');
    await waitForLoaders(page, watch.problems, 'opening the Player leaderboard');
    filters = (await readFilters(page)).map(f => f.text);
    expect(filters).not.toContain('Heroes');

    expect(watch.problems).toEqual([]);
});

test('Other esports: picking a Series narrows Tournaments', async ({ page }) => {
    const watch = await open(page, '/Esports/Other');

    const before = await optionCount(page, 'Tournaments');
    await pickOption(page, 'Series', 'HGC');
    const after = await optionCount(page, 'Tournaments');

    expect(after).toBeGreaterThan(0);
    expect(after).toBeLessThan(before);
    expect(watch.problems).toEqual([]);
});

test('CCL: Organizations lists only the chosen season\'s teams', async ({ page }) => {
    const watch = await open(page, '/Esports/CCL?tab=recentMatches');

    const options = await optionCount(page, 'Organizations');
    expect(options).toBeGreaterThan(0);

    const filters = await readFilters(page);
    const season = filters.find(f => f.text === 'Seasons')?.value;
    const teams = await page.evaluate(async season => {
        const response = await fetch('/api/v1/esports/ccl/organizations', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
            body: JSON.stringify({ season }),
        });
        return response.json();
    }, season);
    expect(options).toBe(new Set(teams.map(team => team.team_name)).size);

    expect(watch.problems).toEqual([]);
});

test('Analytics sample: all maps survives a reload', async ({ page }) => {
    const watch = await open(page, '/Analytics/Sample?tab=deaths');

    const map = page.locator('[data-filter="Map"]').first();
    await map.locator(':scope > div').first().click();
    await map.locator('input[type=checkbox]:checked').first().click();
    await page.evaluate(() => document.body.click());
    await pressFilter(page);
    await waitForLoaders(page, watch.problems, 'filtering to all maps');

    expect(new URL(page.url()).searchParams.get('game_map')).toBe('all');

    await page.reload();
    await waitForLoaders(page, watch.problems, 'reloading');
    expect(JSON.parse(await map.getAttribute('data-value'))).toEqual([]);

    expect(watch.problems).toEqual([]);
});

test('Esports player Talents: asks the league, never the main site\'s builds', async ({ page }) => {
    const watch = await open(page, '/Esports/NGS/Player/Linehouse%231400/621300/Talents?season=9');

    const mainSite = watch.requests.filter(request => request.path.startsWith('/api/v1/player/'));
    expect(mainSite, 'requests to the main site\'s player endpoints').toEqual([]);
    expect(watch.requests.some(request => request.path.startsWith('/api/v1/esports/'))).toBe(true);
    expect(watch.problems).toEqual([]);
});
