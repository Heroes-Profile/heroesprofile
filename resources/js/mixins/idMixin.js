export default {
	data : function () {
			return {
				id : ''
			}
	},
	computed : {
		selector : function () {
			return '#'+this.id;
		}
	},
	mounted : function () {
		this.id = 'a'+guid();
	}
}