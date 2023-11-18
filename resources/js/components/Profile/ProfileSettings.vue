<template>
  <div class=" mx-auto max-w-[700px]  bg-lighten rounded-lg mt-[10vh]">
    <!-- Other content -->

    <h1 class="mb-4 bg-teal p-4 rounded-t-lg">Site Settings</h1>
    <div class="flex items-center flex-wrap justify-start p-4">

      <!-- Removing for now as I am not sure if this functionality is all that useful
     <div>
        Profile Hero/Favorite Hero: <single-select-filter :values="this.filters.heroes" :text="'Heroes'" @input-changed="handleInputChange" :defaultValue="defaultHero"></single-select-filter>
      </div>
    -->
      <div class="flex flex-wrap justify-center">
        <div class="border-r-[1px] px-4  border-white">
          <h3>Default Game Type:</h3> <multi-select-filter :values="this.filters.game_types_full" :text="'Game Type'" @input-changed="handleInputChange" :defaultValue="defaultGameType"></multi-select-filter>
        </div>


        <div class="px-4">
          <h3>Show Advanced Filtering options:</h3> <single-select-filter :values="advancedfilteringoptions" :text="'Advanced Filtering'" @input-changed="handleInputChange" :defaultValue="defaultAdvancedFiltering"></single-select-filter>
        </div>
      </div>
      


     


    </div>
 





    <h1 class="mb-4 bg-teal p-4 ">Profile Settings</h1>

    <div class="flex items-stretch gap-10 p-4">
      <div class="max-w-[50%] border-r-[1px] px-4 border-white">
        <h3 class="mb-auto">Swap account between private and not private </h3>
        <single-select-filter 
          :values="privateOptions" 
          :text="'Account Visibility'" 
          @dropdown-closed="setAccountVisbility()" 
          @input-changed="handleInputChange" 
          :defaultValue="accountVisibility"
          :trackclosure="true"
          >
        </single-select-filter>
      </div>
      <div>
      <h3 class="mb-auto">Link Patreon: <span class="bg-teal px-2"  v-if="!this.user.patreon_account">Connected</span></h3>
      <custom-button class="ml-auto mt-4" v-if="this.user.patreon_account" :href="'/authenticate/patreon'" :text="'Login with Patreon'" :alt="'Login with Patreon'"  :size="'medium'" :color="'blue'"></custom-button>

      <custom-button class="ml-auto text-sm mt-4"  v-if="!this.user.patreon_account" :ignoreclick="true" :text="'Remove Patreon'" :alt="'Remove Patreon'"  :size="'medium'" :color="'red'" @click="removePatreon()"></custom-button>
    </div>
    </div>







  </div>
</template>

<script>
export default {
  name: 'Profile',
  components: {
  },
  props: {
    user: Object,
    filters: Object,
  },
  data(){
    return {
      userhero: null,
      usergametype: null,
      advancedfilteringoptions: [
        { code: 'true', name: 'Show' },
        { code: 'false', name: "Don't Show" }
      ],
      privateOptions: [
        { code: 'true', name: 'Private' },
        { code: 'false', name: "Not Private" }
      ],
      advancedfiltering: null,
      accountVisibility: 'false',
    }
  },
  created(){
    if(this.user.private == 1){
      this.accountVisibility = "true";
    }else{
      this.accountVisibility = "false";
    }
  },
  mounted() {
    this.advancedfiltering = this.defaultAdvancedFiltering;
  },
  computed: {
    defaultHero(){
      if (this.user.user_settings.length > 0){
        let hero = this.user.user_settings.find(item => item.setting === 'hero').value;
        return hero ? hero : null;
      }
      return null;
    },
    defaultGameType(){
      if (this.user.user_settings.length > 0){
        let gameType = this.user.user_settings.find(item => item.setting === 'game_type').value;
        return gameType ? [gameType] : null;
      }
      return null;
    },
    defaultAdvancedFiltering(){
      if (this.user.user_settings.length > 0){
        let advancedfiltering = this.user.user_settings.find(item => item.setting === 'advancedfiltering').value;
        return advancedfiltering ? advancedfiltering : 'false';
      }
      return "false";
    },
  },
  watch: {
  },
  methods: {
    async saveSettings(){
      try{
        const response = await this.$axios.post("/api/v1/profile/save/settings", {
          userid: this.user.battlenet_accounts_id,
          userhero: this.userhero,
          usergametype: this.usergametype,
          advancedfiltering: this.advancedfiltering,
        });
        //window.location.href = "/Profile/Settings";
      }catch(error){
        //Do something here
      }
    },
    async setAccountVisbility(){
      try{
        const response = await this.$axios.post("/api/v1/profile/set/account/visibility", {
          userid: this.user.battlenet_accounts_id,
          accountVisibility: this.accountVisibility,
        });
      }catch(error){
        //Do something here
      }
    },
    handleInputChange(eventPayload) {
      if(eventPayload.type === 'single') {
        if(eventPayload.field == "Heroes"){
          this.userhero = this.filters.heroes.find(hero => hero.code === eventPayload.value).name;
        }else if(eventPayload.field == "Advanced Filtering"){
          this.advancedfiltering = eventPayload.value;
        }else if(eventPayload.field == "Account Visibility"){
          this.accountVisibility = eventPayload.value;
        }

      } else if(eventPayload.type === 'multi') {
        if(eventPayload.field == "Game Type"){
          this.usergametype = eventPayload.value;
        }
      }
    },
    async removePatreon(){
      try{
        const response = await this.$axios.post("/api/v1/profile/remove/patreon", {
          userid: this.user.battlenet_accounts_id,
        });
        window.location.href = "/Profile/Settings";
      }catch(error){
        //Do something here
      }
    },
  }
}
</script>
