import { config } from '@vue/test-utils';
import { vi } from 'vitest';

// jsdom has neither; the filter bar asks both whether it's on a phone
window.matchMedia = window.matchMedia || ((query) => ({
    matches: query.includes('min-width'),
    media: query,
    addEventListener() {},
    removeEventListener() {},
    addListener() {},
    removeListener() {},
}));
window.ResizeObserver = window.ResizeObserver || class {
    observe() {}
    unobserve() {}
    disconnect() {}
};

// The globals app.js gives every component
config.global.mocks = {
    $t: (text, replace = {}) => Object.entries(replace).reduce((out, [key, value]) => out.replace(':' + key, value), String(text ?? '')),
    $axios: { post: vi.fn(() => Promise.resolve({ data: {} })), get: vi.fn(() => Promise.resolve({ data: {} })) },
};
