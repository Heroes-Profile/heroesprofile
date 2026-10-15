// Hero paths for the replay viewer, built once per game and sampled each frame.
//
// A replay only says exactly where a hero is now and then: the game's position updates, the spawn,
// globe pickups and the death spot. Between two of those, 'follow' walks the hero toward its move
// orders, faster while mounted, and eases onto the next exact point over its last few seconds;
// 'smooth' just glides between exact points. With the map's walk grid both go around walls rather
// than through them (walls ported from hotsixors, K-JBoon, https://github.com/K-JBoon/hotsixors,
// Apache License 2.0). Last hits and takedowns put the hero within attack or ability range of a spot,
// so a path that strays further than that is pulled in.
//
// The speeds were tuned against held-out exact positions from real games (follow: median error 3.7
// map units); dashes, blinks and Hearthstone handling tested no better and were left out.

import { routeTo, lineWalkable, findPath } from './replayPathing.js';

const SPEED = 4; // map units per second on foot
const MOUNT_SPEED = 1.3; // times faster while mounted
const MOUNT_CAST = 1; // seconds to summon a mount
const STEP = 0.5; // seconds between path samples
const LAST_HIT_RANGE = 6;
const TAKEDOWN_RANGE = 8;
const EASE = 3; // seconds over which a walk eases onto the next exact point
const MAX_DETOUR = 3; // a route around walls longer than this times the straight line isn't trusted

// data.mounts: [t, player, 1] for a mount press, [t, player, 0] for anything that dismounts.
export function buildTracks(data, { mode = 'follow', walkable = null, walkGrid = null } = {}) {
  const length = data.length_s;
  const byPlayer = new Map(data.players.map(p => [p.idx, []]));

  const exact = new Map(data.players.map(p => [p.idx, []]));
  data.positions.forEach(({ p, pts }) => pts.forEach(([t, x, y, est]) => {
    if (est === 0) exact.get(p)?.push([t, x, y]);
  }));
  data.globe_points.forEach(([t, p, x, y]) => exact.get(p)?.push([t, x, y]));
  data.deaths.forEach(d => {
    if (d.x !== null && d.y !== null) exact.get(d.v)?.push([d.t, d.x, d.y]);
  });

  const near = new Map(data.players.map(p => [p.idx, []]));
  data.minion_deaths.forEach(([t, x, y, , killer]) => {
    if (killer >= 0 && x >= 0) near.get(killer)?.push([t, x, y, LAST_HIT_RANGE]);
  });
  data.deaths.forEach(d => {
    if (d.x === null) return;
    d.k.forEach(k => near.get(k)?.push([d.t, d.x, d.y, TAKEDOWN_RANGE]));
  });

  const moves = new Map(data.players.map(p => [p.idx, []]));
  data.moves.forEach(([t, p, x, y]) => {
    if (!walkable || walkable(x, y)) moves.get(p)?.push([t, x, y]);
  });

  // [t, mounted]: mounted a moment after the press
  const mounts = new Map(data.players.map(p => [p.idx, []]));
  (data.mounts || []).forEach(([t, p, on]) => mounts.get(p)?.push(on ? [t + MOUNT_CAST, true] : [t, false]));
  mounts.forEach(list => list.sort((a, b) => a[0] - b[0]));

  data.lives.forEach(([p, born, bx, by, died]) => {
    const end = died ?? length;
    const inLife = list => list.filter(a => a[0] >= born && a[0] <= end).sort((a, b) => a[0] - b[0]);
    const anchors = dedupe([[born, bx, by], ...inLife(exact.get(p) || [])]);
    const lifeMoves = inLife(moves.get(p) || []);
    // A new life starts on foot
    const lifeMounts = [[born, false], ...inLife(mounts.get(p) || [])];
    const walkLife = points => walk(points, lifeMoves, lifeMounts, end, mode, walkGrid);

    let path = walkLife(anchors);

    // Pull the path within range of each last hit and takedown, then walk again with those as fixed points
    const pulled = inLife(near.get(p) || []).map(([t, x, y, range]) => {
      const [px, py] = sample(path, t);
      const d = Math.hypot(px - x, py - y);
      return d <= range ? null : [t, x + (px - x) * range / d, y + (py - y) * range / d];
    }).filter(Boolean);
    if (pulled.length > 0) {
      path = walkLife(dedupe([...anchors, ...pulled].sort((a, b) => a[0] - b[0])));
    }

    byPlayer.get(p)?.push({ born, died, path });
  });

  return byPlayer;
}

