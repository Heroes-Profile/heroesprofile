import Vue from 'vue';

import Vuex from 'vuex';
Vue.use(Vuex);

export default new Vuex.Store({
	strict: false,
	state: {
		formFields: {},
		formData: [],
		timeframe: [],
		currentAjaxURL: '',
		loading: true,
		raw: {}
	},
	getters: {
		rawfields: (state, getters) => {
			return state.raw.rawfields
		},
		gameMaps: (state, getters) => {
			return state.raw.rawfields.game_map
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
		updateAjaxURL (state, fields){
			state.currentAjaxURL = fields
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
// \			  this.rawfields = response.data.rawfields
// 			  this.primaryFields = response.data.primaryFields
// 			  this.secondaryfields = response.data.secondaryfields
// 			  this.timeframe_type = response.data.timeframe_type
			//   this.$nextTick( () => {
			// 	this.loaded = true
			//   })
			})
			.catch(err => {
				console.error(err)
			})
		},
		updateFormData ({ commit, state }, fields){
			commit('updateFormFields',  fields);
			axios.post(state.currentAjaxURL, {
				params: {
					'data' : fields
				}
			}).then(response => {
				console.log(response.data);
				commit('formData', response.data);
				
			});
		}
	}
})
