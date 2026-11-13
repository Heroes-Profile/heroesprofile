<template>
  <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>
  <div>
    <table-search v-if="!showNone" :query="userinput" :results="battletagresponse" @select="openProfile" @none="showNone = true"></table-search>
    <div v-else class="max-w-[93.75rem] mx-auto mt-10 w-full flex justify-center items-center flex-col">
      <search-component :type="'alt'" :buttonText="'Find Player'" :labelText="'Enter a BattleTag'"></search-component>
      <div class="rounded bg-red text-white p-4 mb-4">{{ $t('No BattleTag found for :userinput Try Again?', { userinput: userinput }) }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SearchedBattletagHolding',
  components: {
  },
  props: {
    userinput: String,
    type: String,
    patreonUser: Boolean,
  },
  data(){
    return {
      // null while the search runs
      battletagresponse: null,
      cancelTokenSource: null,
      showNone: false,
    }
  },
  mounted() {
    this.getData();
  },
  methods: {
    async getData(){
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post("/api/v1/battletag/search", {
          userinput: this.userinput,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        // A validation failure answers an object rather than a list.
        this.battletagresponse = Array.isArray(response.data) ? response.data : [];
      }catch(error){
        this.battletagresponse = [];
      }finally {
        this.cancelTokenSource = null;
      }
    },
    openProfile(item) {
      this.$redirectToProfile(item.battletagShort, item.blizz_id, item.region);
    },
  }
}
</script>
