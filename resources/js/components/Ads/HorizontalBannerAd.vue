<template>
<!-- HeroesProfile - 728x90 Dynamic (60f593ac46e4640fd9497d39) - 728x90, 970x250, 970x90 - Place in <BODY> of page where ad should appear -->
  <div v-if="!patreonUser" class="vm-placement" data-id="60f593ac46e4640fd9497d39"></div>
<!-- / HeroesProfile - 728x90 Dynamic (60f593ac46e4640fd9497d39) -->
</template>

<script>
export default {
  name: 'HorizontalBannerAd',
  components: {
  },
  props: {
    patreonUser: Boolean
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