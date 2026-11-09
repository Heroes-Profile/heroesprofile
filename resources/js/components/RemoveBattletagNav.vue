<template>
  <!-- The whole row is the button, not just the word -->
  <a href="#" role="button" @click.prevent="remove()">Remove</a>
</template>

<script>
export default {
  name: 'RemoveBattletagNav',
  components: {
  },
  props: {
    index: Number,
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
    remove(){
      const cookieName = `alt_search_account${this.index + 1}`;
      document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; SameSite=Strict`;
      // No reload: on that player's own page the reload would put them straight back in the header
      document.getElementById(`alt-account-${this.index}`)?.remove();
    }
  }
}
</script>