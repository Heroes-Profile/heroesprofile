import Vue from 'vue';
import VueRouter from 'vue-router';
import DataTable from './components/DataTable.vue';
import SearchForm from './components/SearchForm.vue';
import BoostrapVue from 'bootstrap-vue';
import ThemeSwitcher from './components/ThemeSwitcher.vue';
import VueCookie from 'vue-cookie';
import ImagePopup from './components/ImagePopup.vue';
import Login from './components/Login.vue';
import Multiselect from 'vue-multiselect';
import HeroTalentData from './components/HeroTalentData.vue';
import { routes } from './routes';
require('./bootstrap');


window.Vue = require('vue');

Vue.use(VueRouter);
Vue.use(BoostrapVue);
Vue.use(VueCookie);
Vue.component('theme-switcher', require('./components/ThemeSwitcher.vue').default);
Vue.component('data-table', require('./components/DataTable.vue').default);
Vue.component('search-form', require('./components/SearchForm.vue').default);
Vue.component('image-popup', require('./components/ImagePopup.vue').default);
Vue.component('login', require('./components/Login.vue').default);
Vue.component('multiselect', Multiselect);
Vue.component('hero-talent-data', HeroTalentData);

const router = new VueRouter({
    mode: 'history',
    routes
});
const app = new Vue({
  router
}).$mount('#app')
