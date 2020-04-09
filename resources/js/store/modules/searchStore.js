export default {
    namespaced: true,
    state: {
        defaultGameType: "Storm League",
        game_type_selection: [],
        selectedHeroes: []
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
                hero: state.selectedHeroes
            }
        }
    },
    mutations: {
        SET_GAME_TYPE(state, payload) {
            state.game_type_selection = payload
        },
        SET_HEROES(state, payload) {
            state.selectedHeroes = payload
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

        }
    }
}