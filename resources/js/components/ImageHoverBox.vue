<template>
  <div class="imageHoverBox flex flex-col gap-2 my-2 text-bold">
    <h2>{{ title }}</h2>
    <p v-html="paragraphOne" v-if="paragraphOne"></p>
    <p v-if="hpOwner">{{ hpOwner }}</p>
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
    hpOwner: String,
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