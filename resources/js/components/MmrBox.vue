<template>
  <div :class="winnerClass">
    <div class="grid grid-cols-2">
      <div class="col-span-2 p-4 text-center font-bold text-blue bg-white">
        {{ title }} MMR | Change
      </div>
      <div class="col-span-1 p-4 border border-gray-400">{{ mmr }}</div>
      <div class="col-span-1 p-4 border border-gray-400">{{ formattedChange }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MmrBox',
  components: {
  },
  props: {
    winner: {
      type: Number,
      required: true,
    },
    title: String,
    mmr: Number,
    change: Number,
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
   winnerClass() {
      return this.winner === 1 ? 'bg-blue' : 'bg-red';
    },
    formattedChange() {
      return this.change > 0 ? `+${this.change}` : this.change;
    },
  },
  watch: {
  },
  methods: {
  }
}
</script>