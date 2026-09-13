<script>
const GLYPHS = {
  a: '⏃', b: '⏚', c: '☊', d: '⎅', e: '⟒', f: '⎎', g: '☌', h: '⊑', i: '⟟', j: '⟊', k: '☍', l: '⌰', m: '⋔',
  n: '⋏', o: '⍜', p: '⌿', q: '⍾', r: '⍀', s: '⌇', t: '⏁', u: '⎍', v: '⎐', w: '⍙', x: '⌖', y: '⊬', z: '⋉',
};
const FALLBACK = Object.values(GLYPHS);

const TARGETS = 'nav.bg-gray-dark a';
const SCRAMBLE_MS = 300;
const TICK_MS = 30;
const FADE_MS = 800;

// Runs in step with VoidWhispers: every nav link turns to glyphs while a whisper is showing.
export default {
  name: 'VoidGlitch',
  data() {
    return {
      active: [],
      timers: [],
      intervals: [],
    };
  },
  mounted() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      return;
    }
    window.addEventListener('void-whisper-show', this.glitchAll);
    window.addEventListener('void-whisper-hide', this.restoreAll);
  },
  beforeUnmount() {
    window.removeEventListener('void-whisper-show', this.glitchAll);
    window.removeEventListener('void-whisper-hide', this.restoreAll);
    this.timers.forEach(clearTimeout);
    this.intervals.forEach(clearInterval);
    this.cleanup();
  },
  methods: {
    toGlyph(char) {
      if (/\s/.test(char)) {
        return char;
      }
      return GLYPHS[char.toLowerCase()] || FALLBACK[Math.floor(Math.random() * FALLBACK.length)];
    },
    candidates() {
      return Array.from(document.querySelectorAll(TARGETS)).filter((el) => {
        if (el.children.length > 0 || !el.textContent.trim()) {
          return false;
        }
        if (el.getBoundingClientRect().width === 0) {
          return false;
        }
        const bg = window.getComputedStyle(el).backgroundColor;
        return bg === 'transparent' || bg === 'rgba(0, 0, 0, 0)';
      });
    },
    isFixed(el) {
      for (let node = el; node && node !== document.body; node = node.parentElement) {
        if (window.getComputedStyle(node).position === 'fixed') {
          return true;
        }
      }
      return false;
    },
    glitchAll() {
      this.cleanup();
      const entries = this.candidates().map((el) => this.createOverlay(el));
      if (!entries.length) {
        return;
      }

      const perTick = Math.max(1, Math.ceil(SCRAMBLE_MS / TICK_MS));
      let tick = 0;
      const interval = setInterval(() => {
        tick++;
        entries.forEach((entry) => {
          const count = Math.ceil((entry.order.length * tick) / perTick);
          entry.order.slice(0, count).forEach((i) => {
            entry.chars[i] = this.toGlyph(entry.original[i]);
          });
          entry.overlay.textContent = entry.chars.join('');
        });
        if (tick >= perTick) {
          clearInterval(interval);
        }
      }, TICK_MS);
      this.intervals.push(interval);
    },
    createOverlay(el) {
      const text = el.textContent;
      const range = document.createRange();
      range.selectNodeContents(el);
      const rect = range.getBoundingClientRect();
      const style = window.getComputedStyle(el);
      const fixed = this.isFixed(el);

      const overlay = document.createElement('span');
      overlay.className = 'void-glyph-overlay';
      overlay.setAttribute('aria-hidden', 'true');
      overlay.textContent = text;
      Object.assign(overlay.style, {
        position: fixed ? 'fixed' : 'absolute',
        left: `${rect.left + (fixed ? 0 : window.scrollX)}px`,
        top: `${rect.top + (fixed ? 0 : window.scrollY)}px`,
        height: `${rect.height}px`,
        lineHeight: `${rect.height}px`,
        fontFamily: style.fontFamily,
        fontSize: style.fontSize,
        fontWeight: style.fontWeight,
        fontStyle: style.fontStyle,
        textTransform: style.textTransform,
        letterSpacing: style.letterSpacing,
        transition: `opacity ${FADE_MS}ms ease`,
      });
      document.body.appendChild(overlay);

      const entry = {
        el,
        overlay,
        original: Array.from(text),
        chars: Array.from(text),
        opacity: el.style.opacity,
        transition: el.style.transition,
      };
      entry.order = entry.original.map((_, i) => i).sort(() => Math.random() - 0.5);

      el.style.transition = 'opacity 150ms ease';
      el.style.opacity = '0';
      this.active.push(entry);

      return entry;
    },
    restoreAll() {
      if (!this.active.length) {
        return;
      }
      this.active.forEach(({ el, overlay }) => {
        el.style.transition = `opacity ${FADE_MS}ms ease`;
        el.style.opacity = '1';
        overlay.style.opacity = '0';
      });
      const finishing = this.active;
      this.active = [];
      this.timers.push(setTimeout(() => this.cleanup(finishing), FADE_MS));
    },
    cleanup(entries = null) {
      const list = entries || this.active;
      list.forEach(({ el, overlay, opacity, transition }) => {
        overlay.remove();
        el.style.opacity = opacity;
        el.style.transition = transition;
      });
      if (!entries) {
        this.active = [];
      }
    },
  },
  render() {
    return null;
  },
};
</script>
