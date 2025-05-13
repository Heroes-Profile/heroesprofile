<template>
  <div v-if="!isLoading" class=" mx-auto max-w-[700px]  bg-lighten rounded-lg mt-[10vh]">
    <h1 v-if="settingsSaved" class="text-center text">Settings Saved</h1>
    <h1 class="mb-4 bg-teal p-4 rounded-t-lg">Global Page Settings</h1>
    <div class="flex items-center flex-wrap justify-start p-4">
      <div class="flex  justify-center">

        <div class="flex-col  border-white border-r-[1px] px-4">
          <div class=" ">
            <h3>Default Multi-Select Game Type:</h3> 
            <multi-select-filter
              :values="this.filters.game_types_full" 
              :text="'Game Type'" 
              @dropdown-closed="saveSettings()" 
              @input-changed="handleInputChange" 
              :defaultValue="usermultigametype"
              :trackclosure="true"
            >
            </multi-select-filter>
          </div>
          <div class="">
            <h3>Default Build Type</h3> 
            <!-- Talent build Type -->
            <single-select-filter 
              :values="filters.talent_build_types" 
              :text="'Talent Build Type'" 
              @dropdown-closed="saveSettings()" 
              @input-changed="handleInputChange" 
              :defaultValue="talentBuildType"
              :trackclosure="true"
            >
            </single-select-filter>
          </div>
        </div>

        <div class="px-4">
          <div class="">
            <h3>Show Advanced Filtering options:</h3> 
            <single-select-filter 
              :values="advancedfilteringoptions" 
              :text="'Advanced Filtering'" 
              @dropdown-closed="saveSettings()" 
              @input-changed="handleInputChange" 
              :defaultValue="advancedfiltering"
              :trackclosure="true"

            >
            </single-select-filter>
          </div>
          <div class="">
            <h3>Default Game Type:</h3> 
            <single-select-filter
              :values="this.filters.game_types_full" 
              :text="'Game Type'" 
              @dropdown-closed="saveSettings()" 
              @input-changed="handleInputChange" 
              :defaultValue="usergametype"
              :trackclosure="true"
            >
            </single-select-filter>
          </div>
        </div>
      </div>

        <div class="px-4 mt-4">
          <h3>Table Style:</h3>
          <tab-button tab1text="Light" :ignoreclick="true" tab2text="Dark" @tab-click="darkmodesetting" :overridedefaultside="darkmode"> </tab-button>
        </div>



    

  

    </div>
    <h1 class="mb-4 bg-teal p-4">Player Stat Settings</h1>

    <div class="flex items-stretch gap-10 p-4">
      <div class="flex-col  border-white border-r-[1px] px-4">
              
        <div class="">
          <h3>Player Match History Style</h3> 
          <tab-button tab1text="Table" :ignoreclick="true" tab2text="Compact" @tab-click="playermatchhistorystylesetting" :overridedefaultside="playerhistorytable"> </tab-button>
        </div>
      </div>

      <div class="flex-col  border-white px-4">
              
        <div class="">
            <h3>Default HP MMR Breakdown Game Type:</h3> 
            <single-select-filter
              :values="this.filters.game_types_full" 
              :text="'HP MMR Game Type'" 
              @dropdown-closed="saveSettings()" 
              @input-changed="handleInputChange" 
              :defaultValue="mmrplayerusergametype"
              :trackclosure="true"
            >
            </single-select-filter>
          </div>
      </div>


    </div>
     

        <div class="max-w-[50%] border-r-[1px] px-4 border-white">
          <h3 class="mb-auto">Player data initial load style</h3>
          <single-select-filter 
            :values="playerdataloadstyles" 
            :text="'Player Load'" 
            @dropdown-closed="saveSettings()" 
            @input-changed="handleInputChange" 
            :defaultValue="playerload"
            :trackclosure="true"
            >
          </single-select-filter>
        </div>


        <div class="max-w-[50%] border-r-[1px] px-4 border-white">
          <h3 class="mb-auto">Show custom games.  Match History page only</h3>
          <single-select-filter 
            :values="showcustomgames" 
            :text="'Show Custom Games'" 
            @dropdown-closed="saveSettings()" 
            @input-changed="handleInputChange" 
            :defaultValue="customgames"
            :trackclosure="true"
            >
          </single-select-filter>
        </div>






    <h1 class="mb-4 bg-teal p-4">Profile Settings</h1>

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
        <h3 class="mb-auto">Link Patreon: <span class="bg-teal px-2"  v-if="this.user.patreon_account">Connected</span></h3>
        <custom-button class="ml-auto mt-4" v-if="!this.user.patreon_account" :href="'/authenticate/patreon'" :text="'Login with Patreon'" :alt="'Login with Patreon'"  :size="'medium'" :color="'blue'"></custom-button>

        <custom-button class="ml-auto text-sm mt-4"  v-if="this.user.patreon_account" :ignoreclick="true" :text="'Remove Patreon'" :alt="'Remove Patreon'"  :size="'medium'" :color="'red'" @click="removePatreon()"></custom-button>
      </div>


    </div>

    
  </div>
  <div v-else>
    <loading-component></loading-component>
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
      isLoading: false,
      userhero: null,
      usergametype: null,
      mmrplayerusergametype: null,
      usermultigametype: null,
      advancedfilteringoptions: [
        { code: 'true', name: 'Show' },
        { code: 'false', name: "Don't Show" }
      ],
      privateOptions: [
        { code: 'true', name: 'Private' },
        { code: 'false', name: "Not Private" }
      ],
      playerdataloadstyles: [
        { code: 'true', name: 'Standard' },
        { code: 'false', name: "No load" }
      ],
      showcustomgames: [
        { code: false, name: "Don't Show" },
        { code: true, name: 'Show' }

      ],
      advancedfiltering: null,
      accountVisibility: 'false',
      talentBuildType: null,
      settingsSaved: false,
      darkmode: false,
      playerhistorytable: true,
      overridedefaultside: null,


      defaultMultiGameTypeOverride: null,
      playerload: 'true',
      customgames: false,
      savemultigametype: null,
    }
  },
  created(){
    if(this.user.private == 1){
      this.accountVisibility = "true";
    }else{
      this.accountVisibility = "false";
    }

    this.usergametype = this.defaultGameType;
    this.mmrplayerusergametype = this.defaultMMRPlayerGameType;
    this.usermultigametype = this.defaultMultiGameType;
    this.savemultigametype = this.usermultigametype;
    this.advancedfiltering = this.defaultAdvancedFiltering;
    this.talentBuildType = this.defaultBuildType;
    this.darkmode = this.defaultDarkMode;
    this.playerhistorytable = this.defaultPlayerhistorytable;
    this.customgames = this.defaultCustomGames;
  },
  mounted() {
  },
  computed: {
    defaultHero(){
      if (this.user.user_settings.length > 0){
        let hero = this.user.user_settings.find(item => item.setting === 'hero');
        return hero ? hero.value : null;
      }
      return null;
    },
    defaultMultiGameType() {
      if (this.user.user_settings.length > 0) {
        let multiGameTypes = this.user.user_settings.find(item => item.setting === 'multi_game_type');
        let gameTypes = multiGameTypes ? multiGameTypes.value : null;

        if (gameTypes && gameTypes.trim() !== '') {
          return gameTypes.split(',').map(value => value.trim());
        }
      }
      return ["sl"];
    },
    defaultGameType() {
      if (this.user.user_settings.length > 0) {
        let gameTypeSetting = this.user.user_settings.find(item => item.setting === 'game_type');
        return gameTypeSetting ? gameTypeSetting.value : null;
      }
      return "sl";
    },
    defaultMMRPlayerGameType() {
      if (this.user.user_settings.length > 0) {
        let gameTypeSetting = this.user.user_settings.find(item => item.setting === 'mmr_player_game_type');
        return gameTypeSetting ? gameTypeSetting.value : "sl";
      }
      return "sl";
    },
    defaultBuildType(){
      if (this.user.user_settings.length > 0){
        let buildtype = this.user.user_settings.find(item => item.setting === 'talentbuildtype');
        return buildtype ? buildtype.value : null;
      }
      return this.filters.talent_build_types[0].code;
    },
    defaultAdvancedFiltering(){
      if (this.user.user_settings.length > 0){
        let advancedfiltering = this.user.user_settings.find(item => item.setting === 'advancedfiltering');
        return advancedfiltering ? advancedfiltering.value : 'false';
      }
      return "false";
    },
    defaultDarkMode(){
      if (this.user.user_settings.length > 0){
        let darkmode = this.user.user_settings.find(item => item.setting === 'darkmode');
        if(darkmode && darkmode.value == 1){
          return "right";
        }else{
          return "left";
        }
      }
      return "left";
    },
    defaultPlayerhistorytable(){
      if (this.user.user_settings.length > 0){
        let playerhistorytable = this.user.user_settings.find(item => item.setting === 'playerhistorytable');
        if(playerhistorytable && playerhistorytable.value == 1){
          return "right";
        }else{
          return "left";
        }
      }
      return "left";
    },
    defaultCustomGames(){
      if (this.user.user_settings.length > 0){
        let customgames = this.user.user_settings.find(item => item.setting === 'customgames');
        return customgames ? customgames.value == 1 ? true : false : false;
      }
      return false;
    },
  },
  watch: {
  },
  methods: {
    async saveSettings(){
      this.isLoading = true;

      let darkmodeinput = this.darkmode;
      if(darkmodeinput == "right"){
        darkmodeinput = true;
      }else if(darkmodeinput == "left"){
        darkmodeinput = false;
      }

      let playerhistorytableinput = this.playerhistorytable;
      if(playerhistorytableinput == "right"){
        playerhistorytableinput = true;
      }else if(playerhistorytableinput == "left"){
        playerhistorytableinput = false;
      }
      try{
        const response = await this.$axios.post("/api/v1/profile/save/settings", {
          userid: this.user.battlenet_accounts_id,
          userhero: this.userhero,
          usergametype: this.usergametype,
          mmrplayerusergametype: this.mmrplayerusergametype,
          usermultigametype: this.savemultigametype,
          advancedfiltering: this.advancedfiltering,
          talentbuildtype: this.talentBuildType,
          darkmode: darkmodeinput,
          playerload: this.playerload,
          customgames: this.customgames,
          playerhistorytable: playerhistorytableinput,
        });
        //window.location.href = "/Profile/Settings";
        this.settingsSaved = true;
        setTimeout(() => {
          this.settingsSaved = false;
        }, 5000);
        this.usermultigametype = this.savemultigametype;
        
      }catch(error){
        //Do something here
      }
      this.isLoading = false;
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
        }else if(eventPayload.field == "Talent Build Type"){
          this.talentBuildType = eventPayload.value;
        }else if(eventPayload.field == "Game Type"){
          this.usergametype = eventPayload.value;
        }else if(eventPayload.field == "MMR Game Type"){
          this.mmrplayerusergametype = eventPayload.value;
        }else if(eventPayload.field == "Player Load"){
          this.playerload = eventPayload.value;
        }else if(eventPayload.field == "Show Custom Games"){
          this.customgames = eventPayload.value;
        }

      } else if(eventPayload.type === 'multi') {
        if(eventPayload.field == "Game Type"){
          this.savemultigametype = eventPayload.value;
        }
      }
   
    },
    darkmodesetting(side){
      if(side == "right"){
        this.darkmode = true;
      }else{
        this.darkmode = false;
      }
      this.saveSettings();
    },
    playermatchhistorystylesetting(side){
      if(side == "right"){
        this.playerhistorytable = true;
      }else{
        this.playerhistorytable = false;
      }
      this.saveSettings();
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
