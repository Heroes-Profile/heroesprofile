<template>
    <div>
        <b-button id="popover-target-1">
            {{title}}
        </b-button>
        <b-popover target="popover-target-1" triggers="click" placement="bottom">
            <template v-slot:title>Game Type</template>
            <game-type-filter></game-type-filter>
        </b-popover>
    </div>
</template>

<script>
import GameTypeFilter from './GameTypeFilter.vue'
import { mapGetters } from "vuex";

export default {
    props: ['field'],
    components: {
        GameTypeFilter
    },
    computed: {
        ...mapGetters({
            selectedGameTypes: 'searchStore/selectedGameTypes'
        }),
        title() {
            return this.selectedGameTypes.join(", ") || 'Game Type'
        }
    }

}
</script>