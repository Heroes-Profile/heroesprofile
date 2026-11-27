import { createApp, defineAsyncComponent } from 'vue';
import axios from 'axios';
import { globalAsyncPost, formatLoadMeta, formatResponseHeaders, createLoadMeta } from './utils/globalAsyncPost';
import Cookies from 'js-cookie';
import { flare } from "@flareapp/flare-client";
import { flareVue } from "@flareapp/flare-vue";

if (process.env.NODE_ENV === 'production') {
  flare.light();
}

// Create a fresh Vue app instance
const app = createApp({});

import '@fortawesome/fontawesome-free/css/all.css'

// Every <details> on the site slides open and shut
import { animateDetails } from './utils/animateDetails';
import { translate } from './utils/translate';
import { formatDate } from './utils/formatGameDate';
animateDetails();

// What the layouts mount loads with the page. panel-box is also picked by name in :is, which the build can't see.
// Inside .vue files, unplugin-vue-components imports each tag's component (vite.config.js).
const eagerComponents = import.meta.glob([
    './components/Nav/NavSearchFlyout.vue',
    './components/SearchComponent.vue',
    './components/LanguagePicker.vue',
    './components/RemoveBattletagNav.vue',
    './components/MobileNavHack.vue',
    './components/OwnerSparks.vue',
    './components/BasilIcon.vue',
    './components/CustomButton.vue',
    './components/RoundImage.vue',
    './components/TranslationNotice.vue',
    './components/HeaderAlert.vue',
    './components/FooterPopupDisclaimer.vue',
    './components/LoginPopup.vue',
    './components/Api/ApiLoginPopup.vue',
    './components/HelpChat.vue',
    './components/RedesignCredit.vue',
    './components/Ads/RichMediaAd.vue',
    './components/Ads/HorizontalBannerAd.vue',
    './components/Ads/DynamicBannerAd.vue',
    './components/PanelBox.vue',
    './components/LoadingComponent.vue',
    './components/ErrorCard.vue',
], { eager: true });
// The rest, which the page views mount, load as their own chunks
const lazyComponents = import.meta.glob('./components/**/*.vue');

// A chunk from before a deploy is gone: reload once for the new build.
// The error still reaches Flare through Vue's error handler; the delay lets that report send.
// Not again within a minute, so a chunk that's really broken can't loop.
function reloadForNewBuild() {
  if (Date.now() - Number(sessionStorage.getItem('chunkReload')) < 60000) {
    return;
  }
  sessionStorage.setItem('chunkReload', Date.now());
  setTimeout(() => window.location.reload(), 500);
}
window.addEventListener('vite:preloadError', reloadForNewBuild);

const componentName = path => path.split('/').pop().replace(/\.\w+$/, '');
Object.entries(eagerComponents).forEach(([path, definition]) => {
    app.component(componentName(path), definition.default);
});
Object.entries(lazyComponents).forEach(([path, loader]) => {
    if (eagerComponents[path]) {
        return;
    }
    app.component(componentName(path), defineAsyncComponent({
        loader,
        loadingComponent: eagerComponents['./components/LoadingComponent.vue'].default,
        delay: 200,
        onError(error, retry, fail) {
            reloadForNewBuild();
            fail();
        },
    }));
});

// Set up axios on Vue's prototype
app.config.globalProperties.$axios = axios;
app.config.globalProperties.$globalAsyncPost = (url, data, options = {}) => globalAsyncPost(axios, url, data, options);
app.config.globalProperties.$formatLoadMeta = formatLoadMeta;
app.config.globalProperties.$formatResponseHeaders = formatResponseHeaders;
app.config.globalProperties.$createLoadMeta = createLoadMeta;
// What a page's failed load shows in its error-card: the HTTP status (500 for a timeout or a failed background job),
// for a query stopped as too large the server's reason, and what Try again runs. Null for a request the page cancelled itself.
app.config.globalProperties.$loadError = (error, retry) => {
  if (axios.isCancel(error)) {
    return null;
  }
  const status = error?.response?.status || 500;
  const reason = error?.response?.data?.error;
  return { status, message: status === 422 && typeof reason === 'string' ? reason : null, retry };
};

