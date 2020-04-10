<template>
    <div>
        <b-button class="mx-1 mb-2" size="sm" @click="major = []; minor = []">Clear</b-button>

    <div class="flex-wrap">
    
        <div>
            MAJOR
            <b-form-checkbox-group v-model="major" class="filter-list">
                <b-form-checkbox v-for="option in major_patch" :value="option" :key="option.key" class="item">
                    <div class="checkbox-image" v-if="option.icon">
                        <img :alt="option.name" :src="option.icon" />
                    </div>
                    <span v-else>{{ option.text }}</span>
                </b-form-checkbox>
            </b-form-checkbox-group>
        </div>
        <div>
            MINOR
            <b-form-checkbox-group v-model="minor" class="filter-list">
                <b-form-checkbox v-for="option in minor_patch" :value="option" :key="option.key" class="item">
                    <div class="checkbox-image" v-if="option.icon">
                        <img :alt="option.name" :src="option.icon" />
                    </div>
                    <span v-else>{{ option.text }}</span>
                </b-form-checkbox>
            </b-form-checkbox-group>
        </div>
   
    </div>
    </div>
</template>

<style lang="scss" scoped>
.filter-list {
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
            major_patch: 'fieldStore/major_patch',
            minor_patch: 'fieldStore/minor_patch'
        }),
        ...mapState({
            major_patch_selection: state => state.searchStore.major_patch_selection,
            minor_patch_selection: state => state.searchStore.minor_patch_selection
        }),
        major: {
            get: function() {
                return this.major_patch_selection || []
            },
            set: function(values) {
                this.$store.dispatch('searchStore/PUSH_MAJOR', values)
            }
        },
        minor: {
            get: function() {
                return this.minor_patch_selection || []
            },
            set: function(values) {
                this.$store.dispatch('searchStore/PUSH_MINOR', values)
            }
        }       
    }

}
</script>