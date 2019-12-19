<template>
  <div>
    <b-button-group role="toolbar" btn-group-toggle>
          <b-button v-on:click="lightMode">Light</b-button>
          <b-button v-on:click="darkMode">Dark</b-button>
    </b-button-group>
  </div>
</template>
<script type="text/ecmascript-6">
    export default {
        mounted() {

        },
          methods: {
            lightMode(){
              //set server cookie to light
              document.body.classList.remove("darkMode");

            },
            darkMode(){
              //set server cookie to dark
              document.body.className = "darkMode";
            }
          }

    }
</script>
