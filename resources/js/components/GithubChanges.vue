<template>
  <div class="grid grid-cols-2 gap-4">
    <div class="col-span-1">
      <h2>Master Branch Commits</h2>
      <ul>
        <li v-for="commit in masterCommits" :key="commit.sha" class="commit-item">
          <a class="link" :href="`https://github.com/${commit.commit.author.name}`" target="_blank">{{ commit.commit.author.name }}</a> committed <a class="link" :href="`https://github.com/Heroes-Profile/heroesprofile/commit/${commit.sha}`" target="_blank">{{ truncateSha(commit.sha) }}</a> on {{ formatDate(commit.commit.author.date) }}: {{ commit.commit.message }} 
        </li>
      </ul>
    </div>
    <div class="col-span-1">
      <h2>Develop Branch Commits</h2>
      <ul>
        <li v-for="commit in developerCommits" :key="commit.sha" class="commit-item">
          <a class="link" :href="`https://github.com/${commit.commit.author.name}`" target="_blank">{{ commit.commit.author.name }}</a> committed <a class="link" :href="`https://github.com/Heroes-Profile/heroesprofile/commit/${commit.sha}`" target="_blank">{{ truncateSha(commit.sha) }}</a> on {{ formatDate(commit.commit.author.date) }}: {{ commit.commit.message }} 
        </li>
      </ul>
    </div>
  </div>
</template>

<style scoped>
.commit-item {
  margin-bottom: 10px; /* Adjust the value as needed for the desired space */
}
</style>


<script>
import moment from 'moment-timezone';

export default {
  name: 'GithubChanges',
  components: {
  },
  props: {
    access_token: String,
  },
  data() {
    return {
      masterCommits: [],
      developerCommits: [],
    };
  },
  mounted() {
    const owner = 'Heroes-Profile';
    const repo = 'heroesprofile';

    // Fetch commits for the master branch
    this.fetchCommits(owner, repo, 'master', this.access_token)
      .then(response => {
        this.masterCommits = response.data;
        console.log(this.masterCommits);
      })
      .catch(error => {
        console.error('Error fetching GitHub commits for the master branch:', error);
      });

    // Fetch commits for the developer branch
    this.fetchCommits(owner, repo, 'develop', this.access_token)
      .then(response => {
        this.developerCommits = response.data;
      })
      .catch(error => {
        console.error('Error fetching GitHub commits for the developer branch:', error);
      });
  },
  methods: {
    fetchCommits(owner, repo, branch, token) {
      const apiUrl = `https://api.github.com/repos/${owner}/${repo}/commits?sha=${branch}`;

      return this.$axios.get(apiUrl, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
    },
    formatDate(dateString) {
      const originalDate = moment.tz(dateString, 'Atlantic/Reykjavik'); // Assuming date strings are in UTC
      const localDate = originalDate.clone().tz(moment.tz.guess());

      return localDate.format('MM/DD/YYYY h:mm:ss a');
    },
    truncateSha(sha) {
      return sha.slice(0, 7);
    },
  },
};
</script>