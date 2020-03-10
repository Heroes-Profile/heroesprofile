<template>
	<div class="data-table" >
		
		<!--<div class="loading" v-if="loading">
		<b-spinner></b-spinner>
	</div>
	<div class="error" v-else-if="error.length > 0">
	Error retreiving data.
</div>-->
<b-table striped bordered responsive small :items="formData" :fields="fields" :busy="loading" :sort-by="sortby" :sort-desc="true" >
	<template v-slot:cell(name)="data" >
		<div class="image-with-name">
			<image-popup  :alttext="data.value.hero_name" :imgSrc="'/images/heroes/'+data.value.short_name+'.png'" :popupdata="'Role: '+ data.value.role+ ' <br>Type: '+data.value.type"></image-popup>  <span class="emphasis">{{ data.value.hero_name }}</span>
			
		</div>
	</template>
	<template v-slot:cell(win_rate)="data">
		{{data.value}}%
	</template>
	<template v-slot:cell(ban_rate)="data">
		{{data.value}}%
	</template>
	<template v-slot:cell(popularity)="data">
		{{data.value}}%
	</template>
	<template v-slot:cell(change)="data">
		{{data.value}}%
	</template>
	<template v-slot:cell(talent_builds)="row">
		<b-button size="sm" @click="row.toggleDetails" class="mr-2">
			{{ row.detailsShowing ? 'Hide' : 'Show'}} <span class="mobileHide">Talent Builds</span>
		</b-button>
	</template>
	<template v-slot:row-details="row" :loaded="false">
		<b-card>
			<b-row class="mb-2">
				
				<hero-talent-data :hero="row.item.name" @loading-status="talentsLoaded=true"></hero-talent-data>
			</b-row>
			
		</b-card>
	</template>
</b-table>

</div>
</template>

<script type="text/ecmascript-6">
import ImagePopup from '@/components/ImagePopup.vue'
import HeroTalentData from '@/components/HeroTalentData.vue'

export default {
	props: ['dataurl'],
	components :{
		ImagePopup,
		HeroTalentData
	},
	data() {
		return{
			tabledata: [],
			tableitems: [{hero_name:"Abathur" }],
			tablefields: [],
			sortby: 'win_rate',
			fields: [
				{ key: 'name', label: 'Hero', sortable: true },
				{ key: 'win_rate', label: 'Win Rate', sortable: true, class: "col-win_rate" },
				{ key: 'popularity', label: 'Popularity', sortable: true },
				{ key: 'pick_rate', label: 'Pick Rate', sortable: true },
				{ key: 'ban_rate', label: 'Ban Rate', sortable: true, class: "mobileHide"},
				{ key: 'influence', label: 'Influence', sortable: true, class: "mobileHide"},
				{ key: 'games_played', label: 'Games Played', sortable: true },
				{ key: 'wins', label: 'Wins', sortable: true, class: "mobileHide"},
				{ key: 'losses', label: 'Losses', sortable: true, class: "mobileHide"},
				{ key: 'bans', label: 'Bans', sortable: true, class: "mobileHide"},
				{ key: 'change', label: 'Win Rate Change', sortable: true, class: "mobileHide" },
				{ key: 'talent_builds', label: 'Talent Builds', sortable: false}
			],
			error: "",
			talentsLoaded: false
			
		}
	},
	
	created () {
		
	},
	watch: {
		
	},
	methods: {
		sanitizeParams(param){
			param = param.replace(/_/g, ' ')
			param = decodeURI(param)
			return param
		},
		talentsLoading(){
			
			this.talentsLoaded = true
			
		}
	},
	computed: {
		formData: function(){
			return this.$store.state.formData;
			
		},
		loading: function(){
			return this.$store.state.loading;
		}
	},
	mounted : function () {
		axios.get('/api/heroes')
		.then(response => {
			this.tabledata = response.data
			// TODO STORE DISPATCH
			// this.loading = false
		})
	}
}
</script>

<style scoped>

</style>
