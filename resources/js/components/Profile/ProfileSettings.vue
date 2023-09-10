<template>
  <div>
    <!-- Other content -->

         <custom-button  v-if="!this.user.patreon_account" :href="'/authenticate/patreon'" :text="'Login with Patreon'" :alt="'Login with Patreon'"  :size="'medium'" :color="'blue'"></custom-button>


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
  },
  computed: {
  },
  watch: {
  },
  methods: {
  }
}
</script>
