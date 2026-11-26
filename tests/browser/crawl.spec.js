import { test, expect } from '@playwright/test';
import fs from 'node:fs';
import path from 'node:path';
import {
    preparePage, watchPage, waitForLoaders, readFilters, pickAnotherOption, pressFilter,
    compareFiltersWithRequest, hiddenSelections, REQUEST_FIELDS,
} from './helpers.js';

/*
 * Every page in pages.json (written by tests/Feature/Characterization/BrowserPagesTest from the routes), each tab
 * of it, each filter on it: change it, press Filter, and check what went out and what came back.
 *
 * CRAWL_ONLY=<regex> limits the run to matching paths.
 */
const pagesFile = path.join(process.cwd(), 'tests/browser/pages.json');
if (!fs.existsSync(pagesFile)) {
    throw new Error('tests/browser/pages.json is missing: run php artisan hp:test user --filter=BrowserPagesTest first');
}
const only = process.env.CRAWL_ONLY ? new RegExp(process.env.CRAWL_ONLY) : null;
const pages = JSON.parse(fs.readFileSync(pagesFile, 'utf8')).filter(entry => !only || only.test(entry.path));

// A page's section tabs (tab-button stretched across the page)
async function sectionTabs(page) {
    return page.locator('div.w-full.bg-surface.flex-wrap > a[role="button"]').allTextContents()
        .then(texts => texts.map(text => text.trim()).filter(Boolean));
}

async function crawlFilters(page, watch, where) {
    const problems = watch.problems;
    const onLoad = await readFilters(page);
    problems.push(...hiddenSelections(onLoad).map(problem => `${where}, on load: ${problem}`));

    for (const filter of onLoad) {
        // The bar can change under us (Type swaps dropdowns): only filters still showing
        const current = (await readFilters(page)).find(f => f.text === filter.text && f.kind === filter.kind);
        if (!current) continue;

        const before = watch.requests.length;
        const picked = await pickAnotherOption(page, current);
        if (!picked) continue;

        const what = `${where}, ${filter.text} = ${picked.label || picked.code}`;
        await pressFilter(page);
        await waitForLoaders(page, problems, what);

        const now = await readFilters(page);
        problems.push(...hiddenSelections(now).map(problem => `${what}: ${problem}`));

        const sent = watch.requests.slice(before).filter(request => request.method === 'POST');
        if (!sent.length) {
            if (REQUEST_FIELDS[filter.text]) {
                problems.push(`${what}: no request went out`);
            }
            continue;
        }
        problems.push(...compareFiltersWithRequest(now, sent.at(-1)).map(problem => `${what}: ${problem}`));
    }
}

/** A page that writes its filters to the URL reloads into the same filters and sends the same request */
async function checkUrlRoundTrip(page, watch, startUrl) {
    const url = page.url();
    if (url === startUrl || !new URL(url).search) return;

    const filtersBefore = await readFilters(page);
    const lastBefore = watch.requests.filter(request => request.method === 'POST').at(-1);

    await page.reload();
    await waitForLoaders(page, watch.problems, `reloading ${url}`);

    const filtersAfter = await readFilters(page);
    const byText = list => Object.fromEntries(list.map(f => [`${f.kind}:${f.text}`, f.kind === 'multi' ? JSON.stringify(JSON.parse(f.value || '[]').map(String).sort()) : f.value]));
    const before = byText(filtersBefore);
    const after = byText(filtersAfter);
    for (const [key, value] of Object.entries(before)) {
        if (key in after && after[key] !== value) {
            watch.problems.push(`reloading ${url}: "${key.split(':')[1]}" was ${value}, came back ${after[key]}`);
        }
    }

    if (!lastBefore || !(await pressFilter(page))) return;
    const count = watch.requests.length;
    await waitForLoaders(page, watch.problems, `Filter after reloading ${url}`);
    const lastAfter = watch.requests.slice(count).filter(request => request.method === 'POST' && request.path === lastBefore.path).at(-1);
    if (lastAfter && JSON.stringify(lastAfter.body) !== JSON.stringify(lastBefore.body)) {
        watch.problems.push(`reloading ${url} and pressing Filter sent ${JSON.stringify(lastAfter.body)}, before the reload it sent ${JSON.stringify(lastBefore.body)}`);
    }
}

for (const entry of pages) {
    test(entry.path, async ({ page }) => {
        await preparePage(page);
        const watch = watchPage(page);

        await page.goto(entry.path);
        const startUrl = page.url();
        await waitForLoaders(page, watch.problems, `opening ${entry.path}`);

        const tabs = await sectionTabs(page);
        if (!tabs.length) {
            await crawlFilters(page, watch, entry.path);
            await checkUrlRoundTrip(page, watch, startUrl);
        }
        for (const tab of tabs) {
            await page.locator('div.w-full.bg-surface.flex-wrap > a[role="button"]', { hasText: tab }).first().click();
            await waitForLoaders(page, watch.problems, `opening the ${tab} tab`);
            await crawlFilters(page, watch, `${entry.path} [${tab}]`);
        }

        expect(watch.problems, `problems on ${entry.path}`).toEqual([]);
    });
}
