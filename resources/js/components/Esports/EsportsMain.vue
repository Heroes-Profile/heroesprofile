<template>
  <div>
    <page-heading :infoText1="infoText1" :heading="'Community Esports'"></page-heading>
    <div class="flex   p-20 bg-lighten flex-wrap justify-center">
      
      <div class="text-center md:w-[15%] mb-15 mx-5">
        <a :href="'/Esports/NGS'"><img src="/images/NGS/600-600-ngs_large.png" alt="NGS"/></a>
        <custom-button :href="'/Esports/NGS'" :text="'NGS'" :alt="'NGS'" :size="'big'" class="mt-10"></custom-button>
      </div>

      <div class="text-center md:w-[15%] mb-15 mx-5">
        <a :href="'/Esports/CCL'"><img src="/images/CCL/600-600-HHE_CCL_Logo_rectangle.png" alt="CCL"/></a>
        <custom-button :href="'/Esports/CCL'" :text="'CCL'" :alt="'CCL'" :size="'big'" class="mt-10"></custom-button>
      </div>

      <div class="text-center md:w-[15%] mb-15 mx-5">
        <a :href="'/Esports/NutCup'"><img src="/images/NutCup/600-600-nut-cup.png" alt="Nut Cup"/></a>
        <custom-button :href="'/Esports/NutCup'" :text="'Nut Cup'" :alt="'Nut Cup'" :size="'big'" class="mt-10"></custom-button>
      </div>

      <div class="text-center md:w-[15%] mb-15 mx-5">
        <a :href="'/Esports/MastersClash'"><img src="/images/MCL/600-600-MCC_logo_2021.png" alt="Masters Clash"/></a>
        <custom-button :href="'/Esports/MastersClash'" :text="'Masters Clash'" :alt="'Masters Clash'" :size="'big'" class="mt-10"></custom-button>
      </div>

      <div class="text-center md:w-[15%] mb-15 mx-5">
        <a :href="'/Esports/HeroesInternational'"><img src="/images/HI/heroes_international.png" alt="Heroes International"/></a>
        <custom-button :href="'/Esports/HeroesInternational'" :text="'Heroes International'" :alt="'Heroes International'" :size="'big'" class="mt-10"></custom-button>
      </div>

    </div>
  </div>
</template>

<script>
export default {
  name: 'EsportsMain',
  components: {
  },
  props: {
  },
  data(){
    return {
      infoText1: "Click a League below to see Overall Stats, Divisions, Teams, Matches, and Unofficial Rankings",
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