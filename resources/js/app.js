import { createApp } from 'vue';
import axios from 'axios';
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
const components = import.meta.globEager('./components/**/*.vue');
Object.entries(components).forEach(([path, definition]) => {
    app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
});

// Set up axios on Vue's prototype
app.config.globalProperties.$axios = axios;

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

// Custom error handler to filter out specific errors
const customErrorHandler = function (error) {
  // Check if the error message contains the specific errors you want to ignore
  if (
    error.message.includes("The play() request was interrupted because the media was removed from the document") ||
    error.message.includes("Label 'https' has already been declared") ||
    error.message.includes("Somehow the event source is null") ||
    error.message.includes("Failed to fetch") ||
    error.message.includes("Load failed") ||
    error.message.includes("The play() request was interrupted by a call to pause(). https://goo.gl/LdLk22") ||
    error.message.includes("The operation was aborted.") ||
    error.message.includes("The operation was aborted.") ||
    error.message.includes("Node.removeChild: The node to be removed is not a child of this node") ||
    error.message.includes("The play() request was interrupted because video-only background media was paused to save power. https://goo.gl/LdLk22") ||
    error.message.includes("Cannot read properties of undefined (reading 'getReadyAds')") ||
    error.message.includes("Cannot redefine property: websredir") ||
    error.message.includes("Cannot read property 'getReadyAds' of undefined") ||
    error.message.includes("obtainVideoInfos is not defined") ||
    error.message.includes("The play() request was interrupted because the media was removed from the document.") ||
    error.message.includes("Cannot redefine property: googletag") ||
    error.message.includes("Cannot read properties of undefined (reading 'nativeBack')") ||
    error.message.includes('can\'t redefine non-configurable property "solana"') ||
    error.message.includes("Failed to load because no supported source was found.") ||
    error.message.includes("The fetching process for the media resource was aborted by the user agent at the user's request.") ||
    error.message.includes("The operation was aborted.") ||
    error.message.includes("The operation was aborted.") ||
    error.message.includes("The operation was aborted.") ||
    error.message.includes("The operation was aborted.") ||
    error.message.includes("Unexpected end of input")
    
  ) {
    return false; 
  }
  return true;
};


app.use(flareVue, {
  errorHandler: customErrorHandler
});

// Attach the application instance to an HTML element with id "app"
app.mount('#app');

// Set up CSRF token for Axios
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}