<template>
  <div>
    <div id="filter-label" class="relative">
      <div @click="showOptions = !showOptions" class="block text-sm font-medium text-gray-700 cursor-pointer border p-2 rounded">
        <span>{{ this.text }}</span>
        <span v-if="selectedOptions.length > 0">: {{ selectedOptionsName.join(', ') }}</span>
      </div>
      <div v-if="showOptions" class="absolute left-0 mt-2 w-full bg-white border border-gray-300 rounded shadow-lg">
        <div class="space-y-2 p-2">
          <div v-for="value in values" :key="value.code">
            <input 
              type="checkbox" 
              :id="value.code" 
              :value="value.code" 
              v-model="selectedOptions"
              class="form-checkbox h-5 w-5 text-indigo-600"
            >
            <label :for="value.code" class="ml-2 text-sm text-gray-900">{{ value.name }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MultiSelectFilter',
  components: {
  },
  props: {
    values: Array,
    text: String,
    defaultValue: Array,
  },
  data(){
    return {
      showOptions: false,
      selectedOptions: this.defaultValue || [],
    }
  },
  created(){
    console.log("value = " + this.defaultValue);
  },
  mounted() {
  },
  computed: {
    selectedOptionsName() {
      return this.selectedOptions.map(code => {
        const value = this.values.find(v => v.code === code);
        return value ? value.name : '';
      });
    },
  },
  watch: {
  },
  methods: {
  }
}
</script>