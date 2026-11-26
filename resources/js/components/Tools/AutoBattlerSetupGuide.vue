<template>
  <!-- Closed it's a small dropdown; open it's the full guide, one tab per system, in four sections -->
  <button v-if="!open" type="button" class="self-start w-full sm:w-[13rem] h-10 rounded bg-surface-3 hover:bg-surface-4 px-3 flex items-center justify-between gap-2 text-sm" @click="open = true">
    {{ $t('How to set this up') }}
    <basil-icon name="arrow" class="rotate-90 text-xs"></basil-icon>
  </button>

  <div v-else class="bg-surface-3 rounded p-4 md:p-6 flex flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <button type="button" class="flex items-center gap-2 text-sm font-semibold" @click="open = false">
        {{ $t('How to set this up') }}
        <basil-icon name="arrow" class="-rotate-90 text-xs"></basil-icon>
      </button>
      <tab-button :small="true" :tabs="systemTabs" :ignoreclick="true" :overridedefaultside="system" @tab-click="system = $event"></tab-button>
    </div>
    <p class="text-sm text-subtext">{{ $t('Takes about 5 minutes. You only need steps 1–9 once; after that, start from step 10.') }}</p>
    <p v-if="system !== 'windows'" class="text-xs text-subtext">{{ $t('Heroes of the Storm finds the map the same way on every system; only the game folder and file tools differ. The screenshots are from Windows.') }}</p>

    <div v-for="(section, sectionIndex) in sections" :key="section.title" class="bg-surface rounded-panel p-4 md:p-5 grid md:grid-cols-[minmax(0,30rem)_minmax(0,1fr)] gap-4">
      <div class="flex flex-col gap-3 min-w-0">
        <div class="flex items-center gap-3">
          <span class="w-7 h-7 shrink-0 rounded-full bg-teal flex items-center justify-center text-sm font-bold">{{ sectionIndex + 1 }}</span>
          <div class="flex flex-col">
            <span class="font-semibold">{{ $t(section.title) }}</span>
            <span class="text-xs text-subtext">{{ $t(section.subtitle) }}</span>
          </div>
        </div>
        <ol class="flex flex-col gap-2">
          <li v-for="step in section.steps" :key="step.number" class="flex gap-3 text-sm">
            <span class="w-5 h-5 shrink-0 rounded-[0.25rem] bg-surface-1 flex items-center justify-center text-[0.625rem] text-subtext">{{ step.number }}</span>
            <a v-if="step.builder" href="#auto-battler" class="text-lteal hover:underline">{{ $t(step.text) }}</a>
            <a v-else-if="step.href" :href="step.href" target="_blank" rel="noopener" class="text-lteal hover:underline">{{ $t(step.text) }}</a>
            <span v-else>{{ $t(step.text) }}</span>
          </li>
        </ol>
        <div v-if="section.prefixes" class="rounded-control bg-surface-1 p-3 flex flex-col gap-1 text-xs">
          <span class="font-semibold">{{ $t('Where is my Wine prefix?') }}</span>
          <span v-for="prefix in section.prefixes" :key="prefix.launcher"><span class="text-subtext">{{ prefix.launcher }}:</span> <code class="text-lteal break-all">{{ prefix.path }}</code></span>
        </div>
        <code v-if="section.path" class="rounded-lg bg-surface-1 px-3 py-2 text-xs text-lteal break-all">{{ section.path }}</code>
      </div>
      <div v-if="system === 'windows' && section.images.length" class="flex flex-wrap gap-3 content-start">
        <button v-for="image in section.images" :key="image" type="button" class="block w-[10rem] h-[6.5rem] rounded overflow-hidden bg-white cursor-zoom-in" @click="zoomed = image">
          <img :src="'/images/autobattler/' + image" :alt="$t('Screenshot')" loading="lazy" class="w-full h-full object-cover object-left-top">
        </button>
      </div>
    </div>

    <!-- A screenshot full size; click outside or ✕ to close -->
    <modal-box v-if="zoomed" width="max-w-[64rem]" container-class="bg-surface-4 rounded p-4 relative" @close="zoomed = null">
      <button type="button" class="absolute top-2 right-2 w-8 h-8 rounded-full bg-surface-1 hover:bg-surface-3 flex items-center justify-center" :aria-label="$t('Close')" @click="zoomed = null"><basil-icon name="cross"></basil-icon></button>
      <img :src="'/images/autobattler/' + zoomed" :alt="$t('Screenshot')" class="w-full h-auto max-h-[80vh] object-contain rounded bg-white">
    </modal-box>
  </div>
</template>

<script>
const FOLDERS = 'Maps/Heroes/SinglePlayerMaps/StartingExperience';

