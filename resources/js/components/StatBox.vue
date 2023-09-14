<template>
  <div class="bg-blue p-4">
    <div class="text-center font-bold text-white mb-2">
      {{ title }}
    </div>
    <div class="text-center text-white border border-gray-400 p-2 rounded">
      {{ value }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'StatBox',
  components: {
  },
  props: {
    title: String,
    value: {
      type: [String, Number]
    },
  },
  data(){
    return {
    }
  },
  created(){
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
  }
}
</script>