/** Where a player's hero is at time t, or null while dead. */
export function positionAt(lives, t) {
  const life = lives?.find(l => t >= l.born && (l.died === null || t <= l.died));
  return life ? sample(life.path, t) : null;
}

function dedupe(anchors) {
  return anchors.filter((a, i) => i === 0 || a[0] !== anchors[i - 1][0]);
}

// Points along a route around walls from one anchor to the next, spread over the time between them;
// empty when the straight line is clear or the route is a long detour
function glide(from, to, grid) {
  if (!grid || to[0] - from[0] <= 0 || lineWalkable(grid, from[1], from[2], to[1], to[2])) return [];
  const straight = Math.hypot(to[1] - from[1], to[2] - from[2]);
  const way = findPath(grid, from[1], from[2], to[1], to[2], 30);
  if (!way || way.length < 2) return [];
  const lengths = [];
  let total = 0;
  let px = from[1];
  let py = from[2];
  way.forEach(([wx, wy]) => {
    total += Math.hypot(wx - px, wy - py);
    lengths.push(total);
    px = wx;
    py = wy;
  });
  if (total > straight * MAX_DETOUR + 4) return [];
  return way.slice(0, -1).map(([wx, wy], i) => [from[0] + (to[0] - from[0]) * (lengths[i] / total), wx, wy]);
}

// Path samples [t, x, y] from the first anchor to the end of the life
function walk(anchors, moves, mounts, end, mode, grid) {
  const path = [];
  for (let i = 0; i < anchors.length; i++) {
    const from = anchors[i];
    const to = anchors[i + 1];
    const until = to ? to[0] : end;

    if (mode !== 'follow') {
      path.push(from, ...(to ? glide(from, to, grid) : []));
      continue;
    }

    const segment = follow(from, moves, mounts, until, grid);
    if (to && until > from[0]) {
      // Ease onto the next exact point over the last few seconds
      const last = sample(segment, until);
      const missX = to[1] - last[0];
      const missY = to[2] - last[1];
      const ease = Math.min(until - from[0], EASE);
      segment.forEach(([t, x, y]) => {
        const share = Math.max(0, (t - (until - ease)) / ease);
        path.push([t, x + missX * share, y + missY * share]);
      });
    } else {
      path.push(...segment);
    }
  }
  return path;
}

// Walk from an anchor toward the latest move order, a sample every STEP seconds, around walls when
// there's a walk grid. An order given shortly before the anchor may still be under way.
function follow(from, moves, mounts, until, grid) {
  const out = [from];
  let [t, x, y] = from;
  let next = moves.findIndex(m => m[0] > t);
  if (next === -1) next = moves.length;
  let target = next > 0 && t - moves[next - 1][0] <= 8 ? moves[next - 1] : null;
  let route = null;
  let nextMount = mounts.findIndex(m => m[0] > t);
  if (nextMount === -1) nextMount = mounts.length;
  let mounted = nextMount > 0 ? mounts[nextMount - 1][1] : false;

  while (t < until) {
    const dt = Math.min(STEP, until - t);
    while (next < moves.length && moves[next][0] <= t + dt) {
      target = moves[next];
      route = null;
      next++;
    }
    while (nextMount < mounts.length && mounts[nextMount][0] <= t + dt) {
      mounted = mounts[nextMount][1];
      nextMount++;
    }
    if (target) {
      route ||= routeTo(grid, x, y, target[1], target[2]);
      let stride = SPEED * (mounted ? MOUNT_SPEED : 1) * dt;
      while (stride > 0 && route.length) {
        const [wx, wy] = route[0];
        const d = Math.hypot(wx - x, wy - y);
        if (d <= stride) {
          x = wx;
          y = wy;
          stride -= d;
          route.shift();
        } else {
          x += (wx - x) / d * stride;
          y += (wy - y) / d * stride;
          stride = 0;
        }
      }
      if (!route.length) {
        target = null;
        route = null;
      }
    }
    t += dt;
    out.push([t, x, y]);
  }
  return out;
}

function sample(path, t) {
  if (path.length === 0) return [0, 0];
  if (t <= path[0][0]) return [path[0][1], path[0][2]];
  const last = path[path.length - 1];
  if (t >= last[0]) return [last[1], last[2]];
  let lo = 0;
  let hi = path.length - 1;
  while (hi - lo > 1) {
    const mid = (lo + hi) >> 1;
    if (path[mid][0] <= t) lo = mid; else hi = mid;
  }
  const a = path[lo];
  const b = path[hi];
  const share = b[0] === a[0] ? 0 : (t - a[0]) / (b[0] - a[0]);
  return [a[1] + (b[1] - a[1]) * share, a[2] + (b[2] - a[2]) * share];
}
