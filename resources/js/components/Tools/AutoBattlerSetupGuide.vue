<template>
  <div class="bg-lighten rounded">
    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 font-bold text-lg">
      <span><i class="fa-solid fa-circle-info mr-2"></i>How to set this up</span>
      <i :class="['fa-solid', open ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
    </button>

    <ol v-if="open" class="list-decimal pl-10 pr-4 pb-4 flex flex-col gap-3">
      <li v-for="(step, i) in steps" :key="i">
        <span v-html="step.text"></span>
        <img v-if="step.image" :src="'/images/autobattler/' + step.image" :alt="'Step ' + (i + 1)" loading="lazy" class="mt-2 max-w-full md:max-w-[600px] rounded">
      </li>
    </ol>
  </div>
</template>

<script>
export default {
  name: 'AutoBattlerSetupGuide',
  data() {
    return {
      open: false,
      steps: [
        { text: 'Open the main installation folder for Heroes of the Storm. For most PC users it will be <code>C:\\Program Files (x86)\\Heroes of the Storm</code>.' },
        { text: 'Open the <strong>Maps</strong> folder. If missing, create it.', image: 'mapsFolder.png' },
        { text: 'Open the <strong>Heroes</strong> folder. If missing, create it.', image: 'heroesFolder.png' },
        { text: 'Open the <strong>SinglePlayerMaps</strong> folder. If missing, create it.', image: 'singplePlayerMapsFolder.png' },
        { text: 'Open the <strong>StartingExperience</strong> folder. If missing, create it.', image: 'startingExperienceFolder.png' },
        { text: 'Download the <a class="link" href="https://github.com/spazzo966/HeroesAITalentBuilds" target="_blank" rel="noopener">HeroesAITalentBuilds</a> repository.', image: 'repository.png' },
        { text: 'Extract the ZIP: right click the .zip file and click <strong>Extract All</strong>.', image: 'extract.png' },
        { text: 'Choose a map and place the entire folder inside the <strong>StartingExperience</strong> folder.' },
        { text: 'Rename the pasted folder to <strong>TutorialVeteran.StormMap</strong>.', image: 'tutorialFolder1.png' },
        { text: 'Open the <strong>TutorialVeteran.StormMap</strong> folder.' },
        { text: 'Open the <strong>Base.StormData</strong> folder.' },
        { text: 'Open the <strong>Mods</strong> folder.' },
        { text: 'Use the Auto Battler below to create the AI builds and download the files.' },
        { text: 'Paste the downloaded files inside the <strong>Mods</strong> folder.', image: 'playerData.png' },
        { text: 'Make sure they are named correctly, from <strong>Player1Data.xml</strong> to <strong>Player10Data.xml</strong>.' },
        { text: 'Open the in-game Options menu and go to <strong>Challenges</strong> (ESC key or gear icon).', image: 'menu.png' },
        { text: 'Play the <strong>Veteran Introduction</strong>.', image: 'play.png' },
      ],
    };
  },
};
</script>
