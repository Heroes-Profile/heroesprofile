<template>
  <div>
    <div class="w-[170px] inline-block m-1 bg-gray-light rounded-2xl">
      <h2 class="bg-blue rounded-t-2xl p-2 text-sm text-center uppercase relative">Level {{ level }} 
        <div @click="removeAnySelections" class="absolute right-0 top-0 text-bold p-2 rounded hover:bg-teal">
          <i class="fa-solid fa-xmark"></i>
        </div>
      </h2>

      <div v-for="(talent, index) in data" :key="talent.title" >
        <talent-builder-click-box
          :talent="talent"
          :isClicked="isSelected(talent)"
          @click="talentClicked(talent, index, level)"
        ></talent-builder-click-box>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TalentBuilderColumn',
  components: {
  },
  props: {
    level: Number,
    data: Object,
    clickedData: Object,
  },
  data(){
    return {
      selectedTalent: null, // Track the currently selected talent
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
    talentClicked(talent, index, level){
      this.selectedTalent = talent;
      this.$parent.talentClicked(talent, index, level);
    },
    isSelected(talent) {
      //return this.selectedTalent === talent;
      return this.clickedData[this.level] === talent.talent_id;
    },
    removeAnySelections(){
      this.$parent.removeLevelSelections(this.level);
    },
  }
}
</script>