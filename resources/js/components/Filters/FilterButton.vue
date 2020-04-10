<template>
    <div>
        <b-button :id="id">
            {{titleForFilterType(filter)}}
        </b-button>
        <b-popover :target="id" triggers="click" placement="bottom">
            <!-- <template v-slot:title>
</template>-->
            <component :is="filter"></component>
        </b-popover>
    </div>
</template>

<script>
import GameTypeFilter from './GameTypeFilter.vue'
import HeroFilter from './HeroFilter.vue'
import MapFilter from './MapFilter.vue'
import HeroLevelFilter from './HeroLevelFilter.vue'
import RankFilter from './RankFilter.vue'

import idMixin from '@/mixins/idMixin'

import { mapState } from "vuex";
import { mapGetters } from "vuex";

export default {
    props: ['filter'],
    mixins: [idMixin],	
    components: {
        GameTypeFilter,
        HeroFilter,
        MapFilter,
        HeroLevelFilter,
        RankFilter
    },
    computed: {
        ...mapGetters({
			titleForFilterType: 'searchStore/titleForFilterType'
		})
    }

}
</script>