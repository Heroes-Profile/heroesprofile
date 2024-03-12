<template>
  <div>

    <div v-if="showPopup" class="modal">
      <new-user-popup @popupclosed="showPopup = false"></new-user-popup>
    </div>


    <div class="text-center py-10">
      <img class="block m-4 md:w-2/5 md:max-w-6xl mr-auto ml-auto pl-25 max-md:px-4" src="/images/logo/full_deathwing.png"/>

      <div class="block text-center mx-auto align-items-center justify-center flex">
        <search-component :type="'alt'" :buttonText="'Find Player'" :labelText="'Enter a battletag'"></search-component>

      </div>
    </div>


    <div class="flex   md:p-20 bg-lighten flex-wrap justify-center items-between max-md:py-4">
      <!--
      <a href="/Compare" class="text-center md:w-[30%] mb-20 mx-5 flex flex-col justify-stretch">

        <i class="fas fa-users" style="font-size: 100px;"></i>

        <h3 class="text-2xl mb-10 mt-2">Player comparison</h3>
        <p>See how you compare to other players or to a certain league tier. You can compare up to four players at one time.</p>
        
        <custom-button  :href="'/Compare'" :text="'Compare'" :alt="'Compare players'" :size="'big'" class="mt-auto"></custom-button>
      </a>

      -->



        <div class="text-center md:w-[30%] mb-20 flex flex-col mx-5">

            <i class="fa-solid fa-globe" style="font-size: 100px;"></i>

            <h3 class="text-2xl mb-10 mt-2">Global Stats</h3>
            <p>Variety of global stats for heroes including, overall stats, builds, compositions, draft, matchups, and more.</p>
            <div class="flex mt-auto gap-10">
              <custom-button  :href="'/Global/Hero'" :text="'Hero Stats'" :alt="'Hero Stats'" :size="'big'" class="mt-auto flex-1" ></custom-button>
              <custom-button :href="'/Global/Talents'" :text="'Talent Stats'" :alt="'Hero Stats'" :size="'big'"  class="mt-auto flex-1" ></custom-button>
            </div>
        </div>
      

      <a href="/Global/Leaderboard" class="text-center md:w-[30%] mb-20 mx-5">

        <i class="fas fa-list-ol" style="font-size: 100px;"></i>

        <h3 class="text-2xl mb-10 mt-2">Variety of Leaderboards</h3>
        <p>View leaderboards based on Player, Hero, or Role using Heroes Profile Rating. Get talent builds, and navigate directly to player's profiles.</p>
        
        <custom-button :href="'/Global/Leaderboard'" :text="'View Leaderboards'" :alt="'View Leaderboards'" :size="'big'" class="mt-10" ></custom-button>
      </a>


      <a href="https://api.heroesprofile.com/Api" target="_blank" class="text-center md:w-[30%] mb-20 flex flex-col mx-5">

        <i class="fa-solid fa-database" style="font-size: 100px;"></i>

        <h3 class="text-2xl mb-10 mt-2">Heroes Profile API</h3>
        <p class="mb-10">Heroes Profile API is a tool used to get Heroes of the Storm data parsed for HeroesProfile.com. </p>
      
        <custom-button  :href="'https://api.heroesprofile.com/Api'" :targetblank="true" :text="'API'" :alt="'API'"  :size="'big'" class="mt-auto"  :color="'teal'"></custom-button>
      </a>

      <div class="text-center md:w-[30%] mb-20 flex flex-col mx-5">

        <i class="fas fa-address-card" style="font-size: 100px;"></i>

        <h3 class="text-2xl mb-10 mt-2">Player profile</h3>
        <p>See all player stats in one place.  See data for individual maps or heroes played, match history and comparisons all from within a streamlined profile.</p>
        <div class="flex mt-auto justify-center">
          <search-component :type="'alt'" :buttonText="'Find Player'" :labelText="'Enter a battletag'" class="mt-auto"></search-component>
        </div>
      </div>
    </div>


    <div class="bg-teal p-5">
      <p c>Heroes Profile uses data from Heroes Profile API.  Heroes Profile API uploads are in open Heroes of the Storm replay database with user uploaded replay data.
        Currently, Heroes Profile has pulled {{ getValueLocal(maxreplayid) }} replays up to and including data from patch
      {{ latestpatch }} and date/time <format-date :input="latestgamedate"></format-date> and incorporated them into our dataset.</p>
      <p>For more information on Heroes Profile API navigate to <a class="link" href="https://api.heroesprofile.com/">https://api.heroesprofile.com/</a></p>
      
    </div>
  </div>
</template>

<script>
import Cookies from 'js-cookie';


  export default {
    name: 'MainPage',
    components: {
    },
    props: {
      user: Object,
      maxreplayid: Number,
      latestpatch: String,
      latestgamedate: String,
    },
    data(){
      return {
        showPopup: true,
      }
    },
    created(){
      Cookies.remove('additional-battletags');

      if (typeof localStorage !== 'undefined' && localStorage !== null) {
        if (localStorage.getItem('newUserPopup')) {
          this.showPopup = false;
        }
      } else {
        this.showPopup = false;
      }
    },
    mounted() {
    },
    computed: {

    },
    watch: {
    },
    methods: {
      getValueLocal(value){
        return value ? value.toLocaleString('en-US') : "";
      },
    }
  }
</script>