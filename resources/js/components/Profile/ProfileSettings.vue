<template>
  <div>
    <!-- Other content -->

    User settings woot woot
    <div>
      Profile Hero/Favorite Hero: <single-select-filter :values="this.filters.heroes" :text="'Heroes'" @input-changed="handleInputChange" :defaultValue="defaultHero"></single-select-filter>
    </div>

    <div>
      Default Game Type: <multi-select-filter :values="this.filters.game_types_full" :text="'Game Type'" @input-changed="handleInputChange" :defaultValue="defaultGameType"></multi-select-filter>
    </div>

    <div>
      Show Advanced Filtering options: <single-select-filter :values="advancedfilteringoptions" :text="'Advanced Filtering'" @input-changed="handleInputChange" :defaultValue="defaultAdvancedFiltering"></single-select-filter>
    </div>


    <custom-button :ignoreclick="true" :text="'Save settings'" @click="saveSettings()"></custom-button>
    <ul>
      <li>Opt Out</li>
    </ul>
    <custom-button  v-if="!this.user.patreon_account" :href="'/authenticate/patreon'" :text="'Login with Patreon'" :alt="'Login with Patreon'"  :size="'medium'" :color="'blue'"></custom-button>


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
      advancedfiltering: null,
    }
  },
  created(){
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
        window.location.href = "/Profile/Settings";
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
        }

      } else if(eventPayload.type === 'multi') {
        if(eventPayload.field == "Game Type"){
          this.usergametype = eventPayload.value;
        }
      }
    },
  }
}
</script>
