export default {
    namespaced: true,
    state: {
        defaultGameType: "Storm League",
        game_type_selection: [],
        selectedHeroes: [],
        selectedMaps: [],
        selectedLevels: [],
        player_league_tier_selected: [],
        role_league_tier_selected: [],
        hero_league_tier_selected: [],
        major_patch_selection: [],
        minor_patch_selection: []
    },
    getters: {
        titleForFilterType : (state, getters) => (filter) => {
            let title = 'All'
            let count = 0
            if (filter === 'game-type-filter') {
                title = state.game_type_selection && state.game_type_selection.length > 0 ? state.game_type_selection.join(", ") : "Storm League"
                count = state.game_type_selection.length
            }

            if (filter === 'hero-filter') {
                title = state.selectedHeroes && state.selectedHeroes.length > 0 ? state.selectedHeroes.join(", ") : 'All Heroes'
                count = state.selectedHeroes.length
            }

            if (filter === 'map-filter') {
                title = state.selectedMaps && state.selectedMaps.length > 0 ? _.map(state.selectedMaps, 'key').join(", ") : 'All Maps'
                count = state.selectedMaps.length
            }

            if (filter === 'hero-level-filter') {
                title = state.selectedLevels && state.selectedLevels.length > 0 ? state.selectedLevels.join(", ") : 'All Levels'
                count = state.selectedLevels.length
            }

            if (filter === 'rank-filter') {
                count = state.hero_league_tier_selected.length + state.role_league_tier_selected.length + state.player_league_tier_selected.length
                
                if (state.hero_league_tier_selected.length > 0) {
                    title = 'Hero'
                }
                if (state.role_league_tier_selected.length > 0) {
                    title = title+'Role'
                }
                if (state.player_league_tier_selected.length > 0) {
                    title = title+'Player'
                }
                if (count == 0) {
                    title = "All Ranks"
                }
            }

            if (filter === 'time-filter') {
                title = 'Time'
            }

            if (title.length > 20) {
                title = title.slice(0,20)+'…'+`(${count})`
            }
            return title
        },
        selectedGameTypes: (state, getters) => {
			return state.game_type_selection.length === 0 ? [state.defaultGameType] : state.game_type_selection
        },
        timeframe: (state, getters, rootState, rootGetters) => {
            let time = _.union(state.major_patch_selection, state.minor_patch_selection)
            if (time.length === 0) {
                return [rootGetters['fieldStore/minor_patch'][0]]
            }
            return time
        },
        heroFormData: (state, getters) => {
            return {
                game_type: getters.selectedGameTypes,
                game_map: state.selectedMaps.length === 0 ? getters.defaultSelectedMaps : state.selectedMaps,
                hero: state.selectedHeroes,
                hero_level: state.selectedLevels,
                player_league_tier: state.player_league_tier_selected,
                role_league_tier: state.role_league_tier_selected,
                hero_league_tier: state.hero_league_tier_selected,
                timeframe: getters.timeframe
            }
        },
        defaultSelectedMaps: (state, getters) => {
            return _.filter(state.maps, (m) => {
                return m.type === 'ranked'
            })
        }
    },
    mutations: {
        SET_GAME_TYPE(state, payload) {
            state.game_type_selection = payload
        },
        SET_HEROES(state, payload) {
            state.selectedHeroes = payload
        },
        SET_MAP(state, payload) {
            state.selectedMaps = payload
        },
        SET_LEVEL(state, payload) {
            state.selectedLevels = payload
        },
        SET_PLAYER_RANK(state, payload) {
            state.player_league_tier_selected = payload
        },
        SET_ROLE_RANK(state, payload) {
            state.role_league_tier_selected = payload
        },
        SET_HERO_RANK(state, payload) {
            state.hero_league_tier_selected = payload
        },
        SET_MAJOR(state, payload) {
            state.major_patch_selection = payload
        },
        SET_MINOR(state, payload) {
            state.minor_patch_selection = payload
        }
    },
    actions: {
        PUSH_GAME_TYPE(context, payload) {
            context.commit('SET_GAME_TYPE', payload)
            context.dispatch("fieldStore/UPDATE_HERO_DATA", null, {root: true});

        },
        PUSH_HEROES(context, payload) {
            context.commit('SET_HEROES', payload)
            context.dispatch("fieldStore/UPDATE_HERO_DATA", null, {root: true});
        },
        PUSH_MAP(context, payload) {
            context.commit('SET_MAP', payload)
            context.dispatch("fieldStore/UPDATE_HERO_DATA", null, {root: true});
        },
        PUSH_LEVEL(context, payload) {
            context.commit('SET_LEVEL', payload)
            context.dispatch("fieldStore/UPDATE_HERO_DATA", null, {root: true});
        },
        PUSH_PLAYER_RANK(context, payload) {
            context.commit('SET_PLAYER_RANK', payload)
            context.dispatch("fieldStore/UPDATE_HERO_DATA", null, {root: true});
        },
        PUSH_ROLE_RANK(context, payload) {
            context.commit('SET_ROLE_RANK', payload)
            context.dispatch("fieldStore/UPDATE_HERO_DATA", null, {root: true});
        },
        PUSH_HERO_RANK(context, payload) {
            context.commit('SET_HERO_RANK', payload)
            context.dispatch("fieldStore/UPDATE_HERO_DATA", null, {root: true});
        },
        PUSH_MAJOR(context, payload) {
            context.commit('SET_MAJOR', payload)
            context.dispatch("fieldStore/UPDATE_HERO_DATA", null, {root: true});
        },
        PUSH_MINOR(context, payload) {
            context.commit('SET_MINOR', payload)
            context.dispatch("fieldStore/UPDATE_HERO_DATA", null, {root: true});
        }

    }
}