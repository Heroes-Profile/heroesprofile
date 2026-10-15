// Walking around walls for the replay viewer: a walk grid from the map's pathing mask and A* routes on it.
// Ported from hotsixors (K-JBoon, https://github.com/K-JBoon/hotsixors, site/static/replay/pathing.js,
// Apache License 2.0), used with the author's permission.

/** Grid from a pathing mask's RGBA pixels (blue > 127 = blocked, row 0 = top of the map); cells are 1 game unit, row 0 = y 0. */
export function buildWalkGrid(rgba, width, height) {
  const blocked = new Uint8Array(width * height);
  for (let py = 0; py < height; py++) {
    const cy = height - 1 - py;
    for (let px = 0; px < width; px++) {
      blocked[cy * width + px] = rgba[(py * width + px) * 4 + 2] > 127 ? 1 : 0;
    }
  }
  return { width, height, blocked };
}

/** The walk grid for a minimap, from its pathing mask, or null if there isn't one. */
export function loadWalkGrid(minimap) {
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
      resolve(buildWalkGrid(context.getImageData(0, 0, img.width, img.height).data, img.width, img.height));
    };
    img.onerror = () => resolve(null);
    img.src = `/images/analytics/minimaps/pathing/${minimap.slug}.png`;
  });
}

function isBlocked(grid, cx, cy) {
  if (cx < 0 || cy < 0 || cx >= grid.width || cy >= grid.height) return true;
  return grid.blocked[cy * grid.width + cx] === 1;
}

export function lineWalkable(grid, x0, y0, x1, y1) {
  const dx = x1 - x0;
  const dy = y1 - y0;
  const steps = Math.ceil(Math.hypot(dx, dy) / 0.5);
  if (steps === 0) return !isBlocked(grid, Math.floor(x0), Math.floor(y0));
  for (let i = 0; i <= steps; i++) {
    const t = i / steps;
    if (isBlocked(grid, Math.floor(x0 + dx * t), Math.floor(y0 + dy * t))) return false;
  }
  return true;
}

function nearestWalkable(grid, x, y, maxRadius = 6) {
  const cx = Math.floor(x);
  const cy = Math.floor(y);
  if (!isBlocked(grid, cx, cy)) return [x, y];
  for (let r = 1; r <= maxRadius; r++) {
    for (let oy = -r; oy <= r; oy++) {
      for (let ox = -r; ox <= r; ox++) {
        if (Math.max(Math.abs(ox), Math.abs(oy)) !== r) continue;
        if (!isBlocked(grid, cx + ox, cy + oy)) return [cx + ox + 0.5, cy + oy + 0.5];
      }
    }
  }
  return null;
}

const SQRT2 = Math.SQRT2;
const MAX_EXPANSIONS = 30000;

/** Waypoints from (fromX, fromY) to (toX, toY) around blocked cells, ending at the target; null if there's no route. */
export function findPath(grid, fromX, fromY, toX, toY, snapRadius = 6) {
  const start = nearestWalkable(grid, fromX, fromY, snapRadius);
  const goal = nearestWalkable(grid, toX, toY, snapRadius);
  if (!start || !goal) return null;

  const { width, height } = grid;
  const startIdx = Math.floor(start[1]) * width + Math.floor(start[0]);
  const goalIdx = Math.floor(goal[1]) * width + Math.floor(goal[0]);
  if (startIdx === goalIdx) return [[toX, toY]];

  const gScore = new Float32Array(width * height).fill(Infinity);
  const cameFrom = new Int32Array(width * height).fill(-1);
  const closed = new Uint8Array(width * height);
  const gx = goalIdx % width;
  const gy = (goalIdx - gx) / width;
  const h = (x, y) => {
    const dx = Math.abs(x - gx);
    const dy = Math.abs(y - gy);
    return dx < dy ? dx * (SQRT2 - 1) + dy : dy * (SQRT2 - 1) + dx;
  };
  const heap = [[h(startIdx % width, (startIdx - (startIdx % width)) / width), startIdx]];
  gScore[startIdx] = 0;
  const push = item => {
    heap.push(item);
    let i = heap.length - 1;
    while (i > 0) {
      const parent = (i - 1) >> 1;
      if (heap[parent][0] <= heap[i][0]) break;
      [heap[parent], heap[i]] = [heap[i], heap[parent]];
      i = parent;
    }
  };
  const pop = () => {
    const top = heap[0];
    const last = heap.pop();
    if (heap.length) {
      heap[0] = last;
      let i = 0;
      for (;;) {
        const l = i * 2 + 1;
        const r = l + 1;
        let small = i;
        if (l < heap.length && heap[l][0] < heap[small][0]) small = l;
        if (r < heap.length && heap[r][0] < heap[small][0]) small = r;
        if (small === i) break;
        [heap[small], heap[i]] = [heap[i], heap[small]];
        i = small;
      }
    }
    return top;
  };

  let expansions = 0;
  let found = false;
  while (heap.length) {
    const [, current] = pop();
    if (current === goalIdx) {
      found = true;
      break;
    }
    if (closed[current]) continue;
    closed[current] = 1;
    if (++expansions > MAX_EXPANSIONS) break;
    const cx = current % width;
    const cy = (current - cx) / width;
    for (let dy = -1; dy <= 1; dy++) {
      for (let dx = -1; dx <= 1; dx++) {
        if (dx === 0 && dy === 0) continue;
        const nx = cx + dx;
        const ny = cy + dy;
        if (nx < 0 || ny < 0 || nx >= width || ny >= height) continue;
        if (isBlocked(grid, nx, ny)) continue;
        if (dx !== 0 && dy !== 0 && (isBlocked(grid, cx + dx, cy) || isBlocked(grid, cx, cy + dy))) continue;
        const n = ny * width + nx;
        if (closed[n]) continue;
        const g = gScore[current] + (dx !== 0 && dy !== 0 ? SQRT2 : 1);
        if (g >= gScore[n]) continue;
        gScore[n] = g;
        cameFrom[n] = current;
        push([g + h(nx, ny), n]);
      }
    }
  }
  if (!found) return null;

  const cells = [];
  for (let at = goalIdx; at !== -1; at = cameFrom[at]) {
    cells.push(at);
    if (at === startIdx) break;
  }
  cells.reverse();
  const points = cells.map(c => [(c % width) + 0.5, Math.floor(c / width) + 0.5]);
  points[points.length - 1] = [toX, toY];
  // Keep only the corners: drop waypoints the previous kept one can see past
  const out = [];
  let anchorX = fromX;
  let anchorY = fromY;
  for (let i = 0; i < points.length; i++) {
    const next = points[i + 1];
    if (!next) {
      out.push(points[i]);
      break;
    }
    if (!lineWalkable(grid, anchorX, anchorY, next[0], next[1])) {
      out.push(points[i]);
      anchorX = points[i][0];
      anchorY = points[i][1];
    }
  }
  return out;
}

/** Straight to the target when nothing is in the way, else around it. */
export function routeTo(grid, x, y, tx, ty) {
  if (!grid || lineWalkable(grid, x, y, tx, ty)) return [[tx, ty]];
  return findPath(grid, x, y, tx, ty) || [[tx, ty]];
}
