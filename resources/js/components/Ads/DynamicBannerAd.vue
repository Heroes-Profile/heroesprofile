<template v-if="!patreonUser" >
  <div v-if="!adBlocker" class="my-20" :id="`dynamic-banner-ad-container-${index}`"></div>
</template>

<script>
import Cookies from 'js-cookie';

export default {
  name: 'DynamicBannerAd',
  components: {
  },
  props: {
    patreonUser: Boolean,
    index: Number,
    mobileOverride: Boolean,
  },
  data(){
    return {
      adBlockerCookie: false,
    }
  },
  created(){
  },
  mounted() {
    let htmlComponent = `#dynamic-banner-ad-container-${this.index}`;
    let mobileoverride = this.mobileOverride;
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
            if (window.innerWidth > 1000 || mobileoverride) {
                //load desktop placement
                placement.setAttribute("data-id", "60f593ac46e4640fd9497d39");
            } else {
                //load mobile placement
                placement.setAttribute("data-id", "60f593cedd63d722e7e57bc8");
            }
            document.querySelector(htmlComponent).appendChild(placement);
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