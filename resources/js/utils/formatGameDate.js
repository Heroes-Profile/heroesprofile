import { siteLocale } from './translate';

// A bare "YYYY-MM-DD" is a calendar day, shown as that day in every time zone
const DAY_ONLY = /^\d{4}-\d{2}-\d{2}$/;

// Server dates: "YYYY-MM-DD HH:MM:SS" (game_date and the like) is UTC; ISO strings carry their own offset
function toDate(value) {
  if (value instanceof Date) {
    return value;
  }
  // A moment object
  if (typeof value?.toDate === 'function') {
    return value.toDate();
  }
  const text = String(value);
  if (DAY_ONLY.test(text)) {
    return new Date(text + 'T00:00:00Z');
  }
  if (/(Z|[+-]\d{2}:?\d{2})$/i.test(text)) {
    return new Date(text);
  }
  return new Date(text.replace(' ', 'T') + 'Z');
}

// A date in the viewer's own format and time zone: the site language on translated pages, the browser's locale on English ones
export function formatDate(value, options = { dateStyle: 'medium' }) {
  if (value === null || value === undefined || value === '') {
    return '';
  }
  const date = toDate(value);
  if (isNaN(date.getTime())) {
    return String(value);
  }
  if (DAY_ONLY.test(String(value))) {
    // A calendar day has no time to show
    const { timeStyle, hour, minute, second, ...dayOptions } = options;
    return new Intl.DateTimeFormat(siteLocale(), { ...dayOptions, timeZone: 'UTC' }).format(date);
  }
  return new Intl.DateTimeFormat(siteLocale(), { ...options, timeZone: options.timeZone || undefined }).format(date);
}

// A game's date and time, short. Plain text, so it can go inside a translated sentence
export function formatGameDate(dateString) {
  return formatDate(dateString, { dateStyle: 'short', timeStyle: 'medium' });
}

// "3 days ago": words, so in the site language even on English pages
export function timeAgo(value, now = Date.now()) {
  if (!value) {
    return '';
  }
  const seconds = Math.round((toDate(value).getTime() - now) / 1000);
  const [unit, size] = [['year', 31536000], ['month', 2592000], ['day', 86400], ['hour', 3600], ['minute', 60], ['second', 1]].find(([, s]) => Math.abs(seconds) >= s) || ['second', 1];
  return new Intl.RelativeTimeFormat(document.documentElement.lang || 'en', { numeric: 'auto' }).format(Math.round(seconds / size), unit);
}
