<template>
  <div>

    <search-component></search-component>



    <h1>Main Paige has these components.</h1>
    <ul class="list-group">
      <li class="list-group-item"><h2>Navigation with 3 buttons</h2></li>
      <ul class="list-group">
        <li class="list-group-item">Heroes Profile API</li>
        <li class="list-group-item">Uploader</li>
        <li class="list-group-item">Remove Ads/Patreon</li>
      </ul>
    </ul>


    

        
       
        
    <ul class="list-group">
      <li class="list-group-item"><h2>Second Navigation</h2></li>
      <ul class="list-group">
        <li class="list-group-item">Global Hero Stats which is a dropdown to more links</li>
        <li class="list-group-item">Leaderboards</li>
        <li class="list-group-item">Tools which is a dropdown to more links</li>
        <li class="list-group-item">Community ESports</li>
        <li class="list-group-item">(User profile if set) or Set Active Battletag if not set</li>
        <li class="list-group-item">Search bar to look for a player</li>
      </ul>
    </ul>


    <ul class="list-group">
      <li class="list-group-item">Global Hero Stats which is a dropdown to more links</li>
      <li class="list-group-item">Leaderboards</li>
      <li class="list-group-item">Tools which is a dropdown to more links</li>
      <li class="list-group-item">Community ESports</li>
      <li class="list-group-item">(User profile if set) or Set Active Battletag if not set</li>
      <li class="list-group-item">Search bar to look for a player</li>
 
    </ul>  
     
      
      

    
    Info box on what to do with search bar
    Search bar for battletag (same functionality as one above)
    Text talking about find another player - seems functionality is same as search bar
    Section on basic summary of whats on site
      Contains components for info and links
    Info box about Heroes Profile
    Info box about Heroes Profile API
    Footer
      Logo
      3 links to Remove Ads/Patreon, Community Esports, Heroes Profile API
      Privacy Policy
      Another info box about what the site does


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