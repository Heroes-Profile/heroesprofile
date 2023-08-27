<template>
  <div class="battlenet-login">
    <h1>Welcome to the App</h1>
    <p>If you want to access your profile, you need to log in through Battle.net.</p>
    <button @click="loginWithBattlenet" class="battlenet-button">
      Login with Battle.net
    </button>
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
    loginWithBattlenet() {
      window.location.href = '/redirect/authenticate/battlenet';
    },
  }
}
</script>

<style scoped>
.battlenet-login {
  text-align: center;
  margin: 20px;
}

.battlenet-button {
  background-color: #000;
  color: #fff;
  padding: 10px 20px;
  font-size: 16px;
  border: none;
  cursor: pointer;
}

.battlenet-button:hover {
  background-color: #333;
}
</style>