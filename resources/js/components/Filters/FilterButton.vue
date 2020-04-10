<template>
    <div>
        <b-button :id="id">
            {{titleForFilterType(filter)}}
        </b-button>
        <b-popover :target="id" triggers="hover" placement="bottom">
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
import TimeFilter from './TimeFilter.vue'

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
        RankFilter,
        TimeFilter
    },
    computed: {
        ...mapGetters({
			titleForFilterType: 'searchStore/titleForFilterType'
		})
    }

}
</script>