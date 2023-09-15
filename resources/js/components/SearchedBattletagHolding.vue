<template>
  <div>
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
        <div><hero-box-small :hero="item.latestHero"></hero-box-small></div>
      </div>
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
      battletagresponse: [],
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
          console.log('Response is missing some data');
        }


      }catch(error){
        console.log(error);
        //this.error = error;
        this.battletagresponse = "Invalid input: '%', '?' and ' ' are invalid inputs";
      }
    },
    redirectToProfile(battletag, blizz_id, region){
      let data = {
        battletag: battletag.split('#')[0],
        blizz_id: blizz_id,
        region: region,
      };

      const existingAccounts = [
        JSON.parse(Cookies.get('alt_search_account1') || 'null'),
        JSON.parse(Cookies.get('alt_search_account2') || 'null'),
        JSON.parse(Cookies.get('alt_search_account3') || 'null'),
      ];

      const accountExists = existingAccounts.some(account => {
        return account && account.battletag === data.battletag && account.blizz_id === data.blizz_id && account.region === data.region;
      });

      if (this.type == "main") {
        Cookies.set('main_search_account', JSON.stringify(data), { sameSite: 'Strict', path: '/', expires: 90 });
      } else if (this.type == "alt" && !accountExists) {
        
        let altNumber = 0;

        if (this.alt_search_account1_exists && this.alt_search_account2_exists && this.alt_search_account3_exists) {
          // Get all cookies and find the oldest one
          const account1Data = JSON.parse(Cookies.get('alt_search_account1'));
          const account2Data = JSON.parse(Cookies.get('alt_search_account2'));
          const account3Data = JSON.parse(Cookies.get('alt_search_account3'));
          
          const oldestAccount = [account1Data, account2Data, account3Data].reduce((oldest, current) => current.date < oldest.date ? current : oldest);

          if (oldestAccount === account1Data) {
            altNumber = 1;
          } else if (oldestAccount === account2Data) {
            altNumber = 2;
          } else {
            altNumber = 3;
          }

          data.date = new Date().toISOString();
          Cookies.set('alt_search_account' + altNumber, JSON.stringify(data), { sameSite: 'Strict', path: '/', expires: 90 });
        } else if (!this.alt_search_account1_exists) {
          data.date = new Date().toISOString();
          Cookies.set('alt_search_account1', JSON.stringify(data), { sameSite: 'Strict', path: '/', expires: 90 });
        } else if (!this.alt_search_account2_exists) {
          data.date = new Date().toISOString();
          Cookies.set('alt_search_account2', JSON.stringify(data), { sameSite: 'Strict', path: '/', expires: 90 });
        } else if (!this.alt_search_account3_exists) {
          data.date = new Date().toISOString();
          Cookies.set('alt_search_account3', JSON.stringify(data), { sameSite: 'Strict', path: '/', expires: 90 });
        }
      }

      window.location.href = '/Player/' + battletag.split('#')[0] + "/" + blizz_id + "/" + region;
    }
  }
}
</script>