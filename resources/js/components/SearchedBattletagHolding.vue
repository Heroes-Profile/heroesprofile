<template>
  <div>
    <div v-if="battletagresponse">
      <div v-if="battletagresponse.length > 1">
        <div 
          class="bg-blue p-4 rounded mb-4 w-[500px] flex flex-col items-center cursor-pointer" 
          v-for="(item, index) in battletagresponse" 
          :key="index" 
          @click="redirectToProfile(item.battletag, item.blizz_id, item.region)"
        >
          <div>{{ item.battletagShort }} ({{ item.regionName }})</div>
          <div>{{ item.latest_game }}</div>
          <div>Games Played: {{ item.totalGamesPlayed }}</div>
          <div>{{ item.latestMap.name }}</div>
          <div><hero-image-wrapper :hero="item.latestHero"></hero-image-wrapper></div>
        </div>
      </div>
    </div>
    <div v-else>
      <loading-component :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
    </div>


  </div>
</template>

<script>
import Cookies from 'js-cookie';

export default {
  name: 'SearchedBattletagHolding',
  components: {
  },
  props: {
    userinput: String,
    type: String,
  },
  data(){
    return {
      battletagresponse: null,
      alt_search_account1_exists: false,
      alt_search_account2_exists: false,
      alt_search_account3_exists: false,
    }
  },
  created(){
    this.alt_search_account1_exists = !!Cookies.get('alt_search_account1');
    this.alt_search_account2_exists = !!Cookies.get('alt_search_account2');
    this.alt_search_account3_exists = !!Cookies.get('alt_search_account3');
  },
  mounted() {
    this.getData();
  },
  computed: {
    isBattletagReponseValid(){
      return this.battletagresponse[0] && this.battletagresponse[0].battletag && this.battletagresponse[0].blizz_id !== undefined && this.battletagresponse[0].region !== undefined;
    }
  },
  watch: {
  },
  methods: {
    async getData(){
      try{
        const response = await this.$axios.post("/api/v1/battletag/search", {
          userinput: this.userinput,
        });
        this.battletagresponse = response.data;
        if(this.isBattletagReponseValid) {
          if(this.battletagresponse.length == 1){
            this.redirectToProfile(this.battletagresponse[0].battletag, this.battletagresponse[0].blizz_id, this.battletagresponse[0].region);
          }
        } else {
          //Do something here
        }
      }catch(error){
        this.battletagresponse = "Invalid input: '%', '?' and ' ' are invalid inputs";
      }
    },
    redirectToProfile(battletag, blizz_id, region) {
      let data = {
        battletag: battletag.split('#')[0],
        blizz_id: blizz_id,
        region: region,
        date: new Date().toISOString(),
      };

      const existingAccounts = [
        JSON.parse(Cookies.get('alt_search_account1') || 'null'),
        JSON.parse(Cookies.get('alt_search_account2') || 'null'),
        JSON.parse(Cookies.get('alt_search_account3') || 'null'),
      ];

      const accountIndex = existingAccounts.findIndex((account) => {
        return account && account.battletag === data.battletag && account.blizz_id === data.blizz_id;
      });

      if (this.type == "main") {
        Cookies.set('main_search_account', JSON.stringify(data), { sameSite: 'Strict', path: '/', expires: 90 });
      } else if (this.type == "alt") {
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

      window.location.href = '/Player/' + battletag.split('#')[0] + "/" + blizz_id + "/" + region;
    }
  }
}
</script>