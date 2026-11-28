<template>
  <!-- The ad only. With an ad blocker the page shows its own notice instead (layouts/app, .ad-blocker-notice) -->
  <div v-if="!patreonUser && !adBlocker" id="horizontal-banner-ad-container" style="min-height:7.5rem; position:relative;"></div>
</template>

<script>
import { adBlocker, markAdBlocker } from '../../utils/adBlocker';

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
    if(!this.patreonUser){
      if(!this.adBlocker){
        window.top.__vm_add = window.top.__vm_add || [];
        (function (success) {
            if (window.document.readyState !== "loading") {
                success();
            } else {
                window.document.addEventListener("DOMContentLoaded", function () {
                    success();
                });
            }
        })(function () {
            var placement = document.createElement("div");
            placement.setAttribute("class", "vm-placement");
            if (window.innerWidth > 1000) {
                //load desktop placement
                placement.setAttribute("data-id", "60f593a446e4640fd9497d37");
            } else {
                //load mobile placement
                placement.setAttribute("data-id", "60f59392dd63d722e7e57bc6");
            }
            document.querySelector("#horizontal-banner-ad-container").appendChild(placement);
            window.top.__vm_add.push(placement);
        });

        setTimeout(() => {
          const container = document.querySelector('#horizontal-banner-ad-container');
          if (container) {
            const placement = container.querySelector('.vm-placement');
            if (!placement || !placement.hasChildNodes()) {
              markAdBlocker('empty');
            }
          }
        }, 3500);
      }
    }
  },
  computed: {
    adBlocker() {
      return adBlocker.value;
    },
  },
  watch: {
  },
  methods: {
  }
}
</script>
