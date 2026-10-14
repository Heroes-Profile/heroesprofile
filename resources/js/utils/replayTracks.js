// Hero paths for the replay viewer, built once per game and sampled each frame.
//
// A replay only says exactly where a hero is now and then: the game's position updates, the spawn,
// globe pickups and the death spot. Between two of those, 'follow' walks the hero toward its move
// orders at hero speed, easing onto the next exact point over its last few seconds (bending the whole
// walk tested worse against held-out positions); 'smooth' just glides between exact points. Last hits and takedowns put the hero within attack or ability
// range of a spot, so a path that strays further than that is pulled in.

const SPEED = 5; // map units per second, about base hero speed
const STEP = 0.5; // seconds between path samples
const LAST_HIT_RANGE = 6;
const TAKEDOWN_RANGE = 8;
const MAX_DRIFT = 25; // a walk that misses the next exact point by more than this is not trusted
const EASE = 3; // seconds over which a walk eases onto the next exact point

export function buildTracks(data, { mode = 'follow', walkable = null } = {}) {
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

  data.lives.forEach(([p, born, bx, by, died]) => {
    const end = died ?? length;
    const inLife = list => list.filter(a => a[0] >= born && a[0] <= end).sort((a, b) => a[0] - b[0]);
    const anchors = dedupe([[born, bx, by], ...inLife(exact.get(p) || [])]);
    const lifeMoves = inLife(moves.get(p) || []);

    let path = walk(anchors, lifeMoves, end, mode);

    // Pull the path within range of each last hit and takedown, then walk again with those as fixed points
    const pulled = inLife(near.get(p) || []).map(([t, x, y, range]) => {
      const [px, py] = sample(path, t);
      const d = Math.hypot(px - x, py - y);
      return d <= range ? null : [t, x + (px - x) * range / d, y + (py - y) * range / d];
    }).filter(Boolean);
    if (pulled.length > 0) {
      path = walk(dedupe([...anchors, ...pulled].sort((a, b) => a[0] - b[0])), lifeMoves, end, mode);
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

// Path samples [t, x, y] from the first anchor to the end of the life
function walk(anchors, moves, end, mode) {
  const path = [];
  for (let i = 0; i < anchors.length; i++) {
    const from = anchors[i];
    const to = anchors[i + 1];
    const until = to ? to[0] : end;
    const segment = mode === 'follow' ? follow(from, moves, until) : [from];

    if (to) {
      const last = segment.length > 1 ? sample(segment, until) : [from[1], from[2]];
      const missX = to[1] - last[0];
      const missY = to[2] - last[1];
      if (mode !== 'follow' || Math.hypot(missX, missY) > MAX_DRIFT || until === from[0]) {
        path.push(from);
      } else {
        const ease = Math.min(until - from[0], EASE);
        segment.forEach(([t, x, y]) => {
          const share = Math.max(0, (t - (until - ease)) / ease);
          path.push([t, x + missX * share, y + missY * share]);
        });
      }
    } else {
      path.push(...segment);
    }
  }
  return path;
}

// Walk from an anchor toward the latest move order, a sample every STEP seconds. An order given
// shortly before the anchor may still be under way.
function follow(from, moves, until) {
  const out = [from];
  let [t, x, y] = from;
  let next = moves.findIndex(m => m[0] > t);
  if (next === -1) next = moves.length;
  let target = next > 0 && t - moves[next - 1][0] <= 8 ? moves[next - 1] : null;

  while (t < until) {
    const dt = Math.min(STEP, until - t);
    while (next < moves.length && moves[next][0] <= t + dt) {
      target = moves[next];
      next++;
    }
    if (target) {
      const dx = target[1] - x;
      const dy = target[2] - y;
      const d = Math.hypot(dx, dy);
      const stride = SPEED * dt;
      if (d <= stride) {
        x = target[1];
        y = target[2];
        target = null;
      } else {
        x += dx / d * stride;
        y += dy / d * stride;
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
