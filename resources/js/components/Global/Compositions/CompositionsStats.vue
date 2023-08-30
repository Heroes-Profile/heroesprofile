<template>
  <div>
  	Work on later Composition Stats
  </div>
</template>

<script>
export default {
  name: 'CompositionsStats',
  components: {
  },
  props: {
  },
  data(){
    return {
    }
  },
  created(){
  	this.getData();
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
    async getData(){
      try{
        const response = await this.$axios.post("/api/v1/global/compositions", {
        });
        console.log(response.data);
      }catch(error){
        console.log(error);
      }
    },
  }
}
</script>