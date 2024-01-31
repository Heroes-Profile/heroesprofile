<template>
  <div v-if="!patreonUser">
    <div v-if="!adBlocker" id="takeover-ad-container" class="my-20"></div>
  </div>
</template>

<script>
import Cookies from 'js-cookie';

export default {
  name: 'TakeoverAd',
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
                placement.setAttribute("data-id", "60f593ac46e4640fd9497d39");
                placement.setAttribute("data-display-type", "hybrid-banner");
            } else {
                //load mobile placement
                placement.setAttribute("data-id", "60f593cedd63d722e7e57bc8");
            }
            document.querySelector("#takeover-ad-container").appendChild(placement);
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