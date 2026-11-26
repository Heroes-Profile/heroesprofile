<template>
  <div class="max-w-[34rem] w-full mx-auto px-4 py-10 md:py-20">
    <div class="bg-surface rounded-panel border-t-4 border-red p-6 md:p-8 flex flex-col items-center gap-5 text-center">
      <span class="w-16 h-16 rounded-full bg-red/20 flex items-center justify-center text-3xl"><i class="fas fa-times"></i></span>
      <div class="flex flex-col gap-2">
        <h1 class="text-2xl md:text-3xl font-bold">{{ $t('Patreon log in failed') }}</h1>
        <p class="text-subtext">{{ $t('We couldn\'t connect your Patreon membership to Heroes Profile. Nothing was charged.') }}</p>
      </div>

      <div class="w-full rounded bg-surface-1 p-4 text-left text-sm flex flex-col gap-2">
        <span class="font-semibold">{{ $t('This usually happens when:') }}</span>
        <ul class="flex flex-col gap-1.5 text-subtext">
          <li v-for="reason in reasons" :key="reason" class="flex gap-2"><span class="text-red">•</span>{{ $t(reason) }}</li>
        </ul>
      </div>

      <div class="w-full grid sm:grid-cols-2 gap-3">
        <custom-button :href="'/authenticate/patreon'" :text="'Try again'" :alt="'Try again'" :color="'red'" class="font-semibold"></custom-button>
        <custom-button :href="'/Contact'" :text="'Contact support'" :alt="'Contact support'" :color="'blue'" class="font-semibold"></custom-button>
      </div>

      <span class="text-xs text-subtext">
        {{ $t('Not a supporter yet?') }}
        <a href="https://www.patreon.com/heroesprofile" target="_blank" rel="noopener noreferrer" class="group/bt inline-flex items-center text-lteal"><span class="group-hover/bt:underline">{{ $t('Subscribe on Patreon') }}</span><basil-icon name="arrow-small" class="text-xs ml-1"></basil-icon></a>
      </span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PatreonAuthenticateFailed',
  data() {
    return {
      reasons: [
        'You closed the Patreon window or pressed Deny.',
        'The log in took too long, or was started in another tab or browser.',
        'Your membership is brand new: it can take 10–15 minutes for the site to see it.',
        'Your membership\'s payment was declined, or the membership has ended.',
        'You\'re logged in to a different Patreon account than the one you support with.',
      ],
    };
  },
};
</script>
