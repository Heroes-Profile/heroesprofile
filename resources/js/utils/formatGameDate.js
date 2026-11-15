import moment from 'moment-timezone';

// A game's date (stored in UTC) in the visitor's time zone: the site language's own short style,
// or MM/DD/YYYY h:mm:ss a on English pages. Plain text, so it can go inside a translated sentence.
export function formatGameDate(dateString) {
  const timezone = moment.tz.guess();
  const localDate = moment.tz(dateString, 'Atlantic/Reykjavik').clone().tz(timezone);

  const siteLocale = document.documentElement.lang;
  if (siteLocale && siteLocale !== 'en') {
    return new Intl.DateTimeFormat(siteLocale, { dateStyle: 'short', timeStyle: 'medium', timeZone: timezone }).format(localDate.toDate());
  }

  return localDate.format('MM/DD/YYYY h:mm:ss a');
}
