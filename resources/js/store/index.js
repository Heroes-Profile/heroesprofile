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
		loading: true
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
		}
	},
	actions:{
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
