<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
    <page-heading :heading="'Heroes International'"></page-heading>
    <div class="flex p-10 md:p-20 bg-surface rounded max-w-[93.75rem] mx-4 md:mx-auto flex-wrap justify-center">

      <div class="text-center md:w-[15%] mb-15 mx-5">
        <a :href="'/Esports/HeroesInternational/?tournament=main'"><img src="/images/HI/heroes_international.png" alt="Heroes International"/></a>
        <custom-button :href="'/Esports/HeroesInternational/?tournament=main'" :text="'Heroes International'" :alt="'Heroes International'" :size="'big'" class="mt-10"></custom-button>
      </div>


      <div class="text-center md:w-[15%] mb-15 mx-5">
        <a :href="'/Esports/HeroesInternational/?tournament=nationscup'"><img src="/images/HI/Flags/AllFlags.png" alt="Heroes International"/></a>
        <custom-button :href="'/Esports/HeroesInternational/?tournament=nationscup'" :text="'Nations Cup'" :alt="'Nations Cup'" :size="'big'" class="mt-10"></custom-button>
      </div>

    </div>
  </div>
</template>

<script>
export default {
  name: 'HeroesInternationalEntry',
  components: {
  },
  props: {
  },
  data(){
    return {
      infoText1: "Click a League below to see Main Heroes International stats, or Nation Cup stats",
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