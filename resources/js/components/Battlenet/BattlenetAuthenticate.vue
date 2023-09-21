<template>
  <div class="battlenet-login">
    <h1>Welcome to the App</h1>
    <p>If you want to access your profile, you need to log in through Battle.net.</p>
    
    <custom-button :href="'/redirect/authenticate/battlenet'" :text="'Login with Battle.net'" :alt="'Login with Battle.net'" :size="'medium'" :color="'blue'" :disabled="!selectedRegion"></custom-button>

    <single-select-filter :values="filters.regions" :text="'Region'" v-model="selectedRegion" @input-changed="handleInputChange"></single-select-filter>

    Put some info on this page about Blizzard OAuth login and how its safer than regular user account creation as no password information is stored/provided to Heroes Profile etc etc
  </div>
</template>

<script>
  import Cookies from 'js-cookie';

  export default {
    name: 'BattlenetAuthenticate',
    components: {
    },
    props: {
      filters: {
        type: Object,
        required: true
      },
    },
    data(){
      return {
        selectedRegion: null,
      }
    },
    created(){
    },
    mounted() {
    },
    computed: {
    },
    watch: {
    },
    methods: {
      handleInputChange(value) {
        if (!value || !value.value) {
          console.error('Invalid value');
          return;
        }
        this.selectedRegion = value.value;
        this.saveCookieData();
      },
      saveCookieData(){
        const regions = {
          "NA": 1,
          "EU": 2,
          "KR": 3,
          "CN": 5,
        };

        let regionInt = regions[this.selectedRegion];

        if (regionInt === undefined) {
          console.error('Invalid region selected');
          return;
        }
        Cookies.set('battlenet_region', regionInt.toString(), { sameSite: 'Lax', path: '/', expires: 90 });
      }
    }
  }
</script>
