export default {
    namespaced: true,
    state: {
        defaultGameType: "Storm League",
        game_type_selection: [],
        selectedHeroes: []
    },
    getters: {
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
        }
    },
    actions: {
        PUSH_GAME_TYPE(context, payload) {
            context.commit('SET_GAME_TYPE', payload)
            context.dispatch("fieldStore/UPDATE_HERO_DATA", null, {root: true});

        }
    }
}