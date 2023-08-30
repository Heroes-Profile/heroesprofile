<template>
  <div>
    <!-- Other content -->
    <button @click="goToPatreon">Login with Patreon</button>
  </div>
</template>

<script>
export default {
  name: 'Profile',
  components: {
  },
  props: {
    user: Object
  },
  data(){
    return {
    }
  },
  created(){
  },
  mounted() {
    console.log(this.user);
  },
  computed: {
  },
  watch: {
  },
  methods: {
    goToPatreon() {
      // Redirect to the Patreon login route on your Laravel backend
      window.location.href = "/authenticate/patreon";
    }
  }
}
</script>
