import { createApp } from 'vue';
import axios from 'axios';

// Create a fresh Vue app instance
const app = createApp({});


// Register components
import ExampleComponent from './components/ExampleComponent.vue';
import MainPage from './components/MainPage.vue';


//Random Pieces - rename later to what you want and place in folder
import Infobox from './components/Infobox.vue';
import HeroBox from './components/HeroBox.vue';
import Filters from './components/Filters.vue';
import SingleSelectFilter from './components/SingleSelectFilter.vue';
import MultiSelectFilter from './components/MultiSelectFilter.vue';

//Global Pages
import GlobalHeroStats from './components/Global/Hero/GlobalHeroStats.vue';
import GlobalHeroMapStats from './components/Global/Hero/Map/GlobalHeroMapStats.vue';
import GlobalTalentsStats from './components/Global/Talents/GlobalTalentsStats.vue';
import GlobalLeaderboard from './components/Global/Leaderboard/GlobalLeaderboard.vue';
import GlobalMatchupsStats from './components/Global/Matchups/GlobalMatchupsStats.vue';
import GlobalMatchupsTalentsStats from './components/Global/Matchups/Talent/GlobalMatchupsTalentsStats.vue';
import CompositionsStats from './components/Global/Compositions/CompositionsStats.vue';


//Authentication
import ProfileSettings from './components/Profile/ProfileSettings.vue';
import Profile from './components/Profile/Profile.vue';
import BattlenetAuthenticate from './components/Battlenet/BattlenetAuthenticate.vue';



// Automatically register Vue components
const components = import.meta.globEager('./components/**/*.vue');
Object.entries(components).forEach(([path, definition]) => {
    app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
});

// Set up axios on Vue's prototype
app.config.globalProperties.$axios = axios;


// Attach the application instance to an HTML element with id "app"
app.mount('#app');

// Set up CSRF token for Axios
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

