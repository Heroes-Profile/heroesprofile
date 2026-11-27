<template>
  <div>
    <page-heading heading="Change Log"></page-heading>
    <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8 -mt-2 mb-4">
      <span class="uppercase text-gray-medium">{{ $t('Latest commits to Heroes Profile') }}</span>
    </div>

    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <!-- Phones show one branch at a time; wider screens show both side by side -->
    <div class="md:hidden max-w-[120rem] w-full mx-auto px-4 mb-4 flex justify-center">
      <tab-button :tabs="branches.map(branch => ({ key: branch.name, text: branch.title }))" :ignoreclick="true" :overridedefaultside="mobileBranch" @tab-click="mobileBranch = $event"></tab-button>
    </div>

    <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8 grid md:grid-cols-2 gap-6 md:gap-8 items-start">
      <div v-for="branch in branches" :key="branch.name" :class="['bg-surface rounded-panel p-4 md:p-8 flex flex-col gap-5 min-w-0', { 'max-md:hidden': branch.name !== mobileBranch }]">
        <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-2">
          <div>
            <div class="flex items-center gap-3">
              <h2 class="text-2xl font-bold">{{ $t(branch.title) }}</h2>
              <span :class="['text-xs font-bold rounded-lg px-2.5 py-1 text-white', branch.chip]">{{ branch.name }}</span>
            </div>
            <p class="text-sm text-subtext mt-1.5">{{ $t(branch.description) }}</p>
          </div>
          <a v-if="repoUrl" class="inline-flex items-center text-sm link-more" :href="`${repoUrl}/commits/${branch.name}`" target="_blank" rel="noopener">{{ $t('View on GitHub') }}</a>
        </div>
        <div class="h-px bg-gray-dark"></div>

        <p v-if="branch.commits === null" class="text-sm text-subtext">{{ $t("Couldn't load commits from GitHub. Try again in a few minutes.") }}</p>

        <!-- Commits grouped by day; click one to read its full message -->
        <template v-else>
        <div v-for="day in groupByDay(branch.commits)" :key="day.label" class="flex flex-col gap-3">
          <span class="text-xs font-bold uppercase text-subtext">{{ day.label }}</span>
          <div class="flex flex-col gap-px rounded-lg overflow-hidden">
            <div v-for="commit in day.commits" :key="commit.sha" :class="['bg-surface-1 transition-[background-color]', open[branch.name + commit.sha] ? 'bg-surface-2 border-l-4 border-teal' : 'hover:bg-surface-2']">
              <button type="button" class="w-full flex items-center gap-4 px-5 py-4 text-left" :aria-expanded="!!open[branch.name + commit.sha]" @click="open[branch.name + commit.sha] = !open[branch.name + commit.sha]">
                <span class="w-9 h-9 rounded-full bg-teal text-white font-bold flex items-center justify-center shrink-0">{{ initial(commit) }}</span>
                <span class="flex-1 min-w-0">
                  <span :class="['block font-bold leading-snug', { 'line-clamp-2': !open[branch.name + commit.sha] }]">{{ title(commit) }}</span>
                  <span class="block text-xs text-subtext mt-1.5">{{ author(commit) }} · {{ commit.short_sha }} · {{ formatTime(commit.date) }}</span>
                </span>
                <basil-icon name="arrow-small" :class="['text-sm shrink-0 transition-transform', open[branch.name + commit.sha] ? '-rotate-90' : 'rotate-90']"></basil-icon>
              </button>

              <transition name="drop">
              <div v-if="open[branch.name + commit.sha]" class="pl-[4.75rem] pr-5 pb-5 flex flex-col gap-3 text-sm">
                <template v-for="(block, index) in body(commit)" :key="index">
                  <ul v-if="block.list" class="flex flex-col gap-2">
                    <li v-for="(item, itemIndex) in block.list" :key="itemIndex" class="flex gap-3"><span class="w-1.5 h-1.5 rounded-full bg-teal shrink-0 mt-2"></span><span class="text-subtext">{{ item }}</span></li>
                  </ul>
                  <p v-else class="text-subtext">{{ block.text }}</p>
                </template>
                <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                  <span class="flex flex-wrap items-center gap-3">
                    <span class="bg-surface-3 rounded-lg px-3 py-1 text-xs">{{ commit.short_sha }}</span>
                    <span v-if="commit.additions != null" class="flex items-center gap-2 tabular-nums">
                      <span class="text-win">+{{ commit.additions.toLocaleString('en-US') }}</span>
                      <span class="text-loss">−{{ commit.deletions.toLocaleString('en-US') }}</span>
                      <span class="text-subtext">· {{ $t(commit.files === 1 ? '1 file' : ':count files', { count: commit.files }) }}</span>
                    </span>
                  </span>
                  <a class="inline-flex items-center text-lteal link-more" :href="commit.url" target="_blank" rel="noopener">{{ $t('Open commit on GitHub') }}</a>
                </div>
              </div>
              </transition>
            </div>
          </div>
        </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import { formatDate as formatLocalDate } from '../utils/formatGameDate';

export default {
  name: 'GithubChanges',
  props: {
    patreonUser: Boolean,
    masterCommits: { type: Array, default: null },
    developCommits: { type: Array, default: null },
  },
  data() {
    return {
      // By branch and commit sha: whether its full message is showing, so a commit on both branches opens in one card only
      open: {},
      // The branch a phone shows
      mobileBranch: 'master',
    };
  },
  computed: {
    branches() {
      return [
        { name: 'develop', title: 'Develop branch', description: 'Work in progress, deployed to the test site.', chip: 'bg-teal', commits: this.developCommits },
        { name: 'master', title: 'Master branch', description: 'Released to heroesprofile.com.', chip: 'bg-blue', commits: this.masterCommits },
      ];
    },
    // The repository's GitHub address, taken from a commit's link
    repoUrl() {
      const commit = (this.developCommits || [])[0] || (this.masterCommits || [])[0];
      return commit ? commit.url.replace(/\/commit\/[^/]+$/, '') : null;
    },
  },
  methods: {
    groupByDay(commits) {
      const days = [];
      (commits || []).forEach(commit => {
        const label = commit.date ? formatLocalDate(commit.date) : '';
        const day = days[days.length - 1];
        if (day && day.label === label) {
          day.commits.push(commit);
        } else {
          days.push({ label, commits: [commit] });
        }
      });
      return days;
    },
    author(commit) {
      return commit.author_login || commit.author_name || '';
    },
    initial(commit) {
      return this.author(commit).charAt(0).toUpperCase();
    },
    title(commit) {
      return commit.message.split('\n')[0];
    },
    // The rest of the message: paragraphs, and runs of "- " lines as lists
    body(commit) {
      const rest = commit.message.split('\n').slice(1).join('\n').trim();
      if (!rest) {
        return [];
      }
      return rest.split(/\n\s*\n/).map(chunk => {
        const lines = chunk.split('\n').map(line => line.trim()).filter(line => line);
        // A bullet that wraps onto the next lines stays one item
        if (/^[-*] /.test(lines[0])) {
          const list = [];
          lines.forEach(line => {
            if (/^[-*] /.test(line)) {
              list.push(line.slice(2));
            } else {
              list[list.length - 1] += ' ' + line;
            }
          });
          return { list };
        }
        return { text: lines.join(' ') };
      });
    },
    formatTime(dateString) {
      return dateString ? formatLocalDate(dateString, { timeStyle: 'short' }) : '';
    },
  },
};
</script>
