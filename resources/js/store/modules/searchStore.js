export default {
    namespaced: true,
    state: {
        defaultGameType: "Storm League",
        selection: [],
        availableGameTypes: ["Quick Match" , "Unranked", "Storm League", "Brawl"]
    },
    getters: {
        selectedGameTypes: (state, getters) => {
			return state.selection.length === 0 ? [state.defaultGameType] : state.selection
        },	
    },
    mutations: {
        SET_GAME_TYPE(state, payload) {
            state.selection = payload
        }
    },
    actions: {
        PUSH_GAME_TYPE(context, payload) {
            context.commit('SET_GAME_TYPE', payload)
        }
    }
}