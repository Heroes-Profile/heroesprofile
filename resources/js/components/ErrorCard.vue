<template>
  <!-- The error pages' card: the Town Portal art on the left; the code, heading, text, buttons and anything else on the right.
       The Blade error pages fill the slots; a page whose data failed to load passes the status and gets that error's text. -->
  <div class="bg-surface rounded-panel overflow-hidden grid md:grid-cols-[2fr_3fr]">
    <div class="relative max-md:h-[18rem]">
      <a href="/" :aria-label="$t('Take me home')">
        <img class="w-full h-full object-cover" src="/images/miscellaneous/404.png" alt="Town Portal">
      </a>
      <round-image class="!absolute bottom-3 right-3" size="small" icon="fas fa-info" title="info" popupsize="large">
        <p class="font-normal">{{ $t('Image from the Heroes of the Storm comic Secrets of the Storm.') }}</p>
        <a href="https://static.heroesofthestorm.com/comics/secrets-of-the-storm/en-us-09ab7fb85c.pdf" target="_blank" rel="noopener noreferrer" class="inline-flex items-center mt-2 font-normal link-more">{{ $t('View the comic') }}</a>
      </round-image>
    </div>

    <div class="p-6 md:p-14 flex flex-col gap-6 min-w-0">
      <span class="self-start px-1.5 py-0.5 rounded-[0.25rem] bg-teal/20 text-lteal text-[0.625rem] font-bold uppercase tracking-wider"><slot name="badge">{{ $t('Error :code', { code: status }) }}</slot></span>
      <div class="flex flex-col gap-2">
        <h1 class="text-3xl md:text-[2.5rem] font-bold"><slot name="heading">{{ $t(text.heading) }}</slot></h1>
        <p class="text-subtext md:text-lg"><slot name="text">{{ message || $t(text.body) }}</slot></p>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <slot name="actions">
          <template v-if="kind === 403">
            <custom-button :href="'/'" :text="'Take me home'" :alt="'Take me home'" :color="'teal'" class="font-semibold"></custom-button>
            <custom-button :href="'/Contact'" :text="'Contact us'" :alt="'Contact us'" class="font-semibold"></custom-button>
          </template>
          <template v-else-if="kind === 404">
            <custom-button :href="'/'" :text="'Take me home'" :alt="'Take me home'" :color="'teal'" class="font-semibold"></custom-button>
            <search-component :type="'alt'" class="!mt-0 !py-0 flex-1 min-w-[16rem]"></search-component>
          </template>
          <template v-else>
            <custom-button text="Try again" alt="Try again" color="teal" :ignoreclick="true" class="font-semibold" @click="tryAgain"></custom-button>
            <custom-button :href="'/'" :text="'Take me home'" :alt="'Take me home'" class="font-semibold"></custom-button>
            <a href="https://github.com/Heroes-Profile/heroesprofile/issues" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-sm font-semibold text-lteal link-more">{{ $t(kind === 429 ? 'Keeps happening? Report it' : 'Report a bug') }}</a>
          </template>
        </slot>
      </div>

      <!-- A page can show something else here (403's ban details); otherwise the popular pages -->
      <slot name="extra">
        <div class="border-t border-surface-3 pt-5 flex flex-col gap-3">
          <span class="text-[0.625rem] font-bold uppercase tracking-wider text-subtext">{{ $t('Popular pages') }}</span>
          <div class="grid sm:grid-cols-2 gap-3">
            <a v-for="page in popularPages" :key="page.href" :href="page.href" class="flex items-center gap-4 bg-surface-1 p-3 md:p-4 link-tile">
              <span class="flex flex-col min-w-0">
                <span class="text-sm md:text-base font-semibold link-tile-title">{{ $t(page.title) }}</span>
                <span class="text-xs md:text-sm text-subtext truncate">{{ $t(page.subtitle) }}</span>
              </span>
              <basil-icon name="arrow-small" class="ml-auto text-sm"></basil-icon>
            </a>
          </div>
        </div>
      </slot>
    </div>
  </div>
</template>

<script>
// Each error's heading and text, as on its Blade page; anything else reads as a server error
const TEXT = {
  403: { heading: 'Access Denied', body: 'You do not have permission to view this page.' },
  404: { heading: 'You got lost in the Nexus', body: 'This page doesn\'t exist or was moved. Take the portal home, or find what you were looking for below.' },
  429: { heading: 'Slow down, hero', body: 'You\'re sending requests too quickly. Wait a moment, then try again.' },
  500: { heading: 'Something broke in the Nexus', body: 'Our server ran into a problem loading this page. Try again in a moment. If it keeps happening, report the page and filters you were using.' },
};

export default {
  name: 'ErrorCard',
  props: {
    status: { type: [Number, String], default: 500 },
    // Replaces the error's text, e.g. the server's reason a query was stopped
    message: String,
    // @retry: a page that can load its data again does that; otherwise Try again reloads the page
    onRetry: Function,
  },
  data() {
    return {
      popularPages: [
        { title: 'Hero Tier List', subtitle: 'Win rates for every hero', href: '/Global/Hero' },
        { title: 'Hero Builds', subtitle: 'Popular talent builds', href: '/Global/Talents' },
        { title: 'Leaderboards', subtitle: 'Top players by season', href: '/Global/Leaderboard' },
        { title: 'Drafter', subtitle: 'Draft helper', href: '/Drafter' },
      ],
    };
  },
  computed: {
    kind() {
      const status = Number(this.status);
      return TEXT[status] ? status : 500;
    },
    text() {
      return TEXT[this.kind];
    },
  },
  methods: {
    tryAgain() {
      if (this.onRetry) {
        this.onRetry();
      } else {
        window.location.reload();
      }
    },
  },
};
</script>
