<template>
  <div class="max-w-[34rem] w-full mx-auto px-4 py-10 md:py-20">
    <div class="bg-surface rounded-panel border-t-4 border-red p-6 md:p-8 flex flex-col items-center gap-5 text-center">
      <span class="w-16 h-16 rounded-full bg-red/20 flex items-center justify-center text-3xl"><i class="fas fa-times"></i></span>
      <div class="flex flex-col gap-2">
        <h1 class="text-2xl md:text-3xl font-bold">{{ $t('Battle.net log in failed') }}</h1>
        <p class="text-subtext">{{ $t('We couldn\'t log you in with your Battle.net account.') }}</p>
      </div>

      <div class="w-full rounded bg-surface-1 p-4 text-left text-sm flex flex-col gap-2">
        <span class="font-semibold">{{ $t('This usually happens when:') }}</span>
        <ul class="flex flex-col gap-1.5 text-subtext">
          <li v-for="reason in reasons" :key="reason" class="flex gap-2"><span class="text-red">•</span>{{ $t(reason) }}</li>
        </ul>
      </div>

      <div class="w-full grid sm:grid-cols-2 gap-3">
        <custom-button :href="'/Authenticate/Battlenet'" :text="'Try again'" :alt="'Try again'" :color="'red'" class="font-semibold"></custom-button>
        <custom-button :href="'/Contact'" :text="'Contact support'" :alt="'Contact support'" :color="'blue'" class="font-semibold"></custom-button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BattlenetAuthenticateFailed',
  data() {
    return {
      reasons: [
        'You closed the Battle.net window or pressed Cancel.',
        'The log in took too long, or was started in another tab or browser.',
        'Your browser blocked cookies for Heroes Profile.',
      ],
    };
  },
};
</script>
