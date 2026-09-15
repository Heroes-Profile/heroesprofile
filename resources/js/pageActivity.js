import axios from 'axios';

// Tracks in-flight page data requests so things like the hidden Xal'atath eye can wait until a page has loaded.
// Background event calls are ignored so they never hold a page "busy".
const IGNORED = ['/Event/Xalatath/Totals', '/Flair/State', '/Event/Xalatath/Eye'];

let pending = 0;
let lastActivity = Date.now();

const isIgnored = (config) => IGNORED.some((path) => (config.url || '').includes(path));

function finish(config) {
  if (config && config.__pageActivityTracked) {
    config.__pageActivityTracked = false;
    pending = Math.max(0, pending - 1);
    lastActivity = Date.now();
  }
}

axios.interceptors.request.use((config) => {
  if (!isIgnored(config)) {
    config.__pageActivityTracked = true;
    pending++;
    lastActivity = Date.now();
  }
  return config;
});

axios.interceptors.response.use(
  (response) => {
    finish(response.config);
    return response;
  },
  (error) => {
    finish(error && error.config);
    return Promise.reject(error);
  }
);

/**
 * Resolves once the page has no data requests in flight, no loading spinner showing,
 * and has been quiet for `quietMs`. Gives up after `maxWaitMs` so a stuck request can't block forever.
 */
export function whenPageSettled({ quietMs = 1200, maxWaitMs = 30000 } = {}) {
  return new Promise((resolve) => {
    const start = Date.now();
    const check = () => {
      const settled = pending === 0
        && document.readyState === 'complete'
        && !document.querySelector('.loading-container')
        && Date.now() - lastActivity >= quietMs;

      if (settled || Date.now() - start >= maxWaitMs) {
        resolve();
        return;
      }
      setTimeout(check, 250);
    };
    check();
  });
}
