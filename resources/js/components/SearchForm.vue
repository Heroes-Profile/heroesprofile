<template>
  <div class="filter-form" v-if="dataLoaded">
    <nav class="navbar primary-filter-bar">

      <filter-button v-for="(filter, index) in filters" :key="index" :filter="filter">
      </filter-button>

    </nav>
  </div>
</template>

<script type="text/ecmascript-6">
import { mapGetters } from "vuex";
import { mapState } from "vuex";

import FilterButton from '@/components/Filters/FilterButton.vue'

export default {
  components: {
    FilterButton
  },
  data() {
    return {
      form: {},
      filters: [
          'game-type-filter',
          'hero-filter',
          'map-filter',
          'hero-level-filter',
          'rank-filter',
          'time-filter'
      ]
    }
  },
  filters: {
    caps: function(value) {
      if (!value) return "";
      return value
        .split("_")
        .map(function(item) {
          return item.charAt(0).toUpperCase() + item.substring(1);
        })
        .join(" ");
    },
    labels: function(selection) {
      // Add this filter to labels to display what's filtered in that field
      if (!selection) return "";
      var values = [];
      var i = 0;
      console.log("value", selection);
      var counter = 0;
      for (const [key, val] of Object.entries(selection)) {
        if (val.text) {
          values.push(val.text);
          counter++;
          if (counter > 1) {
            values.push("...");
            break;
          }
        } else if (val.name) {
          values.push(val.name);
          counter++;
          if (counter > 1) {
            values.push("...");
            break;
          }
        } else {
          values.push(val);
          counter++;
          if (counter > 1) {
            values.push("...");
            break;
          }
        }
      }
      return "(" + values.join(", ") + ")";
    }
  },
  computed: {
    ...mapGetters({
      rawfields: 'fieldStore/rawfields'
    }),
    dataLoaded() {
      return this.rawfields || false
    }
  }
};
</script>
<style>
.primary-filter-popup {
  background-color: white;
  color: black;
}
.popover-body {
  display: flex;
  flex-wrap: wrap;
}
.popover-body .menu-close {
  flex-basis: 100%;
}
.multiselect-wrapper {
  width: 100%;
}

.test3 {
  position: relative;
}
.custom-popover {
  position: absolute;
}
</style>