// Teams, game length and download state for match rows. They depend on the viewer,
// so they're fetched apart from the cached page data and merged into the rows.
app.config.globalProperties.$loadMatchDetails = async function (games, blizzid, region) {
  if (!games || !games.length) {
    return;
  }
  try {
    const response = await axios.post('/api/v1/player/match/history/details', {
      blizz_id: blizzid,
      region: region,
      replayIDs: games.map(game => game.replayID),
    });
    games.forEach(game => Object.assign(game, response.data[game.replayID] || {}));
  } catch (error) {
    // the rows still show without teams
  }
};

// Bar colour for a win rate: red under 45%, yellow under 50%, teal from 50%.
app.config.globalProperties.$winRateColor = function (winRate) {
  const value = Number(winRate);
  if (value < 45) {
    return 'red';
  }
  return value < 50 ? 'yellow' : 'teal';
};

// NGS names a team that quits "Team (Withdrawn S22)". Returns the real name and the season it withdrew in, if any.
app.config.globalProperties.$ngsTeam = function (teamName) {
  const match = String(teamName ?? '').match(/^(.*?)\s*\(Withdrawn S(\d+)\)\s*$/i);
  return match ? { name: match[1], withdrawnSeason: match[2] } : { name: teamName, withdrawnSeason: null };
};

app.config.globalProperties.$t = translate;
// A date in the viewer's own format and time zone; see utils/formatGameDate
app.config.globalProperties.$formatDate = formatDate;

// A hero or map name (or an object with .name) in the chosen language, from window.gameStrings; English when there's no translation.
app.config.globalProperties.$heroName = function (hero) {
  const name = typeof hero === 'object' && hero !== null ? hero.name : hero;
  return window.gameStrings?.heroes?.[name] ?? name;
};
app.config.globalProperties.$mapName = function (map) {
  const name = typeof map === 'object' && map !== null ? map.name : map;
  return window.gameStrings?.maps?.[name] ?? name;
};

// A talent or ability field in the chosen language (localized_* from the server), else the English one.
app.config.globalProperties.$gameText = function (row, field) {
  return row?.['localized_' + field] || row?.[field];
};
app.config.globalProperties.$talentName = function (talent) {
  return talent?.localized_name || talent?.title || talent?.talent_name || '';
};

// An esports player page's league, from its esport and esportfilters (HI's tournament, Other's series and icon).
// params go on every request and link, so HI keeps ?tournament=.
app.config.globalProperties.$esportLeague = function (esport, esportfilters) {
  const tournament = esportfilters?.tournament;
  const series = esportfilters?.series;
  const leagues = {
    NGS: { name: 'NGS', headingImage: '/images/NGS/ngs_large.png', loaderImage: '/images/NGS/no-image-clipped.png' },
    CCL: { name: 'CCL', headingImage: '/images/CCL/600-600-HHE_CCL_Logo_rectangle.png' },
    MastersClash: { name: 'Masters Clash', headingImage: '/images/MCL/no-image.png' },
    HeroesInternational: { name: tournament === 'nationscup' ? 'Nations Cup' : 'Heroes International', headingImage: '/images/HI/heroes_international.png' },
    Other: { name: series, headingImage: '/images/EsportOther/' + (esportfilters?.seriesimage || 'heroesprofilelogo.png') },
  };
  const league = leagues[esport];
  const root = esport === 'Other' ? `/Esports/Other/${encodeURIComponent(series)}` : `/Esports/${esport}`;
  const params = tournament ? { tournament } : {};
  const query = tournament ? `?tournament=${tournament}` : '';
  return {
    ...league,
    loaderImage: league.loaderImage ?? league.headingImage,
    root,
    headingImageUrl: root + query,
    api: esport === 'Other' ? `/api/v1/esports/other/${encodeURIComponent(series)}/player` : `/api/v1/esports/${esport.toLowerCase()}/player`,
    params,
    query,
  };
};

