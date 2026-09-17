<template>
  <div class="grid grid-cols-2 gap-4 max-w-[1500px] mx-auto">
    <div v-for="column in columns" :key="column.title" class="col-span-1">
      <h2 class="text-2xl">{{ column.title }}</h2>
      <p v-if="column.commits === null" class="text-sm">Couldn't load commits from GitHub. Try again in a few minutes.</p>
      <ul v-else>
        <li v-for="commit in column.commits" :key="commit.sha" class="commit-item">
          <span class="text-xs block">
            <a v-if="commit.author_login" class="link" :href="`https://github.com/${commit.author_login}`" target="_blank">{{ commit.author_login }}</a>
            <template v-else>{{ commit.author_name }}</template>
            committed <a class="link" :href="commit.url" target="_blank">{{ commit.short_sha }}</a> on {{ formatDate(commit.date) }}:
          </span>
          <span class="border p-1 block" :class="column.background">{{ commit.message }}</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<style scoped>
.commit-item {
  margin-bottom: 10px;
}
</style>

<script>
import moment from 'moment-timezone';

export default {
  name: 'GithubChanges',
  props: {
    masterCommits: { type: Array, default: null },
    developCommits: { type: Array, default: null },
  },
  computed: {
    columns() {
      return [
        { title: 'Master Branch Commits', commits: this.masterCommits, background: 'bg-blue' },
        { title: 'Develop Branch Commits', commits: this.developCommits, background: 'bg-gray-dark' },
      ];
    },
  },
  methods: {
    formatDate(dateString) {
      if (!dateString) {
        return '';
      }

      return moment.utc(dateString).tz(moment.tz.guess()).format('MM/DD/YYYY h:mm:ss a');
    },
  },
};
</script>
