<template>

  <div class="modal-mask ">
      <div class="modal-wrapper ">
        <div class="modal-container bg-blue rounded-lg p-10 max-w-[750px] w-[90%] max-md:text-sm">

          <div class="modal-header">
            <slot name="header">

              In order to view accurate data for your matches, regularly upload via the Heroes Profile Uploader
            </slot>
          </div>  

          <div class="modal-body">
            <slot name="body">

      <ul class="list-disc mx-auto ml-4">
        <li>Automatically uploads replays</li>
        <li>Pre-match Game Analysis</li>
        <li>Post-match Game Analysis</li>
      </ul>
              <img  class="text-center mx-auto mt-4" src="/images/miscellaneous\windowsUploader.PNG" style="width: 500px" />
            </slot>
          </div>

          <div class="modal-footer flex">
            <slot name="footer">
              <button class="bg-teal p-2 rounded-lg ml-auto" @click="closePopup">Close</button>

            </slot>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
export default {
  name: 'NewUserPopup',
  components: {
  },
  props: {
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
    closePopup() {
      localStorage.setItem('newUserPopup', 'true');
      this.$emit('popupclosed');
    },
  }
}
</script>

<style scoped>
  .modal-mask {
    position: fixed;
    z-index: 9998;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    display: table;
    transition: opacity 0.3s ease;
}

.modal-wrapper {
  display: table-cell;
  vertical-align: middle;
}

.modal-container {
  
  margin: 0px auto;
  
  
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
  transition: all 0.3s ease;
  
}

.modal-header h3 {
  margin-top: 0;
  
}

.modal-body {
  margin: 20px 0;
}

.modal-default-button {
  float: right;
}

/*
 * The following styles are auto-applied to elements with
 * transition="modal" when their visibility is toggled
 * by Vue.js.
 *
 * You can easily play with the modal transition by editing
 * these styles.
 */

.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

.modal-enter-active .modal-container,
.modal-leave-active .modal-container {
  -webkit-transform: scale(1.1);
  transform: scale(1.1);
}

</style>