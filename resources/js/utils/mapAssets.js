import minimaps from '../components/Analytics/minimaps.js';

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
  return map ? { ...map, slug, src: `/images/analytics/minimaps/${slug}.png` } : null;
}

/** The map's structures as icons: { kind, team, x, y, size, src }. */
export function structureIcons(minimap) {
  return (minimap?.structures || []).map(([kind, team, x, y]) => ({
    kind, team, x, y, size: ICON_SIZE[kind], src: `/images/analytics/minimaps/icons/${ICON[kind]}.png`,
  }));
}

// Map shortcuts, from the units in each map's Objects file, drawn with the game's minimap icons while they're open:
// Haunted Mines' ladders (down on the surface, up underground; open while the mines are, from the mine boss
// appearing until it dies), Warhead Junction's two waygates (each to the other) and Towers of Doom's (both bases'
// lead to the one near the middle). exit: a hero can come out there.
export const MAP_SHORTCUTS = {
  haunted_mines: {
    open: 'mines',
    markers: [
      ...[[128, 211], [128, 156], [97, 180], [159, 188]].map(([x, y]) => ({ icon: 'ladder_down', x, y, exit: true })),
      ...[[144, 103], [168, 64], [144, 25], [120, 64]].map(([x, y]) => ({ icon: 'ladder_up', x, y, exit: true })),
    ],
  },
  warhead_junction: {
    open: 'always',
    markers: [{ icon: 'teleporter', x: 124, y: 84, exit: true }, { icon: 'teleporter', x: 124, y: 164, exit: true }],
  },
  towers_of_doom: {
    open: 'unknown',
    markers: [{ icon: 'waygate_in', x: 56, y: 85, exit: false }, { icon: 'waygate_in', x: 192, y: 86, exit: false }, { icon: 'waygate_out', x: 124, y: 82, exit: true }],
  },
};

// Where those shortcuts put a hero out: [[x, y]] by map slug
export const MAP_SHORTCUT_EXITS = Object.fromEntries(Object.entries(MAP_SHORTCUTS)
  .map(([slug, map]) => [slug, map.markers.filter(m => m.exit).map(m => [m.x, m.y])]));

/** [from, to] spans when a map's shortcuts are open, from the game's map objective units. */
export function shortcutOpenSpans(slug, units, length) {
  const rule = MAP_SHORTCUTS[slug]?.open;
  if (rule === 'always') return [[0, length]];
  if (rule === 'mines') {
    // The mine boss appears as the mines open and dies as they close (a placeholder lives at 0-0)
    return (units || [])
      .filter(u => u.name === 'UnderworldBoss' && !(u.b[0] === 0 && u.d && u.d[0] === 0))
      .map(u => [u.b[0], u.d ? u.d[0] : length]);
  }
  return [];
}

/** The shortcut markers to draw at time t: { icon, x, y, src, size }. */
export function shortcutMarkers(slug, spans, t) {
  const map = MAP_SHORTCUTS[slug];
  if (!map || !spans.some(([from, to]) => t >= from && t < to)) return [];
  return map.markers.map(m => ({ ...m, src: `/images/analytics/minimaps/icons/${m.icon}.png`, size: m.icon.startsWith('ladder') ? 7 : 9 }));
}

export function teamColor(team) {
  return team === -1 ? NEUTRAL_COLOR : TEAM_COLORS[team] ?? TEAM_COLORS[1];
}

// The green the game tints Abathur's hat with on the minimap while his Symbiote rides an ally
export const SYMBIOTE_COLOR = '#40a018';

/** feColorMatrix values that multiply the game's white icons by a team's colour. */
export function tintMatrix(team) {
  return tintMatrixFor(teamColor(team));
}

/** feColorMatrix values that multiply the game's white icons by a colour. */
export function tintMatrixFor(hex) {
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
        const px = Math.round((x - (minimap.left || 0)) * scale);
        const py = Math.round((minimap.top - y) * scale);
        return px >= 0 && py >= 0 && px < img.width && py < img.height && alpha[(py * img.width + px) * 4 + 3] > 40;
      });
    };
    img.onerror = () => resolve(null);
    img.src = minimap.src;
  });
}
