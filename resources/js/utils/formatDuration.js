import { siteLocale } from './translate';

const UNITS = [[86400, 'days', 'd'], [3600, 'hours', 'h'], [60, 'minutes', 'm']];

// A length of time as days, hours, minutes and seconds, starting from the first unit that isn't 0.
// English pages: "14h, 10m, 41s". Other languages use the browser's own wording, e.g. "14시간 10분 41초"
// in Korean, and fall back to the English style where the browser can't format durations.
export function formatDuration(totalSeconds) {
  let seconds = Math.max(0, Math.floor(Number(totalSeconds) || 0));
  const parts = {};
  for (const [size, unit] of UNITS) {
    const count = Math.floor(seconds / size);
    seconds %= size;
    if (count || Object.keys(parts).length) {
      parts[unit] = count;
    }
  }
  parts.seconds = seconds;

  const locale = siteLocale();
  if (locale && typeof Intl.DurationFormat === 'function') {
    try {
      // Every unit from the first non-zero one, including 0s in between, like the English style
      const always = Object.fromEntries(Object.keys(parts).map(unit => [unit + 'Display', 'always']));
      return new Intl.DurationFormat(locale, { style: 'narrow', ...always }).format(parts);
    } catch (error) {
      // Fall through to the English style
    }
  }

  const short = { days: 'd', hours: 'h', minutes: 'm', seconds: 's' };
  return Object.entries(parts).map(([unit, count]) => count + short[unit]).join(', ');
}

// The server's "84d, 18h, 44m, 24s" or "1m, 51s", reformatted for the page's language
export function formatDurationText(text) {
  const match = String(text ?? '').match(/^(?:(\d+)d, )?(?:(\d+)h, )?(?:(\d+)m, )?(\d+)s$/);
  if (!match) {
    return text;
  }
  const [, d = 0, h = 0, m = 0, s] = match;
  return formatDuration(Number(d) * 86400 + Number(h) * 3600 + Number(m) * 60 + Number(s));
}
