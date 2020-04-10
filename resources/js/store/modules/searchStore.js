export default {
    namespaced: true,
    state: {
        defaultGameType: "Storm League",
        game_type_selection: [],
        selectedHeroes: [],
        selectedMaps: [],
        selectedLevels: []
    },
    getters: {
        titleForFilterType : (state, getters) => (filter) => {
            let title
            let count
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

            if (title.length > 20) {
                title = title.slice(0,20)+'…'+`(${count})`
            }
            return title
        },
        selectedGameTypes: (state, getters) => {
			return state.game_type_selection.length === 0 ? [state.defaultGameType] : state.game_type_selection
        },
        heroFormData: (state, getters) => {
            return {
                game_type: getters.selectedGameTypes,
                game_map: state.selectedMaps.length === 0 ? getters.defaultSelectedMaps : state.selectedMaps,
                hero: state.selectedHeroes,
                hero_level: state.selectedLevels
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
        }

    }
}