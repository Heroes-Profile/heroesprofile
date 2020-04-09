<template>
    <div>
        <b-button id="popover-target-1">
            {{titleForFilterType(filter)}}
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
import { mapGetters } from "vuex";

export default {
    props: ['filter'],
    components: {
        GameTypeFilter,
        HeroFilter
    },
    computed: {
        ...mapGetters({
			titleForFilterType: 'searchStore/titleForFilterType'
		})
    }

}
</script>