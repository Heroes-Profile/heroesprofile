<template>
  <div class="max-w-[1500px] mx-auto p-4">
    <h1 class="text-3xl font-bold mb-4 text-center">Site Temporarily Disabled</h1>
     <p class="mb-4">
      Heroes Profile is a community-funded site that relies on the support of its users to pay for server costs.
      We are currently operating at a loss and need your help to keep the site running. We've temporarily disabled the site for free users to reduce costs and encourage community support.
    </p>
    <hr class="pb-4"/>
    <h2 class="text-center text-[1.5em] pb-4">To view the site while it is temporarily down, please do one of the following:</h2>
    <div class="flex flex-col md:flex-row gap-4 mb-4 justify-center items-stretch" >
      <div class="md:w-[50%]">
       Patreon subscribers may bypass this page and access the site directly. Please log in to your account
      and authenticate with Patreon to gain access.
      <custom-button href="/Authenticate/Battlenet" text="Login with Existing Patreon Donation" alt="Login with Battle.net" size="big" class=" mt-[2em]"></custom-button>
      </div>
      <div class="md:hidden   bg-white w-full h-[2px] "></div>
      <div class="md:w-[50%]">
      Please consider supporting the site by donating on Patreon. Your support helps us cover server costs and keep the site running. Once you have donated, you may log in to view the site.
      <custom-button href="https://www.patreon.com/c/heroesprofile" text="Donate on Patreon" alt="Donate on Patreon" size="big"  color="red" class="mb-4 mt-[2em]"></custom-button>
       <p class="mb-4 text-sm italic text-center mt-2">
      For new subscribers, it may take 10-15 minutes for the site to recognize your subscription.
    </p>
      </div>
    </div>
   <div class="max-w-[900px] mx-auto">
    
    <p class="mb-4">
      Our funding goal is $700 per month, which covers server costs.
      As of now, we have raised ${{ patreonearnings }} per month through Patreon subscriptions.
    </p>
  

    <!-- Progress Bar -->
    <div class="progress-container">
      <div class="progress-bar" :style="{ width: progressPercent + '%' }"></div>
    </div>
    <p class="mx-auto text-center text-sm italic">Raised: ${{ patreonearnings }} of $700</p>
  </div>
  </div>
</template>

<script>
export default {
  name: 'CommunitySupport',
  props: {
    patreonearnings: {
      type: Number,
      required: true,
    },
  },
  computed: {
    progressPercent() {
      const goal = 700;
      const percent = (this.patreonearnings / goal) * 100;
      return Math.min(percent, 100).toFixed(2);
    },
  },
}
</script>

<style scoped>
.progress-container {
  width: 80%;
  margin: 1em auto;
  background-color: #eee;
  border-radius: 8px;
  height: 3em;
 
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background-color: #4caf50;
  transition: width 0.5s ease-in-out;
}
</style>
