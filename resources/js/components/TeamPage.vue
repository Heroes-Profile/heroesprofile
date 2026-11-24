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
        <p class="text-sm">{{ $t(member.bio) }}</p>
        <!-- How to reach them: email and Reddit link out; a Discord username can only be shown -->
        <div v-if="member.email || member.reddit || member.discord" class="flex flex-col gap-1.5 text-sm">
          <a v-if="member.email" :href="'mailto:' + member.email" class="group/bt inline-flex items-center gap-2 w-fit"><i class="fas fa-envelope w-4 text-center text-teal"></i><span class="group-hover/bt:underline">{{ member.email }}</span></a>
          <a v-if="member.reddit" :href="'https://www.reddit.com/user/' + member.reddit" target="_blank" rel="noopener noreferrer" class="group/bt inline-flex items-center gap-2 w-fit"><i class="fab fa-reddit-alien w-4 text-center text-teal"></i><span class="group-hover/bt:underline">u/{{ member.reddit }}</span></a>
          <span v-if="member.discord" class="inline-flex items-center gap-2" :title="$t('Discord')"><i class="fab fa-discord w-4 text-center text-teal"></i>{{ member.discord }}</span>
        </div>
      </panel-box>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TeamPage',
  props: {
    members: { type: Array, required: true },
  },
  methods: {
    // "June 2018 – Present", the months in the page's language
    period(member) {
      return this.month(member.start) + ' – ' + (member.end ? this.month(member.end) : this.$t('Present'));
    },
    month(value) {
      const [year, month] = String(value).split('-').map(Number);
      return new Intl.DateTimeFormat(document.documentElement.lang || 'en', { month: 'long', year: 'numeric', timeZone: 'UTC' }).format(new Date(Date.UTC(year, month - 1, 1)));
    },
  },
}
</script>
