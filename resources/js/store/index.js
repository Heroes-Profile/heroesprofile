import Vue from 'vue';
import Vuex from 'vuex';
Vue.use(Vuex);

import fieldStore from './modules/fieldStore.js'
import searchStore from './modules/searchStore.js'

export default new Vuex.Store({
	strict: false,
	modules: {
		fieldStore,
		searchStore
	}
})
