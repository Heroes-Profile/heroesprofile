<template>
  <div>
  </div>
</template>

<script>
export default {
  name: 'Prematch',
  components: {
  },
  props: {
    prematchid: Number,
  },
  data(){
    return {
      isLoading: false,
      windowWidth: window.innerWidth,
      data: null,

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
      this.isLoading = true;
      try{
        const response = await this.$axios.post("/api/v1/prematch", {
          prematchid: this.prematchid,
        });
        this.data = response.data; 
      }catch(error){
      //Do something here
      }finally {
        this.isLoading = false;
        this.$nextTick(() => {
          if(this.windowWidth < 1500){
            this.resizeTables();
          }
        });
      }
    },
  }
}
</script>