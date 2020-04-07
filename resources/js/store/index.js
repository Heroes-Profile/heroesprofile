import Vue from 'vue';
import Vuex from 'vuex';
Vue.use(Vuex);

import fieldStore from './modules/fieldStore.js'

export default new Vuex.Store({
	strict: false,
	modules: {
		fieldStore
	}
})
