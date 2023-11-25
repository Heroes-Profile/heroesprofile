<template v-if="!patreonUser">
  <div v-if="mobile" class="vm-placement" data-id="60f593ac46e4640fd9497d39"></div>
  <div v-else class="vm-placement" data-id="60f593cedd63d722e7e57bc8"></div>
</template>

<script>
//  HeroesProfile - 728x90 Dynamic (60f593ac46e4640fd9497d39) - 728x90, 970x250, 970x90 - Place in <BODY> of page where ad should appear
//  HeroesProfile - 300x50 Dynamic Mobile (60f593cedd63d722e7e57bc8) - 300x50, 320x50, 300x100, 320x100 - Place in <BODY> of page where ad should appear

export default {
  name: 'DynamicBannerAd',
  components: {
  },
  props: {
    patreonUser: Boolean
  },
  data(){
    return {
      //Need to dynamically determine this at some point
      mobile: false,
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