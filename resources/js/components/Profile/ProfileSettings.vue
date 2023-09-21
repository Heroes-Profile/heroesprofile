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
    }
  },
  created(){
  },
  mounted() {
  },
  computed: {
    defaultHero(){
      let hero = this.user.user_settings.find(item => item.setting === 'hero').value;

      return hero ? hero : null;
    },
    defaultGameType(){
      let gameType = this.user.user_settings.find(item => item.setting === 'game_type').value;
      return gameType ? [gameType] : null;
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
        });
        //window.location.href = "/Profile/Settings";
      }catch(error){
        console.log(error)
      }
    },
    handleInputChange(eventPayload) {
      if(eventPayload.type === 'single') {
        if(eventPayload.field == "Heroes"){
          this.userhero = this.filters.heroes.find(hero => hero.code === eventPayload.value).name;
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
