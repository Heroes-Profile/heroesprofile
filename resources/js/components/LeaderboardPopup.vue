<template>

  <div class="modal-mask ">
      <div class="modal-wrapper ">
        <div class="modal-container bg-blue rounded-lg p-10 max-w-[750px] w-[90%] text-sm">

          <div class="modal-header">
            <slot name="header">
              <h3 class="text-lg font-bold mb-4">Important: Conditions & Agreement</h3>
              <p class="mb-4">
                In order to view accurate data for your matches, regularly upload via the <a class="link" href="https://api.heroesprofile.com/upload" target="_blank">Heroes Profile Uploader</a>.
              </p>
            </slot>
          </div>  

          <div class="modal-body">
            <slot name="body">
              <div class="mb-4">
                <p class="font-semibold text-red-300 mb-2">Zero Tolerance Policy:</p>
                <p class="mb-3">
                  You must agree to the following conditions. There will be <strong>zero tolerance</strong> if users are found to be violating these rules:
                </p>
                <ul class="list-disc mx-auto ml-4 mb-4 space-y-2">
                  <li>Do not cheat or try to game the system</li>
                  <li>Do not only upload wins</li>
                  <li>Do not upload losses through excluded uploaders to try and game the system into excluding it</li>
                  <li>Use automatic uploaders to prevent friends/other players from making your account appear to violate these rules</li>
                  <li>Do not turn the uploader on and off, as this results in behavior that looks like replay pruning and may cause a review or account to be flagged</li>
                </ul>
                
                <p class="text-sm italic mb-4">
                  Our zero tolerance process means player accounts will be banned from the site.  The data will no longer be viewable or accessible. Violations will result in irreversible action.
                </p>
                
                <div class="mb-4">
                  <p class="font-semibold mb-2">Automatic Uploader Recommendation:</p>
                  <p class="mb-3">
                    Keep the uploader running continuously throughout your playtime.
                  </p>
                </div>
              </div>
              
              <div class="mt-6 flex items-start">
                <input 
                  type="checkbox" 
                  id="agreeConditions" 
                  v-model="agreedToConditions"
                  class="mt-1 mr-2 cursor-pointer"
                />
                <label for="agreeConditions" class="cursor-pointer">
                  I agree to the conditions above and understand that violations will result in zero tolerance enforcement.
                </label>
              </div>
            </slot>
          </div>

          <div class="modal-footer flex">
            <slot name="footer">
              <button 
                class="bg-teal p-2 rounded-lg ml-auto disabled:opacity-50 disabled:cursor-not-allowed" 
                @click="closePopup"
                :disabled="!agreedToConditions"
              >
                I Agree & Close
              </button>

            </slot>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
export default {
  name: 'LeaderboardPopup',
  components: {
  },
  props: {
  },
  data(){
    return {
      agreedToConditions: false
    }
  },
  created(){
  },
  mounted() {
    // Prevent body scroll when modal is open
    document.body.style.overflow = 'hidden';
  },
  beforeUnmount() {
    // Restore body scroll when modal is closed
    document.body.style.overflow = '';
  },
  computed: {
  },
  watch: {
  },
  methods: {
    closePopup() {
      if (!this.agreedToConditions) {
        return;
      }
      localStorage.setItem('leaderboardpopup', 'true');
      localStorage.setItem('leaderboardpopupAgreed', 'true');
      this.$emit('popupclosed');
    },
  }
}
</script>

<style scoped>
  .modal-mask {
    position: absolute;
    z-index: 9998;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.3s ease;
    overflow-y: auto;
  }

.modal-wrapper {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  min-height: 100%;
}

.modal-container {
  margin: 0px auto;
  max-height: calc(100vh - 40px);
  overflow-y: auto;
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