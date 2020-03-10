<template>
	<div class="container-fluid" >
		<nav-bar></nav-bar>		 
		<transition :name="$root.transition" mode="out-in">
			<router-view ref="main"></router-view>
		</transition>
	</div>
  </div>
</template>
<script>
import NavBar from '@/components/NavBar.vue'
// import DataTable from '@/components/DataTable.vue';
// import SearchForm from './components/SearchForm.vue';
// import BoostrapVue from 'bootstrap-vue';
// import ThemeSwitcher from './components/ThemeSwitcher.vue';
// import ImagePopup from './components/ImagePopup.vue';
// import Login from './components/Login.vue';
// import Multiselect from 'vue-multiselect';
// import HeroTalentData from './components/HeroTalentData.vue';
// import StatBox from './components/StatBox.vue';

//Profile Vue Components
// import TabSwitcher from './components/Profile/TabSwitcher.vue';
// import HeroSummary from './components/Profile/HeroSummary.vue';

//Profile Vue Components
// Vue.component('profile-tab-switcher', TabSwitcher);
// Vue.component('hero-summary', HeroSummary);
// Vue.component('stat-box', StatBox);
// Vue.component('theme-switcher', require('./components/ThemeSwitcher.vue').default);
// Vue.component('data-table', require('./components/DataTable.vue').default);
// Vue.component('search-form', require('./components/SearchForm.vue').default);
// Vue.component('image-popup', require('./components/ImagePopup.vue').default);
// Vue.component('login', require('./components/Login.vue').default);
// Vue.component('multiselect', Multiselect);
// Vue.component('hero-talent-data', HeroTalentData);

export default {
	components: {
		NavBar
	}
	
}

</script>
