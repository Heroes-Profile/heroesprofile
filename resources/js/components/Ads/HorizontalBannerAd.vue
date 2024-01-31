<template >
  <div v-if="!patreonUser">
    <div v-if="adBlocker" class="bg-teal px-20 py-4 text-sm text-center">
      Heroes Profile uses ads to help fund site running costs.  Please consider allowing ads or support us through patreon at <a class="link" href="https://www.patreon.com/heroesprofile">https://www.patreon.com/heroesprofile</a>
    </div>
    <div v-else class="mb-2" id="horizontal-banner-ad-container"></div>
  </div>
</template>

<script>
import Cookies from 'js-cookie';

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
      if(!adBlocker){
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
      }

    }
  },
  computed: {
    adBlocker() {
      return Cookies.get('ad-blocker') == "true";
    },
  },
  watch: {
  },
  methods: {
  }
}
</script>