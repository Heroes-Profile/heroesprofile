import { createApp } from 'vue';
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

// Automatically register Vue components
const components = import.meta.glob('./components/**/*.vue', { eager: true });
Object.entries(components).forEach(([path, definition]) => {
    app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
});

// The nav logo spins while any request is in flight
let requestsInFlight = 0;
function loadingStarted() {
  requestsInFlight++;
  document.documentElement.classList.add('nav-logo-loading');
}
function loadingFinished() {
  requestsInFlight = Math.max(requestsInFlight - 1, 0);
  if (!requestsInFlight) {
    document.documentElement.classList.remove('nav-logo-loading');
  }
}

// Registered before the app mounts, so requests made on mount count too
axios.interceptors.request.use((config) => {
  loadingStarted();
  return config;
});
axios.interceptors.response.use((response) => {
  loadingFinished();
  return response;
}, (error) => {
  loadingFinished();
  return Promise.reject(error);
});

// Set up axios on Vue's prototype
app.config.globalProperties.$axios = axios;
// The whole async query counts as loading, including the waits between status polls
app.config.globalProperties.$globalAsyncPost = (url, data, options = {}) => {
  loadingStarted();
  return globalAsyncPost(axios, url, data, options).finally(loadingFinished);
};
app.config.globalProperties.$formatLoadMeta = formatLoadMeta;
app.config.globalProperties.$formatResponseHeaders = formatResponseHeaders;
app.config.globalProperties.$createLoadMeta = createLoadMeta;

// Bar colour for a win rate: red under 45%, yellow under 50%, teal from 50%.
app.config.globalProperties.$winRateColor = function (winRate) {
  const value = Number(winRate);
  if (value < 45) {
    return 'red';
  }
  return value < 50 ? 'yellow' : 'teal';
};

app.config.globalProperties.$redirectToProfile = function (battletag, blizz_id, region, redirect = true) {
  let data = {
    battletag: battletag,
    blizz_id: blizz_id,
    region: region,
    date: new Date().toISOString(),
  };
  if(battletag && blizz_id && region){
   const existingAccounts = [
      JSON.parse(Cookies.get('alt_search_account1') || 'null'),
      JSON.parse(Cookies.get('alt_search_account2') || 'null'),
      JSON.parse(Cookies.get('alt_search_account3') || 'null'),
    ];

    const accountIndex = existingAccounts.findIndex((account) => {
      return account && account.battletag === data.battletag && account.blizz_id === data.blizz_id;
    });

      if (accountIndex >= 0) {
        existingAccounts[accountIndex].region = region;
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
    if(redirect){
      window.location.href = '/Player/' + battletag + "/" + blizz_id + "/" + region;
    }
};


app.use(flareVue);
flare.beforeEvaluate = error => {
  if (
    error.message.includes("Failed to fetch") ||
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
