<template>
  <div class="battlenet-login mx-auto max-w-[700px]  bg-lighten rounded-lg mt-[10vh]">
    <h1 class="mb-4 bg-teal p-4 rounded-t-lg">Heroes Profile Login</h1>
    <div class="p-4">
    <p>If you want to access your profile, you need to log in through Battle.net.</p>
    
    <div class="flex items-end my-20 justify-center">
      <single-select-filter :values="filters.regions" :text="'Region'" v-model="selectedRegion" @input-changed="handleInputChange"></single-select-filter>

      <custom-button class="m-2" :href="'/redirect/authenticate/battlenet'" :text="'Login with Battle.net'" :alt="'Login with Battle.net'" :size="'medium'" :color="'blue'" :disabled="!selectedRegion"></custom-button>
    </div>

   <p class="text-sm"> Heroes Profile uses Blizzard's Battle.net OAuth for authentication.  Battle.net OAuth is an authentication and authorization framework used by Blizzard Entertainment for its online gaming platform, Battle.net. It allows users to securely log in to Battle.net and grant third-party applications limited access to their Battle.net accounts without sharing their account credentials. </p>
  </div>
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
          this.selectedRegion = null;
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
