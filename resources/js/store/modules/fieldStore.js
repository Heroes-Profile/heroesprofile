const _ = require('lodash')
const api = require('./api.js').default
const maps = require('./maps.js').default.maps
export default {

    namespaced: true,
	state: {
		formFields: {},
		formData: [],
		timeframe: [],
		loading: true,
		raw: {},
		maps: maps
	},
	getters: {
        formData: (state, getters) => {
			return state.formData
        },		
        rawfields: (state, getters) => {
			return state.raw.rawfields
		},
		hero_levels: (state, getters) => {
			return state.raw.rawfields.hero_level
		},
		heroes: (state, getters) => {
			return state.raw.rawfields.heroes
		},
		heroRoles: (state, getters) => {
			return state.raw.rawfields.role
		},
		gameTypes: (state, getters) => {
			return state.raw.rawfields.game_type
		},
		gameMaps: (state, getters) => {
			return state.maps
		},
		primaryfields: (state, getters) => {
			return state.raw.primaryfields
		},
		secondaryfields: (state, getters) => {
			return state.raw.secondaryfields
		},
		timeframe_type: (state, getters) => {
			return state.raw.timeframe_type
		}
	},
	mutations: {
		updateFormFields (state, fields){
			state.loading = true
			state.formFields = fields
		},
		formData (state, fields){
			state.formData = fields
			state.loading = false
		},
		updateInitialResources (state, payload) {
			state.raw = payload
		}
	},
	actions:{
		INIT_ALL_RESOURCES (context, payload = null) {
			axios
			.get("/api/heroes")
			.then(response => {
				console.assert(response.data != null, '/api/heroes returned null response data, unable to initialize')
				context.commit('updateInitialResources', response.data)
			})
			.catch(err => {
				console.error(err)
			})
		}, 
		UPDATE_HERO_DATA (context, payload) {
			api.postHeroUpdate(context)
		},
		updateFormData ({ commit, state }, fields){
			commit('updateFormFields',  fields);
			axios.post('/get_heroes_stats_table_data', {
				params: {
					'data' : fields
				}
			}).then(response => {
				commit('formData', response.data);
				
			});
		}
	}
}
