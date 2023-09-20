<template>
  <div>
    <!-- Other content -->

    User settings woot woot
    <div>
      Profile Hero/Favorite Hero: <single-select-filter :values="this.filters.heroes" :text="'Heroes'" @input-changed="handleInputChange"></single-select-filter>
    </div>

    <div>
      Default Game Type: <multi-select-filter :values="this.filters.game_types_full" :text="'Game Type'" @input-changed="handleInputChange"></multi-select-filter>
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
    console.log(this.filters);
  },
  computed: {
  },
  watch: {
  },
  methods: {
    async saveSettings(){
      try{
        const response = await this.$axios.post("/api/v1/profile/save/settings", {
          userhero: this.userhero,
          usergametype: this.usergametype,
        });
      }catch(error){
        console.log(error)
      }
    },
    handleInputChange(eventPayload) {
      console.log(eventPayload);
      if(eventPayload.type === 'single') {
        if(eventPayload.field == "Heroes"){
          this.userhero = eventPayload.value;
        }

      } else if(eventPayload.type === 'multi') {
        //this.selectedMultiFilters[eventPayload.field] = eventPayload.value;
      }
    },
  }
}
</script>
