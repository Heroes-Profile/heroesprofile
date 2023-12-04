import { createApp } from 'vue';
import axios from 'axios';
import Cookies from 'js-cookie';

// Create a fresh Vue app instance
const app = createApp({});

import '@fortawesome/fontawesome-free/css/all.css'

// Register components
import ExampleComponent from './components/ExampleComponent.vue';
import MainPage from './components/MainPage.vue';
import MobileNavHack from './components/MobileNavHack.vue';

// Ads
import RichMediaAd from './components/Ads/RichMediaAd.vue';
import TakeoverAd from './components/Ads/TakeoverAd.vue';
import DynamicBannerAd from './components/Ads/DynamicBannerAd.vue';
import HorizontalBannerAd from './components/Ads/HorizontalBannerAd.vue';
import DynamicSquareAd from './components/Ads/DynamicSquareAd.vue';


//Compare Page
import Compare from './components/Compare/Compare.vue';
import HeroOrLeagueChoiceBox from './components/Compare/HeroOrLeagueChoiceBox.vue';


//Random Pieces
import PageHeading from './components/PageHeading.vue';
import Infobox from './components/Infobox.vue';
import RoundImage from './components/RoundImage.vue';
import HeroGroupBox from './components/HeroGroupBox.vue';
import GroupBox from './components/GroupBox.vue';
import RoleBox from './components/RoleBox.vue';
import CustomButton from './components/CustomButton.vue';
import TabButton from './components/TabButton.vue';
import GameSummaryBox from './components/GameSummaryBox.vue';
import SearchComponent from './components/SearchComponent.vue';
import ImageHoverBox from './components/ImageHoverBox.vue';
import FormatDate from './components/FormatDate.vue';
import NewUserPopup from './components/NewUserPopup.vue';
import ContactForm from './components/ContactForm.vue';

//Match Page
import SingleMatch from './components/SingleMatch.vue';


//Wrappers
import TalentImageWrapper from './components/Wrappers/TalentImageWrapper.vue';
import HeroImageWrapper from './components/Wrappers/HeroImageWrapper.vue';
import MapImageWrapper from './components/Wrappers/MapImageWrapper.vue';


//Charts
import BubbleChart from './components/Charts/BubbleChart.vue';
import BarChart from './components/Charts/BarChart.vue';
import LineChart from './components/Charts/LineChart.vue';
import DualLineChart from './components/Charts/DualLineChart.vue';

//Filtering
import Filters from './components/Filtering/Filters.vue';
import SingleSelectFilter from './components/Filtering/SingleSelectFilter.vue';
import MultiSelectFilter from './components/Filtering/MultiSelectFilter.vue';

import StatBox from './components/StatBox.vue';
import StatBarBox from './components/StatBarBox.vue';
import SearchedBattletagHolding from './components/SearchedBattletagHolding.vue';
import LoadingComponent from './components/LoadingComponent.vue';
import HeroSelection from './components/HeroSelection.vue';

//Global Pages
import GlobalHeroStats from './components/Global/Hero/GlobalHeroStats.vue';
import GlobalHeroMapStats from './components/Global/Hero/Map/GlobalHeroMapStats.vue';
import GlobalTalentsStats from './components/Global/Talents/GlobalTalentsStats.vue';
import GlobalLeaderboard from './components/Global/Leaderboard/GlobalLeaderboard.vue';
import GlobalMatchupsStats from './components/Global/Matchups/GlobalMatchupsStats.vue';
import GlobalMatchupsTalentsStats from './components/Global/Matchups/Talent/GlobalMatchupsTalentsStats.vue';
import CompositionsStats from './components/Global/Compositions/CompositionsStats.vue';
import GlobalTalentDetailsSection from './components/Global/Talents/GlobalTalentDetailsSection.vue';
import GlobalTalentBuildsSection from './components/Global/Talents/GlobalTalentBuildsSection.vue';
import GlolbalDraftStats from './components/Global/Draft/GlobalDraftStats.vue';
import GlobalPartyStats from './components/Global/Party/GlobalPartyStats.vue';
import GlobalExtraStats from './components/Global/Extra/GlobalExtraStats.vue';