// The header's three recent players. An esports player is stored with its league ('NGS', 'CCL',
// 'MastersClash', 'HeroesInternational' + tournament, 'Other' + series) and no region,
// and is a separate entry from the same player's main-site one.
function rememberSearchAccount(data) {
  const existingAccounts = [
    JSON.parse(Cookies.get('alt_search_account1') || 'null'),
    JSON.parse(Cookies.get('alt_search_account2') || 'null'),
    JSON.parse(Cookies.get('alt_search_account3') || 'null'),
  ];

  const accountIndex = existingAccounts.findIndex((account) => {
    // As text: RememberViewedPlayer stores a number, links often pass a string
    return account && account.battletag === data.battletag && String(account.blizz_id) === String(data.blizz_id)
      && (account.league || null) === (data.league || null)
      && (account.tournament || null) === (data.tournament || null)
      && (account.series || null) === (data.series || null);
  });

  if (accountIndex >= 0) {
    existingAccounts[accountIndex].region = data.region;
    existingAccounts[accountIndex].date = data.date;
  } else {
    // Find the oldest account or use the first available slot
    const oldestAccountIndex = existingAccounts.findIndex((account) => {
      return !account;
    });

    if (oldestAccountIndex >= 0) {
      // Use the first available slot
      existingAccounts[oldestAccountIndex] = data;
    } else {
      // Find the oldest account and overwrite it
      const oldestAccount = existingAccounts.reduce((oldest, current) => {
        if (!oldest || new Date(current.date) < new Date(oldest.date)) {
          return current;
        }
        return oldest;
      }, null);

      if (oldestAccount) {
        Object.keys(oldestAccount).forEach((key) => delete oldestAccount[key]);
        Object.assign(oldestAccount, data);
      }
    }
  }
  existingAccounts.forEach((account, index) => {
    if (account) {
      Cookies.set('alt_search_account' + (index + 1), JSON.stringify(account), { sameSite: 'Strict', path: '/', expires: 90 });
    }
  });
}

app.config.globalProperties.$redirectToProfile = function (battletag, blizz_id, region, redirect = true) {
  if(battletag && blizz_id && region){
    rememberSearchAccount({
      battletag: battletag,
      blizz_id: blizz_id,
      region: region,
      date: new Date().toISOString(),
    });
  }
    if(redirect){
      window.location.href = '/Player/' + battletag + "/" + blizz_id + "/" + region;
    }
};

app.use(flareVue);
flare.beforeEvaluate = error => {
  if (
    // Still reports a chunk missing after a deploy
    (error.message.includes("Failed to fetch") && !error.message.includes("dynamically imported module")) ||
    error.message.includes("Label 'https' has already been declared") ||
    error.message.includes("NetworkError when attempting to fetch resource.") ||
    error.message.includes("The operation was aborted.") ||
    error.message.includes("The play() request was interrupted") ||
    error.message.includes("Unexpected end of input") ||
    error.message.includes("Load failed") ||
    error.message.includes("The request is not allowed by the user agent") ||
    error.message.includes("The media resource indicated by the src attribute or assigned media provider object was not suitable.") ||
    error.message.includes("Cannot redefine property: websredir") ||
    error.message.includes("Cannot redefine property: ethereum") ||
    error.message.includes("The fetching process for the media resource") ||
    error.message.includes("Failed to execute 'whenDefined'") ||
    error.message.includes("Node.removeChild: The node to be removed is not a child of this node") ||
    error.message.includes("Cannot read properties of undefined (reading 'push')") ||
    error.message.includes("Cannot redefine property: googletag") ||
    error.message.includes("Somehow the event source is null") ||
    error.message.includes("Failed to load because no supported source was found.") ||
    error.message.includes("Cannot read properties of undefined (reading 'nativeBack')") ||
    error.message.includes("null is not an object (evaluating 'e.source.postMessage')") ||
    error.message.includes('Permission denied to access property "then"')
  )
  { 
    return false; 
  } 
}; 


// Attach the application instance to an HTML element with id "app"
app.mount('#app');

// Set up CSRF token for Axios
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// A page left open past its session gets 419 on every POST. Fetch a fresh token and retry once.
let csrfRefresh = null;
axios.interceptors.response.use(null, async (error) => {
  const config = error.config;
  if (!error.response || error.response.status !== 419 || !config || config.__csrfRetried) {
    return Promise.reject(error);
  }
  config.__csrfRetried = true;

  try {
    // Concurrent 419s share one refresh
    csrfRefresh = csrfRefresh || axios.get('/Session/CsrfToken').finally(() => { csrfRefresh = null; });
    const freshToken = (await csrfRefresh).data.token;

    axios.defaults.headers.common['X-CSRF-TOKEN'] = freshToken;
    if (token) {
      token.content = freshToken;
    }
    config.headers['X-CSRF-TOKEN'] = freshToken;
  } catch (refreshError) {
    return Promise.reject(error);
  }

  return axios(config);
});
