<template>
  <div class="card relative text-center flex-1">
    <img class="card-img-top relative  w-16  mx-auto" loading="eager" :src="getRoleImage()" :alt="role" @mouseover="showTooltip = true" @mouseleave="showTooltip = false">
    
    {{ role }}
  </div>
</template>

<script>
export default {
  name: 'RoleBox',
  components: {
  },
  props: {
    role: String,
    hovertext: String,
  },
  data(){
    return {
      showTooltip: false,
      popuptext: "",
    }
  },
  created(){
    this.popuptext = this.hovertext ? this.hovertext : this.role;
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
    getRoleImage(){
      let formattedRole = this.role.toLowerCase();

      //This is the wrong image I think?  The other one is without _compare
      return `/images/roles/${formattedRole}.png`;
    }
  }
}
</script>
<style scoped>
  img{filter: brightness(0);}

  </style>