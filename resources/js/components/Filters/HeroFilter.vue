<template>
      <div class="heroes">
        <b-form-checkbox-group v-model="gameTypes" class="filter-list">
            <b-form-checkbox
              v-for="option in heroes"
              :value="option.key"
              :key="option.key"
              class= "item"
            >
              <div class="checkbox-image" v-if="option.icon">
                <img :alt="option.name" :src="option.icon" />
              </div>
              <span v-else>{{ option.text }}</span>
            </b-form-checkbox>
          </b-form-checkbox-group>
    </div>
</template>


<style lang="scss" scoped>

.heroes {
    height: 500px;
    overflow: scroll;
}

.filter-list 
{
    display: grid;
    padding: 1rem;
    padding-left: 2rem;
    .item {
        padding: 5px 0;
    }
}

</style>

<script>
import { mapState } from "vuex";
import { mapGetters } from "vuex";

export default {
    computed: {
        ...mapGetters({
            heroes: 'fieldStore/heroes',
            selectedGameTypes: 'searchStore/selectedGameTypes'

        }),
        gameTypes: {
			get: function ()  {
                return this.selectedGameTypes || []
			},
			set: function (values)  {
                this.$store.dispatch('searchStore/PUSH_GAME_TYPE', values)
			}
		},

    }

}
</script>