// The folder steps every system shares; the first step (finding the game folder) differs
const folderSteps = [
  { number: 2, text: 'Open the Maps folder. Create it if missing.' },
  { number: 3, text: 'Open the Heroes folder. Create it if missing.' },
  { number: 4, text: 'Open the SinglePlayerMaps folder. Create it if missing.' },
  { number: 5, text: 'Open the StartingExperience folder. Create it if missing.' },
];
const buildSteps = [
  { number: 10, text: 'Open TutorialVeteran.StormMap → Base.StormData → Mods.' },
  { number: 11, text: 'Use the Auto Battler below to pick heroes and talents, then download the files.', builder: true },
  { number: 12, text: 'Paste the files into the Mods folder.' },
  { number: 13, text: 'Check the names: Player1Data.xml to Player10Data.xml.' },
];
const startSteps = [
  { number: 14, text: 'Open the in-game menu (ESC or the gear icon) and go to Challenges.' },
  { number: 15, text: 'Play the Veteran Introduction.' },
];
const REPOSITORY_STEP = { number: 6, text: 'Download the HeroesAITalentBuilds repository from GitHub (Code → Download ZIP).', href: 'https://github.com/spazzo966/HeroesAITalentBuilds' };

const SYSTEMS = {
  windows: {
    path: 'C:\\Program Files (x86)\\Heroes of the Storm\\' + FOLDERS.replaceAll('/', '\\'),
    first: 'Open the Heroes of the Storm installation folder.',
    extract: 'Extract the ZIP: right-click it and choose Extract All.',
    copy: 'Pick a map and paste its whole folder into StartingExperience.',
    rename: 'Rename the pasted folder to TutorialVeteran.StormMap.',
    names: null,
  },
  mac: {
    path: '/Applications/Heroes of the Storm/' + FOLDERS,
    first: 'In the Battle.net app, select Heroes of the Storm, click the gear next to Play and choose Show in Finder. It is usually /Applications/Heroes of the Storm.',
    extract: 'Double-click the ZIP in Downloads to unzip it (Safari may already have).',
    copy: 'Pick a map and copy its whole folder into StartingExperience.',
    rename: 'Rename the copied folder to TutorialVeteran.StormMap. If Finder asks about adding the .StormMap extension, choose Add.',
    names: 'Check the names: Player1Data.xml to Player10Data.xml. If your browser saved one as "Player1Data (1).xml", rename it back.',
  },
  linux: {
    path: '<prefix>/drive_c/Program Files (x86)/Heroes of the Storm/' + FOLDERS,
    first: 'Open your Wine prefix (see below), then drive_c/Program Files (x86)/Heroes of the Storm. Battle.net\'s gear next to Play → Show in Explorer also opens it.',
    extract: 'Extract the ZIP with your file manager (Extract Here) or unzip.',
    copy: 'Pick a map and copy its whole folder into StartingExperience.',
    rename: 'Rename the copied folder to TutorialVeteran.StormMap. Folder names are case-sensitive on Linux, so match them exactly.',
    names: null,
    prefixes: [
      { launcher: 'Lutris', path: '~/Games/battlenet' },
      { launcher: 'Steam (Proton)', path: '~/.steam/steam/steamapps/compatdata/<appid>/pfx' },
      { launcher: 'Bottles', path: '~/.local/share/bottles/bottles/<name>' },
      { launcher: 'Wine', path: '~/.wine' },
    ],
  },
};

function detectSystem() {
  const platform = (navigator.userAgentData?.platform || navigator.platform || navigator.userAgent || '').toLowerCase();
  if (platform.includes('mac')) return 'mac';
  if (platform.includes('linux') && !platform.includes('android')) return 'linux';
  return 'windows';
}

export default {
  name: 'AutoBattlerSetupGuide',
  data() {
    return {
      open: false,
      zoomed: null,
      system: detectSystem(),
      systemTabs: [
        { key: 'windows', text: 'Windows' },
        { key: 'mac', text: 'macOS' },
        { key: 'linux', text: 'Linux' },
      ],
    };
  },
  mounted() {
    this.onKeydown = event => { if (event.key === 'Escape') this.zoomed = null; };
    document.addEventListener('keydown', this.onKeydown);
  },
  beforeUnmount() {
    document.removeEventListener('keydown', this.onKeydown);
  },
  computed: {
    sections() {
      const system = SYSTEMS[this.system];
      return [
        {
          title: 'Prepare the game folder',
          subtitle: 'Create the folder path the custom map needs.',
          steps: [{ number: 1, text: system.first }, ...folderSteps],
          images: ['mapsFolder.png', 'heroesFolder.png', 'singplePlayerMapsFolder.png', 'startingExperienceFolder.png'],
          path: system.path,
          prefixes: system.prefixes,
        },
        {
          title: 'Get the battle map',
          subtitle: 'Download the map and put it in place.',
          steps: [REPOSITORY_STEP, { number: 7, text: system.extract }, { number: 8, text: system.copy }, { number: 9, text: system.rename }],
          images: ['repository.png', 'extract.png', 'tutorialFolder1.png'],
        },
        {
          title: 'Add the players\' builds',
          subtitle: 'Create the builds here and drop the files in.',
          steps: buildSteps.map(step => (step.number === 13 && system.names ? { ...step, text: system.names } : step)),
          images: ['playerData.png'],
        },
        {
          title: 'Start the battle',
          subtitle: 'Launch it from the game client.',
          steps: startSteps,
          images: ['menu.png', 'play.png'],
        },
      ];
    },
  },
};
</script>
