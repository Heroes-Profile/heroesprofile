<template>
  <div>
        Lets rethink how filtering is done.
    <h1>Leaderboard</h1>
    <infobox :input="infoText1"></infobox>
    <infobox :input="infoText2"></infobox>

    {{ "<filters></filters>" }}
  </div>
</template>

<script>
export default {
  name: 'GlobalLeaderboard',
  components: {
  },
  props: {
  },
  data(){
    return {
      infoText1: "Leaderboards are a reflection of user uploaded data. Due to replay file corruption or other issues, the data is not always reflective of real player stats. Please keep that in mind when reviewing leaderboards.",
      infoText2: " Only users who upload replays through an approved automatic uploader will be able to rank on the leaderboards. The main uploader can be found at Heroes Profile Uploader while the secondary uploader that works on platforms other than windows can be found at Heroes Profile Electron Uploader. For any questions, please contact zemill@heroesprofile.com ",
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
        const response = await this.$axios.post("/api/v1/global/leaderboard", {
          season: 24, 
          gameType: 5,
          type: "player",
          stackSize: 1,
        });

        console.log(response.data);
      }catch(error){
        console.log(error);
      }
    },
  }
}
</script>