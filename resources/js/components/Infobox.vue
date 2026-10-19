<template>
  <div>
    <p class="text-sm max-md:text-sm"><slot>{{ this.input }}</slot></p>
  </div>
</template>

<script>
export default {
  name: 'Infobox',
  components: {
  },
  props: {
    input: String,

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