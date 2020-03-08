<template>
    <div>
        <data-table></data-table>
		<search-form></search-form>
    </div>
</template>

<script>
import DataTable from '@/components/DataTable.vue';
import SearchForm from '@/components/SearchForm.vue';

export default {
    components: {
		DataTable,
		SearchForm
	}
}
</script>
