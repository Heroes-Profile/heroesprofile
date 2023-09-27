<template>
  <div>
    <h2>{{ title }}</h2>
    <p>{{ paragraphOne }}</p>
    <p>{{ paragraphTwo }}</p>
  </div>
</template>

<script>
export default {
  name: 'ImageHoverBox',
  components: {
  },
  props: {
    title: String,
    paragraphOne: String,
    paragraphTwo: String
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
  }
}
</script>