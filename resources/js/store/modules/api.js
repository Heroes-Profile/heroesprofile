export default {
    postHeroUpdate: function (context) {
        axios.post('/get_heroes_stats_table_data', {
            params: {
                'data': context.rootGetters['searchStore/heroFormData']
            }
        }).then(response => {
            context.commit('formData', response.data);
        });
    },
    postHeroUpdateDB: _.wrap(_.memoize(function () {
        return _.debounce(postHeroUpdate, 200);
    }))
}