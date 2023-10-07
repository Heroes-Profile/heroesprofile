<template>
  <div>
    <h2>{{ title }}</h2>
    <p v-if="paragraphOne">{{ paragraphOne }}</p>
    <p v-if="paragraphTwo">{{ paragraphTwo }}</p>
    <p v-if="paragraphThree">{{ paragraphThree }}</p>
    <p v-if="paragraphFour">{{ paragraphFour }}</p>
    <p v-if="paragraphFive">{{ paragraphFive }}</p>
    <p v-if="paragraphSix">{{ paragraphSix }}</p>
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
    paragraphTwo: String,
    paragraphThree: String,
    paragraphFour: String,
    paragraphFive: String,
    paragraphSix: String,
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