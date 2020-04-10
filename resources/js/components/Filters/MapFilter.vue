<template>
    <div>
        <b-button class="mx-1 mb-2" size="sm" @click="selection = []">Clear</b-button>

        <div class='flex-wrap'>
            <b-button class="mx-1" size="sm" @click="selectLanes(2)">2 Lanes</b-button>
            <b-button class="mx-1" size="sm" @click="selectLanes(3)">3 Lanes</b-button>
            <b-button class="mx-1" size="sm" @click="selectGroup(ranked)">All Ranked</b-button>

        </div>
        Ranked Maps
        <b-form-checkbox-group v-model="selection" class="filter-list">
            <b-form-checkbox v-for="option in ranked" :value="option" :key="option.key" class="item">
    
                <span>{{ option.key }}</span>
            </b-form-checkbox>
        </b-form-checkbox-group>

        Additional Maps
        <b-form-checkbox-group v-model="selection" class="filter-list">
            <b-form-checkbox v-for="option in qm" :value="option" :key="option.key" class="item">
    
                <span>{{ option.key }}</span>
            </b-form-checkbox>
        </b-form-checkbox-group>

        ARAM Brawls
        <b-form-checkbox-group v-model="selection" class="filter-list">
            <b-form-checkbox v-for="option in aram" :value="option" :key="option.key" class="item">
    
                <span>{{ option.key }}</span>
            </b-form-checkbox>
        </b-form-checkbox-group>

        Fun Brawls
        <b-form-checkbox-group v-model="selection" class="filter-list">
            <b-form-checkbox v-for="option in brawl" :value="option" :key="option.key" class="item">
    
                <span>{{ option.key }}</span>
            </b-form-checkbox>
        </b-form-checkbox-group>
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
    methods: {
        selectLanes: function(lanes) {
             let lane_maps = _.filter(this.ranked, (m) => {
                return m.lanes === lanes
            })
            this.selection = _.union(this.selection, lane_maps)
        },
        selectGroup: function(group) {
            this.selection = _.union(this.selection, group)
        }
    },
    computed: {
        ...mapGetters({
            maps: 'fieldStore/gameMaps',
        }),
        ...mapState({
            selectedMaps: state => state.searchStore.selectedMaps,
        }),
        ranked() {
            return _.filter(this.maps, (m) => {
                return m.type === 'ranked'
            })
        },
        aram() {
            return _.filter(this.maps, (m) => {
                return m.type === 'aram'
            })
        },
        qm() {
            return _.filter(this.maps, (m) => {
                return m.type === 'qm'
            })
        },
        brawl() {
            return _.filter(this.maps, (m) => {
                return m.type === 'brawl'
            })
        },
        selection: {
            get: function() {
                return this.selectedMaps || []
            },
            set: function(values) {
                this.$store.dispatch('searchStore/PUSH_MAP', values)
            }
        },

    }

}
</script>