<template>
  <modal-box title="Important: Conditions & Agreement">
    <template #header>
      <p>
        In order to view accurate data for your matches, regularly upload via the <a class="underline hover:text-header" href="/Upload" target="_blank">Heroes Profile Uploader</a>.
      </p>
    </template>

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

    <template #footer>
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
      <custom-button class="ml-auto" color="teal" text="I agree & Close" :disabled="!agreedToConditions" :ignoreclick="true" @click="closePopup"></custom-button>
    </template>
  </modal-box>
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
