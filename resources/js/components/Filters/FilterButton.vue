<template>
    <div>
        <b-button id="popover-target-1">
            {{filter}}
        </b-button>
        <b-popover target="popover-target-1" triggers="click" placement="bottomright">
            <!-- <template v-slot:title>
</template>-->
            <component :is="filter"></component>
        </b-popover>
    </div>
</template>

<script>
import GameTypeFilter from './GameTypeFilter.vue'
import HeroFilter from './HeroFilter.vue'

import { mapState } from "vuex";

export default {
    props: ['filter'],
    components: {
        GameTypeFilter,
        HeroFilter
    },
    computed: {
        // ...mapState({
        //     selectedTypes: 'searchStore/selectedTypes',
        //     selectedHeroes: 'searchStore/selectedHeroes'
        // }),
        // title() {
        //     console.log(this.selectedTypes)
        //     if (this.filter === 'game-type-filter') {
        //         return this.selectedTypes && this.selectedTypes.length > 0 ? this.selectedTypes.join(", ") : "Storm League"
        //     }

        //     if (this.filter === 'hero-filter') {
        //         return this.selectedHeroes && this.selectedHeroes.length > 0 ? this.selectedHeroes.join(", ") : 'All Heroes'
        //     }

        // }
    }

}
</script>