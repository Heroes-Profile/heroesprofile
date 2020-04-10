<template>
  <div class="filter-form" v-if="dataLoaded">
    <nav class="navbar primary-filter-bar">

      <filter-button v-for="(filter, index) in filters" :key="index" :filter="filter">
      </filter-button>

      <!-- <filter-button></filter-button> -->

      
      <!-- 
        TIME FILTER
        <b-dropdown id="dropdown-form" ref="dropdown1" boundary="window" @hidden="updateFields()">
        <template
          v-slot:button-content
        >Timeframe: {{ timeframetype | caps }} {{ form.timeframe | labels }}</template>
        <b-dropdown-form>
          <b-form-group label="Timeframe Type">
            <b-form-radio v-model="timeframetype" value="major" @change="timeframeChange()">Major</b-form-radio>
            <b-form-radio v-model="timeframetype" value="minor" @change="timeframeChange()">Minor</b-form-radio>
          </b-form-group>
          <b-form-group label="Timeframe" class="multiselect-wrapper">
            <multiselect
              :show-labels="false"
              v-model="form.timeframe"
              track-by="value"
              label="key"
              placeholder="All"
              :multiple="true"
              :options="currentTimeFrameOptions"
              :searchable="true"
              :allow-empty="true"
            ></multiselect>
          </b-form-group>
          <b-button @click="hideDropdowns()" variant="primary" class="menu-close">Apply</b-button>
        </b-dropdown-form>
      </b-dropdown> -->

      <!-- 
        GAME TYPE
        <b-dropdown id="dropdown-form2" ref="dropdown2" boundary="window" @hidden="updateFields()">
        <template v-slot:button-content>Game Type {{ form.gametype | labels }}</template>
        <b-dropdown-form>
          <b-form-checkbox-group v-model="form.game_type" name="rawfields.game_type.text">
            <b-form-checkbox
              v-for="option in rawfields.game_type"
              :value="option.key"
              :key="option.key"
            >
              <div class="checkbox-image" v-if="option.icon">
                <img :alt="option.name" :src="option.icon" />
              </div>
              <span v-else>{{ option.text }}</span>
            </b-form-checkbox>
          </b-form-checkbox-group>
          <b-button @click="hideDropdowns()" variant="primary" class="menu-close">Apply</b-button>
        </b-dropdown-form>
      </b-dropdown> -->

      <!-- 
        HEROES
        <b-dropdown
        id="dropdown-formheroes"
        ref="dropdownheroes"
        boundary="window"
        @hidden="updateFields()"
      >
        <template v-slot:button-content>Heroes {{ form.hero | labels }}</template>
        <b-dropdown-form>
          <b-form-group label="Heroes">
            <b-form-checkbox-group
              buttons
              v-model="form.hero"
              name="rawfields.hero.value"
              class="hero-images-checkbox"
            >
              <b-form-checkbox
                v-for="option in rawfields.hero"
                :value="option.key"
                :key="option.key"
              >
                <image-popup :alttext="option.key" :imgSrc="'/images/heroes/'+option.value+'.png'"></image-popup>
              </b-form-checkbox>
            </b-form-checkbox-group>
          </b-form-group>

          <b-button @click="hideDropdowns()" variant="primary" class="menu-close">Apply</b-button>
        </b-dropdown-form>
      </b-dropdown> -->

      <b-dropdown id="dropdown-form3" ref="dropdown3" boundary="window" @hidden="updateFields()">
        <template v-slot:button-content>Rank</template>
        <b-dropdown-form>
          <div class="filter-group">
            Player Rank
            <multiselect
              v-model="form.player_league_tier"
              track-by="value"
              label="key"
              placeholder="All"
              :multiple="true"
              :options="rawfields.player_league_tier"
              :searchable="true"
              :allow-empty="true"
            ></multiselect>
          </div>
          <div class="filter-group">
            Role Rank
            <multiselect
              v-model="form.role_league_tier"
              track-by="value"
              label="key"
              placeholder="All"
              :multiple="true"
              :options="rawfields.role_league_tier"
              :searchable="true"
              :allow-empty="true"
            ></multiselect>
          </div>
          <div class="filter-group">
            Hero Rank
            <multiselect
              v-model="form.hero_league_tier"
              track-by="value"
              label="key"
              placeholder="All"
              :multiple="true"
              :options="rawfields.hero_league_tier"
              :searchable="true"
              :allow-empty="true"
            ></multiselect>
          </div>
          <b-button @click="hideDropdowns()" variant="primary" class="menu-close">Apply</b-button>
        </b-dropdown-form>
      </b-dropdown>
      <b-dropdown id="dropdown-form4" ref="dropdown4" boundary="window" @hidden="updateFields()">
        <template v-slot:button-content>More</template>
        <b-dropdown-form>
          <div class="filter-group">
            <b-form-group label="Game Map">
              <multiselect
                v-model="form.game_map"
                track-by="value"
                label="key"
                placeholder="All"
                :multiple="true"
                :options="rawfields.game_map"
                :searchable="true"
                :allow-empty="true"
              ></multiselect>
            </b-form-group>

            <b-form-group label="Hero Level">
              <b-form-checkbox-group v-model="form.hero_level" name="rawfields.hero_level.name">
                <b-form-checkbox
                  v-for="option in rawfields.hero_level"
                  :value="option.key"
                  :key="option.key"
                >
                  <div class="checkbox-image" v-if="option.icon">
                    <img :alt="option.name" :src="option.icon" />
                  </div>
                  <span v-else>{{ option.text }}</span>
                </b-form-checkbox>
              </b-form-checkbox-group>
            </b-form-group>
            <b-form-group label="Role">
              <b-form-checkbox-group buttons v-model="form.role" name="rawfields.role.name">
                <b-form-checkbox
                  v-for="option in rawfields.role"
                  :value="option.key"
                  :key="option.key"
                >
                  <div class="checkbox-image" v-if="option.icon">
                    <img :alt="option.name" :src="option.icon" />
                  </div>
                  <span v-else>{{ option.text }}</span>
                </b-form-checkbox>
              </b-form-checkbox-group>
            </b-form-group>
          </div>

          <b-button @click="hideDropdowns()" variant="primary" class="menu-close">Apply</b-button>
        </b-dropdown-form>
      </b-dropdown>
    </nav>
  </div>
