<template>
  <div>
    <page-heading :heading="'Team'" :subheading="'The people behind Heroes Profile'"></page-heading>

    <!-- A card per person, from config/team.php, as the Figma Team frame: initial, name and company with the role pill on the
         right, when they've been on the team and their peak rank side by side, the bio, then how to reach them as chips -->
    <div class="max-w-[120rem] mx-auto px-4 md:px-8 grid md:grid-cols-2 gap-6">
      <panel-box v-for="member in members" :key="member.name" :divider="false">
        <div class="flex items-start gap-4">
          <span class="w-14 h-14 shrink-0 rounded-full bg-teal flex items-center justify-center text-2xl font-bold">{{ member.name.charAt(0) }}</span>
          <div class="flex-1 min-w-0 flex flex-col gap-1">
            <h2 class="text-2xl font-bold leading-tight">{{ member.name }}</h2>
            <span v-if="member.company" class="text-sm text-subtext">{{ member.company }}</span>
          </div>
          <span class="shrink-0 rounded-lg bg-teal px-2.5 py-1 text-xs font-bold">{{ $t(member.role) }}</span>
        </div>
        <div class="grid sm:grid-cols-2 gap-3">
          <div class="rounded-control bg-surface-1 px-4 py-3 flex flex-col gap-1.5">
            <span class="text-xs text-subtext">{{ $t('On the team') }}</span>
            <span class="font-semibold">{{ period(member) }}</span>
          </div>
          <!-- Their best Storm League rank, in the rank tier colours -->
          <div v-if="member.peak_rank" class="rounded-control bg-surface-1 px-4 py-3 flex flex-col gap-1.5 items-start">
            <span class="text-xs text-subtext">{{ $t('Peak rank') }}</span>
            <rank-tier-pill :tier="peakTier(member.peak_rank)" align="left" class="!min-w-[9rem] !w-auto !py-1 text-sm"></rank-tier-pill>
          </div>
        </div>
        <p class="text-sm leading-relaxed">{{ $t(member.bio) }}</p>
        <!-- Their player page, then how to reach them: email and Reddit link out; Discord opens their profile with an ID,
             otherwise copies the username -->
        <div v-if="member.profile || member.email || member.reddit || member.discord" class="flex flex-wrap gap-2 text-sm mt-auto">
          <a v-if="member.profile" :href="member.profile" :class="chipClass"><img src="/images/logo/heroesprofilelogo.png" alt="" class="h-4 w-auto" /><span class="group-hover/bt:underline">{{ member.name }}</span></a>
          <a v-if="member.email" :href="'mailto:' + member.email" :class="chipClass"><i class="fas fa-envelope text-teal"></i><span class="group-hover/bt:underline">{{ member.email }}</span></a>
          <a v-if="member.reddit" :href="'https://www.reddit.com/user/' + member.reddit" target="_blank" rel="noopener noreferrer" :class="chipClass"><i class="fab fa-reddit-alien text-teal"></i><span class="group-hover/bt:underline">u/{{ member.reddit }}</span></a>
          <a v-if="member.discord && member.discord_id" :href="'https://discord.com/users/' + member.discord_id" target="_blank" rel="noopener noreferrer" :class="chipClass" :title="$t('Discord')"><i class="fab fa-discord text-teal"></i><span class="group-hover/bt:underline">{{ member.discord }}</span></a>
          <button v-else-if="member.discord" type="button" :class="chipClass" :title="copiedDiscord === member.discord ? $t('Copied') : $t('Copy Discord username')" @click="copyDiscord(member.discord)"><i class="fab fa-discord text-teal"></i><span class="group-hover/bt:underline">{{ member.discord }}</span><i :class="[copiedDiscord === member.discord ? 'fas fa-check text-lteal' : 'fas fa-copy text-subtext group-hover/bt:text-lteal', 'text-xs transition-colors']"></i></button>
        </div>
      </panel-box>
    </div>

    <!-- People who helped with a one-off piece of work: a row each in one card, what they did, when, and their Discord -->
    <div v-if="contributors.length" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-6">
      <panel-box title="Contributors" subtitle="People who helped with a part of Heroes Profile." :divider="false">
        <!-- As many cards across as fit, six on a wide screen -->
        <div class="grid grid-cols-[repeat(auto-fill,minmax(17rem,1fr))] gap-3">
          <div v-for="contributor in contributors" :key="contributor.name" :class="['rounded-control bg-surface-1 px-4 py-3 flex flex-col gap-3', { 'border-2 border-teal': contributor.featured }]">
            <div class="flex items-center gap-3">
              <span :class="['shrink-0 rounded-full bg-teal flex items-center justify-center font-bold', contributor.featured ? 'w-12 h-12 text-xl' : 'w-10 h-10 text-lg']">{{ contributor.name.charAt(0) }}</span>
              <div class="flex-1 min-w-0 flex flex-col">
                <h3 :class="['font-bold flex items-center gap-2', { 'text-lg': contributor.featured }]">{{ contributor.name }}
                  <icon-with-hover v-if="contributor.featured" class="text-teal text-base" size="small" icon="fas fa-award" title="info" popupsize="fit">
                    <p class="max-sm:text-xs whitespace-nowrap">{{ $t('Significant contributor') }}</p>
                  </icon-with-hover>
                </h3>
                <!-- The chevron is held to the last word, so it never wraps onto a line of its own -->
                <a v-if="contributor.link" :href="contributor.link" target="_blank" rel="noopener" class="group/bt text-sm text-teal"><span class="group-hover/bt:underline">{{ leadWords($t(contributor.contribution)) }}</span><span class="whitespace-nowrap"><span class="group-hover/bt:underline">{{ lastWord($t(contributor.contribution)) }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span></a>
                <span v-else class="text-sm text-teal">{{ $t(contributor.contribution) }}</span>
              </div>
            </div>
            <div class="mt-auto flex flex-wrap items-center gap-2">
              <span class="mr-auto text-sm text-subtext">{{ period(contributor) }}</span>
              <a v-if="contributor.github" :href="'https://github.com/' + contributor.github" target="_blank" rel="noopener noreferrer" :class="[chipClass, 'bg-surface-3 text-sm']" title="GitHub"><i class="fab fa-github text-teal"></i><span class="group-hover/bt:underline">{{ contributor.github }}</span></a>
              <a v-if="contributor.discord && contributor.discord_id" :href="'https://discord.com/users/' + contributor.discord_id" target="_blank" rel="noopener noreferrer" :class="[chipClass, 'bg-surface-3 text-sm']" :title="$t('Discord')"><i class="fab fa-discord text-teal"></i><span class="group-hover/bt:underline">{{ contributor.discord }}</span></a>
              <button v-else-if="contributor.discord" type="button" :class="[chipClass, 'bg-surface-3 text-sm']" :title="copiedDiscord === contributor.discord ? $t('Copied') : $t('Copy Discord username')" @click="copyDiscord(contributor.discord)"><i class="fab fa-discord text-teal"></i><span class="group-hover/bt:underline">{{ contributor.discord }}</span><i :class="[copiedDiscord === contributor.discord ? 'fas fa-check text-lteal' : 'fas fa-copy text-subtext group-hover/bt:text-lteal', 'text-xs transition-colors']"></i></button>
            </div>
          </div>
        </div>
      </panel-box>
    </div>

    <!-- People whose GitHub issues reported real bugs or ideas: a chip each with their count, linking to their issues -->
    <div v-if="bugReporters.length" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-6">
      <panel-box title="Bug reports & ideas" subtitle="People who reported bugs and suggested features on GitHub." :divider="false">
        <template #right>
          <search-input v-model="reporterSearch" class="w-full sm:w-[20rem]" :placeholder="$t('Search')"></search-input>
        </template>
        <!-- Chips all one size, as many across as fit; the border is always there so the highlighted one isn't taller -->
        <div class="grid grid-cols-[repeat(auto-fill,minmax(13rem,1fr))] gap-2 text-sm">
          <span v-for="reporter in shownReporters" :key="reporter.github" :class="['flex items-center gap-2 rounded-lg bg-surface-1 border-2 px-3 py-1.5 min-w-0', reporter.featured ? 'border-teal' : 'border-transparent']">
            <a :href="'https://github.com/Heroes-Profile/heroesprofile/issues?q=is%3Aissue+author%3A' + reporter.github" target="_blank" rel="noopener noreferrer" class="group/bt min-w-0 flex items-center gap-2">
              <i class="fab fa-github text-teal"></i><span class="truncate group-hover/bt:underline" :title="reporter.github">{{ reporter.github }}</span>
            </a>
            <icon-with-hover v-if="reporter.featured" class="text-teal shrink-0" size="small" icon="fas fa-award" title="info" popupsize="fit">
              <p class="max-sm:text-xs whitespace-nowrap">{{ $t('Significant contributor') }}</p>
            </icon-with-hover>
            <span class="ml-auto shrink-0 text-subtext tabular-nums">{{ reporter.count }}</span>
          </span>
        </div>
        <p v-if="!shownReporters.length" class="text-sm text-subtext">{{ $t('No results found') }}</p>
      </panel-box>
    </div>
  </div>
</template>

<script>
import { rankName } from './RealRankBadge.vue';

export default {
  name: 'TeamPage',
  props: {
    members: { type: Array, required: true },
    contributors: { type: Array, default: () => [] },
    bugReporters: { type: Array, default: () => [] },
  },
  data() {
    return {
      // The Discord username just copied, for its tick
      copiedDiscord: null,
      reporterSearch: '',
    };
  },
  computed: {
    shownReporters() {
      const search = this.reporterSearch.trim().toLowerCase();
      return search ? this.bugReporters.filter(reporter => reporter.github.toLowerCase().includes(search)) : this.bugReporters;
    },
    // A contact chip: email, Reddit or Discord
    chipClass() {
      return 'group/bt inline-flex items-center gap-2 rounded-lg bg-surface-1 px-3 py-1.5 w-fit';
    },
  },
  methods: {
    // The rank pill's tier name for a peak rank ({ league: 'master' } becomes 'Master'), which it colours and translates
    peakTier(rank) {
      const name = rankName(rank, text => text);
      return name || 'Unranked';
    },
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
