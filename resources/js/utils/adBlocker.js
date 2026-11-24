import { ref } from 'vue';

// Remembered for a day across tabs, so ad slots don't reserve space on every new tab.
// "script" = the ad manager failed to load, "empty" = it loaded but a placement stayed empty.
const KEY = 'ad-blocker-detected';
const TTL = 24 * 60 * 60 * 1000;

function stored() {
  try {
    const value = JSON.parse(localStorage.getItem(KEY));
    return value && Date.now() - value.at < TTL ? value : null;
  } catch (e) {
    return null;
  }
}

export const adBlocker = ref(stored() !== null || window.adManagerState === 'blocked');

export function markAdBlocker(by) {
  adBlocker.value = true;
  try {
    localStorage.setItem(KEY, JSON.stringify({ by, at: Date.now() }));
  } catch (e) {}
}

function onAdManagerState() {
  if (window.adManagerState === 'blocked') {
    markAdBlocker('script');
  } else if (window.adManagerState === 'loaded' && stored()?.by === 'script') {
    // Blocker turned off: ads come back from the next page on
    try {
      localStorage.removeItem(KEY);
    } catch (e) {}
  }
}

onAdManagerState();
window.addEventListener('ad-manager-state', onAdManagerState);
