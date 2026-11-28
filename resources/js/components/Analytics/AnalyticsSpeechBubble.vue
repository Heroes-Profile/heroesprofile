<template>
  <!-- Comic style: the Heroes Profile logo is the announcer, its line in a speech bubble pointing at it. Logo at the top, so long bubbles read down from it. -->
  <div class="flex items-start gap-4">
    <img
      :src="logo"
      alt="Heroes Profile"
      :title="$t('Heroes Profile')"
      class="w-14 h-auto shrink-0 announcer-logo"
      :class="{ talking }"
      @animationend="$emit('done')"
    >
    <div class="speech-bubble flex-1" :class="{ pop: talking, 'two-lines': twoLines }" :title="title">
      <slot></slot>
    </div>
  </div>
</template>

<script>
// Shared by the replay viewer's announcer and the Coaching summary
export default {
  name: 'AnalyticsSpeechBubble',
  props: {
    // Plays the logo's bounce and the bubble's pop; emits done when the bounce ends
    talking: Boolean,
    // Fixed two-line height, so a live feed never shifts the page
    twoLines: Boolean,
    title: { type: String, default: '' },
    // Another Heroes Profile logo to speak with (the help chat's Diablo and Xal'atath)
    logo: { type: String, default: '/images/logo/heroesprofilelogo.png' },
  },
  emits: ['done'],
};
</script>

<style scoped>
.announcer-logo.talking {
  animation: announcer-talk 1.1s ease-in-out;
}

/* A comic speech bubble with its tail pointing left at the logo */
.speech-bubble {
  position: relative;
  background: #f4f4f8;
  color: #111;
  border: 2px solid #111;
  border-radius: 1rem;
  padding: 0.6rem 0.9rem;
  line-height: 1.4;
}

/* Links stay dark on the light bubble; the site's light hover colours vanish here */
.speech-bubble :deep(a) {
  color: inherit;
}

/* The palette's teal */
.speech-bubble :deep(a:hover) {
  color: #008b8b;
}

.speech-bubble.two-lines {
  /* Exactly two lines plus padding and border, whatever the text */
  height: calc(2.8em + 1.2rem + 0.25rem);
  display: flex;
  align-items: center;
}

/* Level with the middle of the logo (w-14), not the middle of the bubble */
.speech-bubble::before,
.speech-bubble::after {
  content: '';
  position: absolute;
  top: 1.75rem;
  transform: translateY(-50%);
  border-style: solid;
}

/* The tail's outline, then its fill on top */
.speech-bubble::before {
  left: -0.875rem;
  border-width: 0.5625rem 0.875rem 0.5625rem 0;
  border-color: transparent #111 transparent transparent;
}

.speech-bubble::after {
  left: -0.625rem;
  border-width: 0.4375rem 0.6875rem 0.4375rem 0;
  border-color: transparent #f4f4f8 transparent transparent;
}

.speech-bubble.pop {
  animation: bubble-pop 0.3s ease-out;
}

@keyframes bubble-pop {
  0% { transform: scale(0.92); opacity: 0.6; }
  100% { transform: scale(1); opacity: 1; }
}

@keyframes announcer-talk {
  0% { transform: translateY(0) rotate(0); }
  20% { transform: translateY(-0.1875rem) rotate(-5deg); }
  45% { transform: translateY(0) rotate(4deg); }
  70% { transform: translateY(-2px) rotate(-2deg); }
  100% { transform: translateY(0) rotate(0); }
}

@media (prefers-reduced-motion: reduce) {
  .announcer-logo.talking,
  .speech-bubble.pop {
    animation: none;
  }
}
</style>
