<template>
    <div>
        <b-button :id="id">
            {{titleForFilterType(filter)}}
        </b-button>
        <b-popover :target="id" triggers="click" placement="bottomright">
            <!-- <template v-slot:title>
</template>-->
            <component :is="filter"></component>
        </b-popover>
    </div>
</template>

<script>
import GameTypeFilter from './GameTypeFilter.vue'
import HeroFilter from './HeroFilter.vue'
import idMixin from '@/mixins/idMixin'

import { mapState } from "vuex";
import { mapGetters } from "vuex";

export default {
    props: ['filter'],
    mixins: [idMixin],	
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