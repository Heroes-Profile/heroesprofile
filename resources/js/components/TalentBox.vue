<template>
  <div class="card" style="width: 18rem;">
    <img class="card-img-top" :src="getTalentImage()" :alt="talent.name">
    {{ talent.name }}
    {{ this.removeNumbers(talent.hotkey) }}
    {{ talent.description }}
  </div>
</template>

<script>
export default {
  name: 'TalentBox',
  components: {
  },
  props: {
    talent: Object
  },
  data(){
    return {
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
    getTalentImage(){
      return `/images/talents/${this.talent.icon}`;
    },
    removeNumbers(str) {
      return str.replace(/\d+/g, '');
    }
  }
}
</script>