<template>
  <div>
    <page-heading :heading="'Team'" :subheading="'The people behind Heroes Profile'"></page-heading>

    <!-- A card per person, from config/team.php: name, company, what they handle, when they've been on the team, a short bio -->
    <div class="max-w-[120rem] mx-auto px-4 md:px-8 grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
      <panel-box v-for="member in members" :key="member.name" :divider="false">
        <div class="flex flex-col gap-1">
          <h2 class="text-2xl font-bold">{{ member.name }}</h2>
          <span v-if="member.company" class="text-sm text-subtext">{{ member.company }}</span>
          <span class="font-medium text-teal">{{ $t(member.role) }}</span>
          <span class="text-sm text-subtext">{{ period(member) }}</span>
        </div>
        <!-- Their best Storm League rank, in words, named as the profile names ranks -->
        <div v-if="member.peak_rank" class="flex items-center gap-3 text-sm">
          <span class="text-subtext">{{ $t('Peak rank') }}</span>
          <span class="font-semibold">{{ localRankName(member.peak_rank) }}</span>
        </div>
        <p class="text-sm">{{ $t(member.bio) }}</p>
        <!-- How to reach them: email and Reddit link out; a Discord username can only be shown -->
        <div v-if="member.email || member.reddit || member.discord" class="flex flex-col gap-1.5 text-sm">
          <a v-if="member.email" :href="'mailto:' + member.email" class="group/bt inline-flex items-center gap-2 w-fit"><i class="fas fa-envelope w-4 text-center text-teal"></i><span class="group-hover/bt:underline">{{ member.email }}</span></a>
          <a v-if="member.reddit" :href="'https://www.reddit.com/user/' + member.reddit" target="_blank" rel="noopener noreferrer" class="group/bt inline-flex items-center gap-2 w-fit"><i class="fab fa-reddit-alien w-4 text-center text-teal"></i><span class="group-hover/bt:underline">u/{{ member.reddit }}</span></a>
          <!-- Discord: with their user ID the name opens their profile; without one it copies the username for Add Friend -->
          <a v-if="member.discord && member.discord_id" :href="'https://discord.com/users/' + member.discord_id" target="_blank" rel="noopener noreferrer" class="group/bt inline-flex items-center gap-2 w-fit" :title="$t('Discord')"><i class="fab fa-discord w-4 text-center text-teal"></i><span class="group-hover/bt:underline">{{ member.discord }}</span></a>
          <button v-else-if="member.discord" type="button" class="group/bt inline-flex items-center gap-2 w-fit" :title="copiedDiscord === member.discord ? $t('Copied') : $t('Copy Discord username')" @click="copyDiscord(member.discord)"><i class="fab fa-discord w-4 text-center text-teal"></i><span class="group-hover/bt:underline">{{ member.discord }}</span><i :class="[copiedDiscord === member.discord ? 'fas fa-check text-lteal' : 'fas fa-copy text-subtext group-hover/bt:text-lteal', 'text-xs transition-colors']"></i></button>
        </div>
      </panel-box>
    </div>

    <!-- People who helped with a one-off piece of work: lighter cards, what they did and when -->
    <div v-if="contributors.length" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-10 flex flex-col gap-4">
      <div>
        <h2 class="text-2xl font-bold">{{ $t('Contributors') }}</h2>
        <p class="text-sm text-subtext mt-1">{{ $t('People who helped with a part of Heroes Profile.') }}</p>
      </div>
      <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
        <div v-for="contributor in contributors" :key="contributor.name" class="rounded bg-surface-1 px-5 py-4 flex flex-col gap-1">
          <h3 class="text-lg font-bold">{{ contributor.name }}</h3>
          <!-- The chevron is held to the last word, so it never wraps onto a line of its own -->
          <a v-if="contributor.link" :href="contributor.link" target="_blank" rel="noopener" class="group/bt font-medium text-teal"><span class="group-hover/bt:underline">{{ leadWords($t(contributor.contribution)) }}</span><span class="whitespace-nowrap"><span class="group-hover/bt:underline">{{ lastWord($t(contributor.contribution)) }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span></a>
          <span v-else class="font-medium text-teal">{{ $t(contributor.contribution) }}</span>
          <span class="text-sm text-subtext">{{ period(contributor) }}</span>
          <!-- Discord: with their user ID the name opens their profile; without one it copies the username for Add Friend -->
          <a v-if="contributor.discord && contributor.discord_id" :href="'https://discord.com/users/' + contributor.discord_id" target="_blank" rel="noopener noreferrer" class="group/bt inline-flex items-center gap-2 w-fit text-sm mt-2" :title="$t('Discord')"><i class="fab fa-discord w-4 text-center text-teal"></i><span class="group-hover/bt:underline">{{ contributor.discord }}</span></a>
          <button v-else-if="contributor.discord" type="button" class="group/bt inline-flex items-center gap-2 w-fit text-sm mt-2" :title="copiedDiscord === contributor.discord ? $t('Copied') : $t('Copy Discord username')" @click="copyDiscord(contributor.discord)"><i class="fab fa-discord w-4 text-center text-teal"></i><span class="group-hover/bt:underline">{{ contributor.discord }}</span><i :class="[copiedDiscord === contributor.discord ? 'fas fa-check text-lteal' : 'fas fa-copy text-subtext group-hover/bt:text-lteal', 'text-xs transition-colors']"></i></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { localRankName } from './RealRankBadge.vue';

export default {
  name: 'TeamPage',
  props: {
    members: { type: Array, required: true },
    contributors: { type: Array, default: () => [] },
  },
  data() {
    return {
      // The Discord username just copied, for its tick
      copiedDiscord: null,
    };
  },
  methods: {
    localRankName,
    async copyDiscord(username) {
      try {
        await navigator.clipboard.writeText(username);
      } catch (error) {
        // The browser refused the clipboard
        return;
      }
      this.copiedDiscord = username;
      clearTimeout(this.copyTimer);
      this.copyTimer = setTimeout(() => { this.copiedDiscord = null; }, 2000);
    },
    // A line split before its last word ("… Heroes Profile " and "team")
    leadWords(text) {
      const index = text.lastIndexOf(' ');
      return index === -1 ? '' : text.slice(0, index + 1);
    },
    lastWord(text) {
      return text.slice(text.lastIndexOf(' ') + 1);
    },
    // "June 2018 – Present", "October – December 2026" or "October 2026", the months in the page's language
    period(member) {
      if (member.end === member.start) {
        return this.month(member.start);
      }
      if (member.end && member.end.slice(0, 4) === member.start.slice(0, 4)) {
        const [year, month] = member.start.split('-').map(Number);
        const startMonth = new Intl.DateTimeFormat(document.documentElement.lang || 'en', { month: 'long', timeZone: 'UTC' }).format(new Date(Date.UTC(year, month - 1, 1)));
        return startMonth + ' – ' + this.month(member.end);
      }
      return this.month(member.start) + ' – ' + (member.end ? this.month(member.end) : this.$t('Present'));
    },
    month(value) {
      const [year, month] = String(value).split('-').map(Number);
      return new Intl.DateTimeFormat(document.documentElement.lang || 'en', { month: 'long', year: 'numeric', timeZone: 'UTC' }).format(new Date(Date.UTC(year, month - 1, 1)));
    },
  },
}
</script>
