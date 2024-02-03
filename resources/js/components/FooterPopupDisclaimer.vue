<template>
  <div v-if="popUpVar">
    Heroes Profile is funded by its developers. If you like the site, consider donating to our Patreon. even $2 helps. The data on this website relies on replays uploaded to Heroes Profile API. Make sure you go there to upload your data. If you do not upload your replays, your data will not be accurate.
This site uses cookies in order to store your recent profile views. To learn more, view our <a class="link" href="/Privacy/Policy">Privacy Policy</a>

    <custom-button @click="closePopup" text="Accept and close" alt="Accept and close" size="small" :ignoreclick="true">Accept and close</custom-button>

  </div>
</template>

<script>
export default {
  name: 'ExampleComponent',
  components: {
  },
  props: {
  },
  data(){
    return {
      popUpVar: true,
    }
  },
  created(){
    if (typeof localStorage !== 'undefined' && localStorage !== null) {
      if (localStorage.getItem('popupDisclaimer')) {
        this.popUpVar = false;
      }
    } else {
      this.popUpVar = false;
    }
      
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
    closePopup(){
      localStorage.setItem('popupDisclaimer', 'true');
      this.popUpVar = false;
    }
  }
}
</script>