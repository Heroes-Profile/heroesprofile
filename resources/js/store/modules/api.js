export default {
    postHeroUpdate: _.debounce(function (context) {
        axios.post('/get_heroes_stats_table_data', {
            params: {
                'data': context.rootGetters['searchStore/heroFormData']
            }
        }).then(response => {
            context.commit('formData', response.data);
        });
    }, 300)
}