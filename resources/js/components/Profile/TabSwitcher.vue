<template>
  <div>
    <b-button-group role="toolbar" btn-group-toggle>
          <b-button v-on:click="profile">Profile</b-button>
          <b-button v-on:click="friendsAndFoes">Friends and Foes</b-button>
          <b-button v-on:click="heroes">Heroes</b-button>
          <b-button v-on:click="matchups">Matchups</b-button>
          <b-button v-on:click="roles">Roles</b-button>
          <b-button v-on:click="maps">Maps</b-button>
          <b-button v-on:click="talents">Talents</b-button>
          <b-button v-on:click="mmrBreakdown">MMR Breakdown</b-button>
          <b-button v-on:click="matchHistory">Match History</b-button>
    </b-button-group>
  </div>
</template>
<script type="text/ecmascript-6">
    export default {
        mounted() {

        },
          methods: {
          }
    }
</script>
