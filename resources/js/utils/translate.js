// Interface text in the chosen language (window.translations, from lang/{locale}.json); the English key itself when there's no translation.
// :name placeholders are filled from replace, e.g. translate(':count games', { count: 5 }).
export function translate(key, replace = {}) {
  let text = window.translations?.[key] || key;
  // Longest names first, so :team doesn't eat the start of :team2 (Laravel does the same)
  const names = Object.keys(replace).sort((a, b) => b.length - a.length);
  for (const name of names) {
    text = text.replaceAll(':' + name, replace[name]);
  }
  return text;
}

// The language picked on the site, for Intl/toLocale* dates; undefined on English pages so those keep the browser's own style
export function siteLocale() {
  const lang = document.documentElement.lang;
  return lang && lang !== 'en' ? lang : undefined;
}
