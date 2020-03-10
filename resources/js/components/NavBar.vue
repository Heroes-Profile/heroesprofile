<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="/">
      Heroes
      <img
        src="/images/logo/heroesprofilelogo.png"
        width="34"
        height="30"
        class="d-inline-block align-top"
        alt
      />
      Profile
    </a>
    <!-- <button
      class="navbar-toggler"
      type="button"
      data-toggle="collapse"
      data-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent"
      aria-expanded="true"
      aria-label="Toggle navigation"
    >
      <span class="navbar-toggler-icon"></span>
    </button>-->

    <theme-switcher></theme-switcher>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ml-auto">
          <router-link
            v-for="tab in tabs"
            :key="tab.route"
            :to="tab.route"
            tag="li"
            class="m2-3 px-2"
            exact
            active-class="btn-primary"
          >{{tab.title}}</router-link>
      </ul>
      </div>
  </nav>
</template>

<style lang="sass" scoped>
</style>

<script>
import ThemeSwitcher from "@/components/ThemeSwitcher.vue";

export default {
  components: {
    ThemeSwitcher
  },
  data: function() {
    return {
      tabs: [
        {
          title: "Global Win Rates",
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
    };
  }
};
</script>