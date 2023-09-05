<template>
  <div class="card relative" style="width: 18rem;">
    <img class="card-img-top relative hover:opacity-75 w-24 h-35" :src="getRoleImage()" :alt="role" @mouseover="showTooltip = true" @mouseleave="showTooltip = false">
    <div v-if="showTooltip" class="absolute top-0 left-0 bg-gray-700 text-white text-xs p-1 opacity-90" :style="{ left: '50%', transform: 'translateX(-50%)' }">
      {{ popuptext }}
    </div>
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
      return `/images/roles/${formattedRole}_compare.png`;
    }
  }
}
</script>