require('./bootstrap')

import Vue from 'vue';
import store from './store'
import router from './router'

import VueCookie from 'vue-cookie';
Vue.use(VueCookie);

import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

import BoostrapVue from 'bootstrap-vue'
Vue.use(BoostrapVue);

import Multiselect from 'vue-multiselect';
Vue.component('multiselect', Multiselect);


// TODO: What is this?
// import vClickOutside from 'v-click-outside';
// Vue.use(vClickOutside);

import HeroesProfileApp from './HeroesProfileApp'
const app = new Vue({
	el: '#app',
	router,
	store,
	template: `<heroes-profile-app></heroes-profile-app>`,
	components: {HeroesProfileApp},
	mounted () {
		console.log('app.js mounted')
		this.$store.dispatch('fieldStore/INIT_ALL_RESOURCES')
	}
})
