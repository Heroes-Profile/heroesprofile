// Shrinks a table that is wider than its container so every column fits, and keeps it fitted as images load
// or the screen size changes.
// CSS zoom rather than a transform: zoom shrinks the real layout, so the container's height follows on its own
// and sticky table headers stay at the top of the screen. (A transform is applied after layout, so a stuck header
// drifted down the screen as the page scrolled, leaving rows showing above it.)
export function scaleTableToFit(table, container) {
  if (!table || !container) {
    return 1;
  }

  const fit = () => {
    // Measured at full size; scrollWidth so a wrapper around several tables is measured by its widest one
    table.style.zoom = '';
    const scale = Math.min(1, container.clientWidth / table.scrollWidth);

    // Never scale up; that clips the right side
    table.style.zoom = scale < 1 ? String(scale) : '';

    return scale;
  };

  if (!table.scaleObserver) {
    table.scaleObserver = new ResizeObserver(() => fit());
    table.scaleObserver.observe(table);
    table.scaleObserver.observe(container);
  }

  return fit();
}
