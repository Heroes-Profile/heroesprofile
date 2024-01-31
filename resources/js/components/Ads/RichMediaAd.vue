<template v-if="!patreonUser">
  <div v-if="!adBlocker" id="rich-media-placement"></div>
</template>

<script>  
import Cookies from 'js-cookie';

export default {
  name: 'RichMediaAd',
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
            placement.setAttribute("data-id", "60f59381dd63d722e7e57bc4");
            document.querySelector("#rich-media-placement").appendChild(placement);
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