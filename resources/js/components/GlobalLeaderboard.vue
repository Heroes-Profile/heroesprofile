<template>
  <div>
    <div class="intro">
      <h2>Global Win Rates</h2>
     <div><p>Hero win rates based on differing increments, stat types, game type, or league tier.</p></div>
    </div>
    <search-form></search-form>
    <data-table></data-table>
  </div>
</template>

<script>
import DataTable from "@/components/DataTable.vue";
import SearchForm from "@/components/SearchForm.vue";

export default {
  components: {
    DataTable,
    SearchForm
  }
};
</script>
