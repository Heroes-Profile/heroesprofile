<template>
  <div>
    <div class="text-center py-56">
      <img class="block m-4 w-full max-w-6xl mr-auto ml-auto pl-16" src="/images/logo/full_deathwing.png"/>
    <search-component></search-component>
  </div>

    <div class="flex space-x-20 p-20 bg-lighten">
      <div class="text-center">
        <h3 class="text-2xl mb-10 ">In-depth player comparison</h3>
        <p>See how you compare to other players or to a certain league tier. You can compare up to four players at one time.</p>
        
        <custom-button :href="'#'" :text="'Compare'" :alt="'Compare players'" :size="'big'" class="mt-10"></custom-button>
      </div>
      <div class="text-center">
        <h3 class="text-2xl mb-10">Extensive player profile</h3>
        <p>See all of your stats in one place: see data for individual maps or heroes played, match history and comparisons all from within a streamlined profile.</p>
       
        <custom-button :href="'#'" :text="'Find your profile'" :alt="'Find your profile'" :size="'big'" class="mt-10"></custom-button>
      </div>
      <div class="text-center">
        <h3 class="text-2xl mb-10">Variety of Leaderboards</h3>
        <p>View leaderboards based on Player, Hero, or Role using Heroes Profile Rating. Get talent builds, and navigate directly to player's profiles.</p>
       
        <custom-button :href="'#'" :text="'View Leaderboards'" :alt="'View Leaderboards'" :size="'big'" class="mt-10" ></custom-button>
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