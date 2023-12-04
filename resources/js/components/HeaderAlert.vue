<template>
  {{ headerAlert }}
</template>

<script>
export default {
  name: 'HeaderAlert',
  components: {
  },
  props: {
  },
  data(){
    return {
      headerAlert: "",
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
        const response = await this.$axios.post("/api/v1/main/header/data", {
        });
        this.headerAlert = response.data.text;
      }catch(error){
        //Do something here
      }finally {
      }
    },
  }
}
</script>