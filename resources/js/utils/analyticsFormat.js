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
      const s = Math.round(Number(seconds));
      return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0');
    },
    heroName(id) {
      const hero = (this.heroes || []).find(h => h.code === Number(id));
      return hero ? hero.name : 'Unknown hero';
    },
    mapName(id) {
      return (this.maps || {})[id] || 'Map ' + id;
    },
  },
};