import GlobalTalentsBuilder from './components/Global/Talents/Builder/GlobalTalentsBuilder.vue';
import TalentBuilderColumn from './components/Global/Talents/Builder/TalentBuilderColumn.vue';
import TalentBuilderClickBox from './components/Global/Talents/Builder/TalentBuilderClickBox.vue';


//Authentication
import ProfileSettings from './components/Profile/ProfileSettings.vue';
import BattlenetAuthenticate from './components/Battlenet/BattlenetAuthenticate.vue';

//Player Stas
import PlayerStats from './components/Player/PlayerStats.vue';
import FriendFoe from './components/Player/FriendFoe.vue';
import PlayerHeroesAllStats from './components/Player/Heroes/PlayerHeroesAllStats.vue';
import PlayerHeroSingleStats from './components/Player/Heroes/PlayerHeroSingleStats.vue';
import PlayerMatchup from './components/Player/PlayerMatchup.vue';
import PlayerRolesAllStats from './components/Player/Roles/PlayerRolesAllStats.vue';
import PlayerRoleSingleStats from './components/Player/Roles/PlayerRoleSingleStats.vue';
import PlayerMapsAllStats from './components/Player/Maps/PlayerMapsAllStats.vue';
import PlayerMapSingleStats from './components/Player/Maps/PlayerMapSingleStats.vue';
import PlayerTalents from './components/Player/PlayerTalents.vue';
import MmrData from './components/Player/MmrData.vue';
import PlayerMatchHistory from './components/Player/PlayerMatchHistory.vue';

//Esports
import EsportsMain from './components/Esports/EsportsMain.vue';
import EsportsTeams from './components/Esports/EsportsTeams.vue';
import EsportsRecentMatches from './components/Esports/EsportsRecentMatches.vue';
import EsportsHeroStats from './components/Esports/EsportsHeroStats.vue';
import EsportsTalentStats from './components/Esports/EsportsTalentStats.vue';
import EsportsSingleTeam from './components/Esports/EsportsSingleTeam.vue';
import EsportsPlayerStats from './components/Esports/Player/EsportsPlayerStats.vue';
import EsportsPlayerHeroStats from './components/Esports/Player/EsportsPlayerHeroStats.vue';
import EsportsPlayerMapStats from './components/Esports/Player/EsportsPlayerMapStats.vue';
import EsportsPlayerMatchHistory from './components/Esports/Player/EsportsPlayerMatchHistory.vue';
import EsportsOrganizations from './components/Esports/EsportsOrganizations.vue';


//NGS
import NgsMain from './components/Esports/NGS/NgsMain.vue';
import NgsDivisions from './components/Esports/NGS/NgsDivisions.vue';
import NgsSingleDivision from './components/Esports/NGS/NgsSingleDivision.vue';
import NgsStandings from './components/Esports/NGS/NgsStandings.vue';
import NgsSingleDivisionMatchHistory from './components/Esports/NGS/NgsSingleDivisionMatchHistory.vue';

//CCL
import CclMain from './components/Esports/CCL/CclMain.vue';

//Nut Cup
import NutCupMain from './components/Esports/NutCup/NutCupMain.vue';

//Masters Clash
import MastersClashMain from './components/Esports/MastersClash/MastersClashMain.vue';

//Heroes International
import HeroesInternationalEntry from './components/Esports/HeroesInternational/HeroesInternationalEntry.vue';
import HeroesInternationalMain from './components/Esports/HeroesInternational/HeroesInternationalMain.vue';
import HeroesInternationalNationsCup from './components/Esports/HeroesInternational/HeroesInternationalNationsCup.vue';



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

// Attach the application instance to an HTML element with id "app"
app.mount('#app');

// Set up CSRF token for Axios
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

