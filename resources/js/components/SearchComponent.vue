<template>
  <div>
    <div class="input-group mb-3">
      <input type="text" class="form-control search-input" placeholder="Enter your battletag" aria-label="Enter your battletag" aria-describedby="basic-addon2" v-model="userinput" @keyup.enter="clickedButton">
      <div class="input-group-append">
        <button class="btn btn-outline-secondary" type="button" @click="clickedButton">Show My Stats</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SearchComponent',
  components: {
  },
  props: {
    type: String,
  },
  data(){
    return {
      userinput: "",
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
    clickedButton(){
      window.location.href = '/battletag/searched/' + this.userinput + "/" + this.type;
    }
  }
}
</script>