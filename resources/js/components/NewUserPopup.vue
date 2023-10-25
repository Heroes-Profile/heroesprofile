<template>

  <div class="modal-mask">
      <div class="modal-wrapper">
        <div class="modal-container">

          <div class="modal-header">
            <slot name="header">

              In order to view accurate data for your matches, regularly upload via the Heroes Profile Uploader
            </slot>
          </div>  

          <div class="modal-body">
            <slot name="body">

      *Automatically uploads replays
      *Pre-match Game Analysis
      *Post-match Game Analysis
              <img src="/images/miscellaneous\windowsUploader.PNG" style="width: 500px" />
            </slot>
          </div>

          <div class="modal-footer">
            <slot name="footer">
              <button @click="closePopup">Close</button>

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
      //localStorage.setItem('newUserPopup', 'true');
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
  width: 1000px;
  margin: 0px auto;
  padding: 20px 30px;
  background-color: #213d7a;
  border-radius: 2px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
  transition: all 0.3s ease;
  font-family: Helvetica, Arial, sans-serif;
}

.modal-header h3 {
  margin-top: 0;
  color: #42b983;
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