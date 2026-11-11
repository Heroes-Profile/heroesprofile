<template>
  <div>
    <analytics-heading :title="'What Analytics is'" :takeaway="'Your own season games, read from their replays and compared with everyone else.'">
      Every number comes from your processed replays (games from Oct 2, 2026 on). Pick a section below, or use the filters to narrow everything to a game type, map or hero.
    </analytics-heading>
    <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-6 mb-10 text-sm">
      <div class="bg-darken rounded p-4">
        <h3 class="font-bold mb-2">You vs global</h3>
        <p>Each section compares your games with a fixed 10% sample of all games under the same filters. Every game in that sample is processed, whoever played it, so the comparison isn't tilted towards people who use Analytics.</p>
      </div>
      <div class="bg-darken rounded p-4">
        <h3 class="font-bold mb-2">One match at a time</h3>
        <p>Pick a match from the Match list at the top, from Match History, or from the replay viewer. Every section then shows that match alone, against your usual on the same hero and against everyone on the same hero, map and mode.</p>
      </div>
      <div class="bg-darken rounded p-4">
        <h3 class="font-bold mb-2">How it's worked out</h3>
        <p>Each section ends with its methodology: what the data is, how each number is calculated, and what it can't tell you. Replays record exact positions only now and then, so position-based numbers are estimates.</p>
      </div>
      <div class="bg-darken rounded p-4">
        <h3 class="font-bold mb-2">Keeping Analytics</h3>
        <p>Analytics is in pre-alpha, with a free trial for every Battle.net account, and a new one with each stage of development. To keep it, subscribe on <a class="link" href="https://www.patreon.com/heroesprofile" target="_blank">Patreon</a> at any tier, or hold any paid <a class="link" href="/Api">API plan</a> and link your Battle.net account on your API account page.</p>
      </div>
    </div>

    <!-- One card per section, like the home page's sections -->
    <div class="flex flex-wrap justify-center items-stretch">
      <div v-for="section in sections" :key="section.tab" class="text-center md:w-[30%] mb-12 flex flex-col mx-5">
        <i :class="section.icon" style="font-size: 60px;"></i>
        <h3 class="text-2xl mb-4 mt-2">{{ section.title }}</h3>
        <p class="mb-6">{{ section.text }}</p>
        <custom-button
          :href="pathFor(section.tab)"
          :text="'Open ' + section.title"
          :alt="'Open ' + section.title"
          class="mt-auto"
          :color="'teal'"
          :ignoreclick="true"
          @click="$emit('open', section.tab)"
        ></custom-button>
      </div>
    </div>
  </div>
</template>

<script>
// The /Analytics landing page: what Analytics is, how comparisons work, and a card per section
export default {
  name: 'AnalyticsSummary',
  props: {
    pathFor: Function,
  },
  emits: ['open'],
  data() {
    return {
      sections: [
        { tab: 'deaths', title: 'Deaths', icon: 'fas fa-skull', text: 'Where and when you die, who kills you, whether you were ganked or lost a teamfight, and how often you die on the enemy half.' },
        { tab: 'map', title: 'Map & Positions', icon: 'fas fa-map', text: 'Heatmaps of where you spend your time on each map and where you send your hero, and how you split your time between lanes.' },
        { tab: 'positioning', title: 'Positioning', icon: 'fas fa-crosshairs', text: 'Whether your positioning is good: how you use lanes all game, where you stand when fights start, and whether you get to objectives and camps in time.' },
        { tab: 'timelines', title: 'Timelines & Objectives', icon: 'fas fa-stream', text: 'How your games unfold: level leads, comebacks, talent tier races, objectives, camps and forts, experience sources and kill participation.' },
        { tab: 'activity', title: 'Activity', icon: 'fas fa-mouse-pointer', text: 'How busy you are over a game, when you pick talents, and how you use each of your hero\'s abilities.' },
        { tab: 'sessions', title: 'Sessions', icon: 'fas fa-clock', text: 'Whether you play worse the longer you play, how you do after a win or a loss, by time of day, game length and disconnects.' },
        { tab: 'coaching', title: 'Coaching', icon: 'fas fa-chalkboard-teacher', text: 'Habits that go with winning and losing, like overstaying lost fights or missing objectives, with what to work on and examples from your replays.' },
        { tab: 'stacks', title: 'Stacks', icon: 'fas fa-users', text: 'How you do solo and in a party, against premade stacks, and what the teams that beat bigger stacks do differently.' },
        { tab: 'games', title: 'Match History', icon: 'fas fa-list', text: 'Every season match, with a replay viewer for each one and a button to see all of Analytics for that single match.' },
      ],
    };
  },
};
</script>
