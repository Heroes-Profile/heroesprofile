<template>

  <div class="modal-mask ">
      <div class="modal-wrapper ">
        <div class="modal-container bg-surface-4 rounded-2xl p-10 max-md:p-4 max-w-[944px] w-[90%]">

          <div class="modal-header">
            <slot name="header">
              <h3 class="text-3xl max-md:text-2xl font-bold text-center mb-6">Important: Conditions & Agreement</h3>
              <p>
                In order to view accurate data for your matches, regularly upload via the <a class="underline hover:text-header" href="/Upload" target="_blank">Heroes Profile Uploader</a>.
              </p>
            </slot>
          </div>

          <div class="modal-body">
            <slot name="body">
              <div class="flex flex-col gap-4">
                <div>
                  <p class="font-bold mb-1">Zero Tolerance Policy:</p>
                  <p>
                    You must agree to the following conditions. There will be zero tolerance if users are found to be violating these rules:
                  </p>
                  <ul class="list-disc ml-8 mt-1 space-y-1">
                    <li>Do not cheat or try to game the system</li>
                    <li>Do not only upload wins</li>
                    <li>Do not upload losses through excluded uploaders to try and game the system into excluding it</li>
                    <li>Use automatic uploaders to prevent friends/other players from making your account appear to violate these rules</li>
                    <li>Do not turn the uploader on and off during playtime between matches, as this results in behavior that looks like replay pruning and may cause a review or account to be flagged</li>
                  </ul>
                </div>

                <p>
                  Our zero tolerance process means player accounts will be banned from the site. The data will no longer be viewable or accessible. Violations will result in irreversible action.
                </p>

                <div>
                  <p class="mb-1">Automatic Uploader Recommendation:</p>
                  <p>Keep the uploader running continuously throughout your playtime.</p>
                </div>
              </div>
            </slot>
          </div>

          <!-- Kept out of the scrolling body so it is always on screen -->
          <div class="modal-footer flex flex-col gap-6">
            <div class="flex items-start gap-3">
              <input
                type="checkbox"
                id="agreeConditions"
                v-model="agreedToConditions"
                class="mt-0.5 cursor-pointer w-6 h-6 shrink-0 accent-teal"
              />
              <label for="agreeConditions" class="cursor-pointer">
                I agree to the conditions above and understand that violations will result in zero tolerance enforcement.
              </label>
            </div>
            <slot name="footer">
              <custom-button class="ml-auto" color="teal" text="I agree & Close" :disabled="!agreedToConditions" :ignoreclick="true" @click="closePopup"></custom-button>
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
    position: fixed;
    z-index: 9998;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    /* Phones: the visible height, not the height behind the address bar */
    height: 100dvh;
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
  max-height: calc(100dvh - 40px);
  display: flex;
  flex-direction: column;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
  transition: all 0.3s ease;
}

.modal-header h3 {
  margin-top: 0;
  
}

/* Only the rules scroll; header and footer stay put */
.modal-body {
  margin: 20px 0;
  overflow-y: auto;
  flex: 1 1 auto;
  min-height: 0;
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