<template>
  <div v-if="!patreonUser">
    <div v-if="!adBlocker" :id="`dynamic-square-ad-container-${index}`" style="min-height:250px; position:relative;"></div>
  </div>
</template>

<script>
import Cookies from 'js-cookie';

export default {
  name: 'DynamicSquareAd',
  components: {
  },
  props: {
    patreonUser: Boolean,
    index: Number,
  },
  data(){
    return {
      detectedBlocker: false,
    }
  },
  created(){
  },
  mounted() {
    let htmlComponent = `#dynamic-square-ad-container-${this.index}`;

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
                placement.setAttribute("data-id", "60f5939d46e4640fd9497d35");
            } else {
                //load mobile placement
                placement.setAttribute("data-id", "60f5939d46e4640fd9497d35");
            }
            document.querySelector(htmlComponent).appendChild(placement);
            window.top.__vm_add.push(placement);
        });

        setTimeout(() => {
          const container = document.querySelector(htmlComponent);
          if (container) {
            const placement = container.querySelector('.vm-placement');
            if (!placement || !placement.hasChildNodes()) {
              this.detectedBlocker = true;
              sessionStorage.setItem('ad-blocker-detected', 'true');
            }
          }
        }, 3500);
      }
    }
  },
  computed: {
    adBlocker() {
      return this.detectedBlocker || sessionStorage.getItem('ad-blocker-detected') === 'true' || Cookies.get('ad-blocker') == "true";
    },
  },
  watch: {
  },
  methods: {
  }
}
</script>
