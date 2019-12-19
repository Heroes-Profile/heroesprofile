import Vue from 'vue';
import Vuex from 'vuex';
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

//Profile Vue Components
import TabSwitcher from './components/Profile/TabSwitcher.vue';
import HeroSummary from './components/Profile/HeroSummary.vue';

import { routes } from './routes';
import vClickOutside from 'v-click-outside';
require('./bootstrap');


window.Vue = require('vue');

Vue.use(VueRouter);
Vue.use(BoostrapVue);
Vue.use(VueCookie);
Vue.use(Vuex);
Vue.use(vClickOutside);
Vue.component('theme-switcher', require('./components/ThemeSwitcher.vue').default);
Vue.component('data-table', require('./components/DataTable.vue').default);
Vue.component('search-form', require('./components/SearchForm.vue').default);
Vue.component('image-popup', require('./components/ImagePopup.vue').default);
Vue.component('login', require('./components/Login.vue').default);
Vue.component('multiselect', Multiselect);
Vue.component('hero-talent-data', HeroTalentData);
let handleOutsideClick

const store = new Vuex.Store({
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
      /*
      var timeframe = [];
      if(fields.timeframe != undefined){
          for (var i = 0; i < fields.timeframe.length; i++) {
            timeframe.push(fields.timeframe[i].value.replace(/['"]+/g, ''));
        }
      }
      timeframe = timeframe.join(',');
      */
      axios.post(state.currentAjaxURL, {
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
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

//Profile Vue Components
Vue.component('profile-tab-switcher', TabSwitcher);
Vue.component('hero-summary', HeroSummary);

const router = new VueRouter({
    mode: 'history',
    routes
});
const app = new Vue({
  router,
  store
}).$mount('#app')
