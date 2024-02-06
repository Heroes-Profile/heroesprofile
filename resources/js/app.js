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

app.config.globalProperties.$redirectToProfile = function (battletag, blizz_id, region) {
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
 
    window.location.href = '/Player/' + battletag + "/" + blizz_id + "/" + region;
};

app.use(flareVue);
// Attach the application instance to an HTML element with id "app"
app.mount('#app');

// Set up CSRF token for Axios
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}