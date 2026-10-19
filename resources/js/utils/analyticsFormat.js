// Shared helpers for the analytics tabs. Expects `heroes` ([{ code, name }]) and
// `maps` ({ id: name }) props on the component.
export default {
  methods: {
    // The "you" or "global" row of a result set, or an empty object
    pick(rows, who) {
      return (rows || []).find(r => r.who === who) || {};
    },
    round(value, digits = 1) {
      if (value === null || value === undefined || value === '') {
        return null;
      }
      const factor = Math.pow(10, digits);
      return Math.round(Number(value) * factor) / factor;
    },
    percent(value) {
      return value === null || value === undefined ? '-' : this.round(Number(value) * 100, 1) + '%';
    },
    share(part, total) {
      return total ? this.round((100 * Number(part)) / Number(total), 1) + '%' : '-';
    },
    clock(seconds) {
      if (seconds === null || seconds === undefined) {
        return '-';
      }
      const s = Math.round(Math.abs(Number(seconds)));
      return (Number(seconds) < 0 ? '-' : '') + Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0');
    },
    // +0:12 / -0:12: ahead or behind
    signedClock(seconds) {
      if (seconds === null || seconds === undefined) return '-';
      return (seconds < 0 ? '-' : '+') + this.clock(Math.abs(seconds));
    },
    orDash(value) {
      return value === null || value === undefined ? '-' : value;
    },
    // ", more than most players (21%)" style comparison for a one-line takeaway; empty without a global value
    versus(mine, global, unit = '', margin = 3) {
      if (global === null || global === undefined || mine === null || mine === undefined) return '';
      if (mine > global + margin) return ', more than most players (' + this.round(global) + unit + ')';
      if (mine < global - margin) return ', less than most players (' + this.round(global) + unit + ')';
      return ', about the same as most players';
    },
    // High / Medium / Low ratings (Coaching, Stacks)
    confidenceClass(level) {
      return level === 'High' ? 'text-teal' : level === 'Medium' ? 'text-yellow' : 'text-red';
    },
    heroName(id) {
      const hero = (this.heroes || []).find(h => h.code === Number(id));
      return hero ? hero.name : 'Unknown hero';
    },
    mapName(id) {
      return (this.maps || {})[id] || 'Map ' + id;
    },
    // The site's filters name maps; the analytics data uses map ids
    mapId(name) {
      const entry = Object.entries(this.maps || {}).find(([, n]) => n === name);
      return entry ? Number(entry[0]) : null;
    },
  },
};
