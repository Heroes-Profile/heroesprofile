<template>
  <span @click="remove()">Remove</span>
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
      location.reload();
    }
  }
}
</script>