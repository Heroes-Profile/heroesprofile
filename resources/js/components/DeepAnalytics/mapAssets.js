import minimaps from './minimaps.js';

// Shared by the map views and the replay viewer: which minimap a map uses, and the game's icons.

export const TEAM_COLORS = ['#315bb6', '#e44a23'];
export const NEUTRAL_COLOR = '#e6c619';

const ICON = { fort: 'town', keep: 'town', core: 'core', watch: 'watchtower' };
const ICON_SIZE = { fort: 12, keep: 12, core: 18, watch: 8 };

export function minimapFor(mapName) {
  if (!mapName) {
    return null;
  }
  const slug = mapName.toLowerCase().replace(/'/g, '').replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
  const map = minimaps[slug];
  return map ? { ...map, slug, src: `/images/deep-analytics/minimaps/${slug}.png` } : null;
}

/** The map's structures as icons: { kind, team, x, y, size, src }. */
export function structureIcons(minimap) {
  return (minimap?.structures || []).map(([kind, team, x, y]) => ({
    kind, team, x, y, size: ICON_SIZE[kind], src: `/images/deep-analytics/minimaps/icons/${ICON[kind]}.png`,
  }));
}

export function teamColor(team) {
  return team === -1 ? NEUTRAL_COLOR : TEAM_COLORS[team] ?? TEAM_COLORS[1];
}

/** feColorMatrix values that multiply the game's white icons by a team's colour. */
export function tintMatrix(team) {
  const hex = teamColor(team);
  const [r, g, b] = [1, 3, 5].map(i => parseInt(hex.substr(i, 2), 16) / 255);
  return `${r} 0 0 0 0  0 ${g} 0 0 0  0 0 ${b} 0 0  0 0 0 1 0`;
}

/** A walkable test from the minimap's transparency: outside the drawn map is not walkable. */
export function loadWalkable(minimap) {
  return new Promise(resolve => {
    if (!minimap) {
      resolve(null);
      return;
    }
    const img = new Image();
    img.onload = () => {
      const canvas = document.createElement('canvas');
      canvas.width = img.width;
      canvas.height = img.height;
      const context = canvas.getContext('2d');
      context.drawImage(img, 0, 0);
      const alpha = context.getImageData(0, 0, img.width, img.height).data;
      const scale = img.width / minimap.width;
      resolve((x, y) => {
        const px = Math.round(x * scale);
        const py = Math.round((minimap.top - y) * scale);
        return px >= 0 && py >= 0 && px < img.width && py < img.height && alpha[(py * img.width + px) * 4 + 3] > 40;
      });
    };
    img.onerror = () => resolve(null);
    img.src = minimap.src;
  });
}
