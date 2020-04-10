<template>
    <div>
        <b-form-checkbox-group v-model="selection" class="filter-list">
            <b-form-checkbox
              v-for="option in hero_levels"
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


.filter-list 
{
    display: grid;
    padding: 1rem;
    padding-left: 2rem;
    .item {
        padding: 5px 0;
    }
}

.item:hover {
    background: rgba(168, 168, 168, 0.384)
}

</style>

<script>
import { mapState } from "vuex";
import { mapGetters } from "vuex";

export default {
    computed: {
        ...mapGetters({
            hero_levels: 'fieldStore/hero_levels',
        }),
        ...mapState({
            selectedLevels: state => state.searchStore.selectedLevels,
        }),
        selection: {
			get: function ()  {
                return this.selectedLevels || []
			},
			set: function (values)  {
                this.$store.dispatch('searchStore/PUSH_LEVEL', values)
			}
		},

    }

}
</script>