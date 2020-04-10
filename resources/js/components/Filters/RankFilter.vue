<template>
    <div class="flex-wrap">
    <div>
        PLAYERS
        <b-form-checkbox-group v-model="player" class="filter-list">
            <b-form-checkbox v-for="option in player_tiers" :value="option.key" :key="option.key" class="item">
                <div class="checkbox-image" v-if="option.icon">
                    <img :alt="option.name" :src="option.icon" />
                </div>
                <span v-else>{{ option.text }}</span>
            </b-form-checkbox>
        </b-form-checkbox-group>
    </div>
    <div>
        HEROES
        <b-form-checkbox-group v-model="hero" class="filter-list">
            <b-form-checkbox v-for="option in hero_tiers" :value="option.key" :key="option.key" class="item">
                <div class="checkbox-image" v-if="option.icon">
                    <img :alt="option.name" :src="option.icon" />
                </div>
                <span v-else>{{ option.text }}</span>
            </b-form-checkbox>
        </b-form-checkbox-group>
    </div>
    <div>
        ROLE
        <b-form-checkbox-group v-model="role" class="filter-list">
            <b-form-checkbox v-for="option in role_tiers" :value="option.key" :key="option.key" class="item">
                <div class="checkbox-image" v-if="option.icon">
                    <img :alt="option.name" :src="option.icon" />
                </div>
                <span v-else>{{ option.text }}</span>
            </b-form-checkbox>
        </b-form-checkbox-group>
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
            player_tiers: 'fieldStore/player_tiers',
            hero_tiers: 'fieldStore/hero_tiers',
            role_tiers: 'fieldStore/role_tiers'
        }),
        ...mapState({
            player_league_tier_selected: state => state.searchStore.player_league_tier_selected,
            role_league_tier_selected: state => state.searchStore.role_league_tier_selected,
            hero_league_tier_selected: state => state.searchStore.hero_league_tier_selected
        }),
        player: {
            get: function() {
                return this.player_league_tier_selected || []
            },
            set: function(values) {
                this.$store.dispatch('searchStore/PUSH_PLAYER_RANK', values)
            }
        },
        role: {
            get: function() {
                return this.role_league_tier_selected || []
            },
            set: function(values) {
                this.$store.dispatch('searchStore/PUSH_ROLE_RANK', values)
            }
        },
        hero: {
            get: function() {
                return this.hero_league_tier_selected || []
            },
            set: function(values) {
                this.$store.dispatch('searchStore/PUSH_HERO_RANK', values)
            }
        }


    }

}
</script>