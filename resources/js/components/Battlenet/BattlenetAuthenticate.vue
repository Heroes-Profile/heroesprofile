<template>
  <div class="battlenet-login">
    <h1>Welcome to the App</h1>
    <p>If you want to access your profile, you need to log in through Battle.net.</p>
    <custom-button :href="'/redirect/authenticate/battlenet'" :text="'Login with Battle.net'" :alt="'Login with Battle.net'"  :size="'medium'" :color="'blue'"></custom-button>


    Put some info on this page about Blizzard OAuth login and how its safer than regular user account creation as no password information is stored/provided to Heroes Profile etc etc
  </div>
</template>

<script>
export default {
  name: 'BattlenetAuthenticate',
  components: {
  },
  props: {
  },
  data(){
    return {
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
  }
}
</script>
