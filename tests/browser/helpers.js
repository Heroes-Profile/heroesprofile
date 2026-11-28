// Shared by the crawl and the named cases: page setup, watching for errors, reading and driving the filter bar

// A stuck page: a loader still showing after this long
export const LOADER_TIMEOUT = 90 * 1000;

// Filter label => the request fields it can land in. Used to compare what a dropdown shows with what goes out.
export const REQUEST_FIELDS = {
    'Heroes': ['hero'],
    'Role': ['role'],
    'Regions': ['region'],
    'Region': ['region'],
    'Map': ['game_map', 'map'],
    'Game Type': ['game_type', 'gametype'],
    'Season': ['season'],
    'Seasons': ['season'],
    'Divisions': ['division'],
    'Teams': ['team'],
    'Organizations': ['team'],
    'Series': ['series'],
    'Tournaments': ['tournament'],
    'Stat Filter': ['statfilter'],
    'Team One Party': ['teamoneparty'],
    'Team Two Party': ['teamtwoparty'],
    'Mirror Matches': ['mirror'],
    'Minimum Games': ['minimum_games', 'minimumgames'],
    'Group Size': ['groupsize'],
};

const LOCAL = ['127.0.0.1', 'localhost', '[::1]'];

/** Ads and other third-party scripts are blocked; the Leaderboard's terms popup counts as accepted */
export async function preparePage(page) {
    await page.addInitScript(() => {
        localStorage.setItem('leaderboardpopup', 'true');
        localStorage.setItem('leaderboardpopupAgreed', 'true');
    });
    await page.route(url => !LOCAL.includes(url.hostname), route => route.abort());
}

/** Collects what counts as a failure on any page: site console errors, page errors, failed or rejected API calls */
export function watchPage(page) {
    const problems = [];
    const requests = [];

    page.on('pageerror', error => problems.push(`page error: ${error.message}`));
    page.on('console', message => {
        if (message.type() !== 'error') return;
        const text = message.text();
        // The blocked third-party scripts fail to load; that's ours, not the site's
        if (/ERR_FAILED|ERR_BLOCKED|net::|CORS|Failed to load resource/i.test(text)) return;
        problems.push(`console error: ${text}`);
    });
    page.on('response', async response => {
        const url = new URL(response.url());
        if (!LOCAL.includes(url.hostname) || !url.pathname.startsWith('/api/')) return;
        const request = response.request();
        let body = null;
        try { body = request.postDataJSON(); } catch { body = request.postData(); }
        const entry = { method: request.method(), path: url.pathname, status: response.status(), body };
        requests.push(entry);

        // Guests can't save settings; Filters.vue posts the advanced toggle anyway (the cookie keeps it)
        if (url.pathname === '/api/v1/profile/save/settings' && response.status() === 401) return;
        if (response.status() >= 400) {
            problems.push(`HTTP ${response.status()} from ${request.method()} ${url.pathname} ${JSON.stringify(body)}`);
            return;
        }
        try {
            const json = await response.json();
            if (json && json.status === 'failure to validate inputs') {
                problems.push(`failure to validate inputs from ${url.pathname}: ${JSON.stringify(json.errors || json)} sent ${JSON.stringify(body)}`);
            }
        } catch {
            // Not JSON
        }
    });

    return { problems, requests };
}

/** Waits until no loader shows; a loader still up after LOADER_TIMEOUT is a stuck page */
export async function waitForLoaders(page, problems, what) {
    await page.waitForLoadState('domcontentloaded');
    // Requests start from mounted(); give them a moment to put a loader up
    await page.waitForTimeout(300);
    try {
        await page.waitForFunction(() => {
            const visible = el => el.offsetParent !== null || getComputedStyle(el).position === 'fixed';
            return ![...document.querySelectorAll('img.logo-spin, .loading-container')].some(visible);
        }, null, { timeout: LOADER_TIMEOUT });
    } catch {
        problems.push(`loader still showing ${LOADER_TIMEOUT / 1000}s after ${what}`);
    }
    await page.waitForLoadState('networkidle', { timeout: 15000 }).catch(() => {});
}

