<template>
  <div class="hp-tabs">
    <router-link 
      v-for="tab in tabs" 
      :key="tab.route" 
      :to="tab.route" 
      tag="button"
      exact
      active-class="btn-primary">
      {{tab.title}}
    </router-link>

    </div>
</template>

<style lang="sass" scoped>



</style>

<script>
  export default {
    data: function () {
      return {
        tabs: [
          {
            title: 'Global Leaderboard',
            route: "/"
          },
          {
            title: "Profile",
            route: "/profile"
          },
           {
            title: "OptOut",
            route: "/optout"
          }
        ]
      }
  }
}
</script>