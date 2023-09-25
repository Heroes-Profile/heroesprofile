<template>
  <div>
    <div class="text-center py-10">
      <img class="block m-4 w-2/5 max-w-6xl mr-auto ml-auto pl-25" src="/images/logo/full_deathwing.png"/>

      <div class="block text-center mx-auto align-items-center justify-center flex">
        <search-component :type="'alt'" :buttonText="'Find Player'" :labelText="'Enter a battletag'"></search-component>

      </div>
    </div>


    <div class="flex   p-20 bg-lighten flex-wrap justify-center">
      <div class="text-center md:w-[30%] mb-20 mx-5">
        <h3 class="text-2xl mb-10 ">Player comparison</h3>
        <p>See how you compare to other players or to a certain league tier. You can compare up to four players at one time.</p>
        
        <custom-button :href="'/Compare'" :text="'Compare'" :alt="'Compare players'" :size="'big'" class="mt-10"></custom-button>
      </div>


      <div class="text-center md:w-[30%] mb-20 flex flex-col mx-5">
        <h3 class="text-2xl mb-10">Global Stats</h3>
        <p>Variety of global stats for heroes including, overall stats, builds, compositions, draft, matchups, and more.</p>
        
        <div class="flex gap-2 justify-around mt-auto">
          <custom-button :href="'/Global/Hero'" :text="'Hero Stats'" :alt="'Hero Stats'" :size="'big'" class="mt-10" ></custom-button>
          <custom-button :href="'/Global/Talents'" :text="'Talent Stats'" :alt="'Hero Stats'" :size="'big'"  class="mt-10" ></custom-button>
      </div>
      </div>

      <div class="text-center md:w-[30%] mb-20 mx-5">
        <h3 class="text-2xl mb-10">Variety of Leaderboards</h3>
        <p>View leaderboards based on Player, Hero, or Role using Heroes Profile Rating. Get talent builds, and navigate directly to player's profiles.</p>
        
        <custom-button :href="'#'" :text="'View Leaderboards'" :alt="'View Leaderboards'" :size="'big'" class="mt-10" ></custom-button>
      </div>


      <div class="text-center md:w-[30%] mb-20 flex flex-col mx-5">
        <h3 class="text-2xl mb-10">Heroes Profile API</h3>
        <p>Heroes Profile API is a tool used to get Heroes of the Storm data parsed for HeroesProfile.com. </p>
      
        <custom-button  :href="'https://api.heroesprofile.com/Api'" :targetblank="true" :text="'API'" :alt="'API'"  :size="'big'" class="mt-auto"  :color="'teal'"></custom-button>
      </div>

      <div class="text-center md:w-[30%] mb-20 flex flex-col mx-5">
        <h3 class="text-2xl mb-10">Player profile</h3>
        <p>See all player stats in one place.  See data for individual maps or heroes played, match history and comparisons all from within a streamlined profile.</p>
        <div class="flex mt-auto justify-center">
          <search-component :type="'alt'" :buttonText="'Find Player'" :labelText="'Enter a battletag'"></search-component>
        </div>
      </div>
    </div>


    <div class="bg-teal p-20">
      <p c>Heroes Profile uses data from Heroes Profile API.  Heroes Profile API uploads are in open Heroes of the Storm replay database with user uploaded replay data.
        Currently, Heroes Profile has pulled 49,812,486 replays up to and including data from patch
      2.55.3.90670 and date/time 2023-09-06 18:20:21  and incorporated them into our dataset.</p>
      <p>For more information on Heroes Profile API navigate to <a href="https://api.heroesprofile.com/">https://api.heroesprofile.com/</a></p>
    </div>
  </div>
</template>

<script>

  export default {
    name: 'MainPage',
    components: {
    },
    props: {
      user: Object,
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