</template>

<script type="text/ecmascript-6">
import ImagePopup from "@/components/ImagePopup.vue";
import { mapGetters } from "vuex";
import { mapState } from "vuex";

import FilterButton from '@/components/Filters/FilterButton.vue'

export default {
  components: {
    ImagePopup,
    FilterButton
  },
  data() {
    return {
      form: {},
      timeframetype: "major",
      gametype: "Storm League",
      timeframe: undefined,
      selectedTimeframe: null,
      showTimeframe: false,
      showGameType: false,
      showFilterMenu: false,
      finalFields: {},
      currentlySelectedPopup: "",
      selectedPopover: "",
      filtersChanged: true,
      filters: [
          'game-type-filter',
          'hero-filter',
          'map-filter',
          'hero-level-filter',
          'rank-filter'
      ]
    };
  },
  mounted() {
    this.updateFields();
  },
  watch: {
    form: function() {
      this.filtersChanged = true;
    }
  },
  methods: {
    timeframeChange: function() {
      this.form["timeframe"] = undefined;
      this.timeframe = this.timeframe;
    },
    hideDropdowns() {
      // Close the menu and (by passing true) return focus to the toggle button
      // this.$refs.dropdown1.hide(true);
      // this.$refs.dropdown2.hide(true);
      // this.$refs.dropdown3.hide(true);
      this.$refs.dropdown4.hide(true);
      // this.$refs.dropdownheroes.hide(true);
    },
    updateFields() {
      if (this.filtersChanged) {
        this.finalFields = this.form;
        this.$store.dispatch("fieldStore/updateFormData", this.form);
        this.filtersChanged = false;
      }
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
      gameMaps: 'fieldStore/gameMaps',
      rawfields: 'fieldStore/rawfields',
      primaryfields: 'fieldStore/primaryfields',
      secondaryfields: "fieldStore/secondaryfields",
      timeframe_type: "fieldStore/timeframe_type"
    }),
    dataLoaded() {
      return this.rawfields || false
    },
    currentTimeFrameOptions: function() {
      let timeframe = "";
      switch (this.timeframetype) {
        case "major":
          timeframe = this.rawfields.major_patch;
          break;
        case "minor":
          timeframe = this.rawfields.minor_patch;
          break;
        default:
          timeframe = this.rawfields.major_patch;
      }
      return timeframe;
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