/** Every filter dropdown on the page that's showing */
export async function readFilters(page) {
    return page.evaluate(() => [...document.querySelectorAll('[data-filter-kind]')]
        .filter(el => el.offsetParent !== null && !el.closest('[inert]'))
        .map(el => ({
            kind: el.dataset.filterKind,
            text: el.dataset.filter,
            value: el.dataset.value ?? '',
            // A single select shows its option in bold; the placeholder ("All") isn't
            showsOption: el.dataset.filterKind === 'single' ? !!el.querySelector('span.font-bold') : true,
            label: (el.querySelector('span.font-bold')?.textContent || '').trim(),
        })));
}

function filterLocator(page, filter) {
    return page.locator(`[data-filter-kind="${filter.kind}"][data-filter="${filter.text.replace(/"/g, '\\"')}"]`).first();
}

async function closeDropdowns(page) {
    await page.evaluate(() => document.body.click());
    await page.waitForTimeout(150);
}

/**
 * Picks an option other than the current one. Returns { code, label } of what was picked, or null when the
 * filter has nothing else to pick.
 */
export async function pickAnotherOption(page, filter) {
    const box = filterLocator(page, filter);
    await box.locator(':scope > div').first().click();
    await page.waitForTimeout(150);

    const picked = await box.evaluate((el, kind) => {
        const inputs = [...el.querySelectorAll(kind === 'single' ? 'input[type=radio]' : 'input[type=checkbox]')]
            .filter(input => input.value && input.value !== 'on' && !input.disabled);
        const current = el.dataset.value ?? '';
        const target = kind === 'single'
            ? inputs.find(input => input.value !== current)
            : inputs.find(input => !input.checked) || inputs.find(input => input.checked);
        if (!target) return null;
        target.click();
        return { code: target.value, label: (target.closest('label')?.textContent || '').trim() };
    }, filter.kind);

    await closeDropdowns(page);
    return picked;
}

/** Presses the bar's Filter button, when there is one (auto-apply bars apply as the dropdown closes) */
export async function pressFilter(page) {
    const button = page.getByRole('button', { name: 'Filter', exact: true }).filter({ visible: true }).first();
    if (await button.count() && await button.isEnabled()) {
        await button.click();
        return true;
    }
    return false;
}

function asList(value) {
    if (value === null || value === undefined || value === '') return [];
    return (Array.isArray(value) ? value : [value]).map(String);
}

/**
 * Each dropdown against the last request: a dropdown showing All while the request filters on something, or
 * showing an option the request doesn't carry, is a mismatch.
 */
export function compareFiltersWithRequest(filters, request) {
    const problems = [];
    if (!request || !request.body || typeof request.body !== 'object') return problems;

    for (const filter of filters) {
        const fields = REQUEST_FIELDS[filter.text];
        if (!fields) continue;
        const field = fields.find(name => Object.prototype.hasOwnProperty.call(request.body, name));
        if (!field) continue;

        const sent = asList(request.body[field]);
        const held = filter.kind === 'multi' ? asList(JSON.parse(filter.value || '[]')) : asList(filter.value);

        if (!held.length && sent.length) {
            problems.push(`"${filter.text}" shows All but ${request.path} sent ${field}=${JSON.stringify(request.body[field])}`);
        } else if (held.length && !sent.length) {
            problems.push(`"${filter.text}" holds ${JSON.stringify(held)} but ${request.path} sent no ${field}`);
        } else if (held.length && filter.kind === 'single' && !sent.includes(held[0]) && !(filter.label && sent.includes(filter.label))) {
            problems.push(`"${filter.text}" shows ${filter.label || held[0]} but ${request.path} sent ${field}=${JSON.stringify(request.body[field])}`);
        }
    }

    return problems;
}

/** A single select holding a value it can't show (it fell out of a narrowed list) */
export function hiddenSelections(filters) {
    return filters
        .filter(filter => filter.kind === 'single' && filter.value !== '' && !filter.showsOption)
        .map(filter => `"${filter.text}" shows All while holding ${filter.value}`);
}
