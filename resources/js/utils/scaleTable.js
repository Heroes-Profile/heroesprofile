// Shrinks a table that is wider than its container so every column fits.
// A transform doesn't change layout, so the container height is set to the scaled height,
// and kept in sync as images load or the screen size changes.
export function scaleTableToFit(table, container) {
  if (!table || !container) {
    return 1;
  }

  const fit = () => {
    // scrollWidth so a wrapper around several tables is measured by its widest one
    const scale = Math.min(1, container.clientWidth / table.scrollWidth);

    if (scale < 1) {
      table.style.transformOrigin = 'top left';
      table.style.transform = `scale(${scale})`;
      container.style.height = (table.clientHeight * scale) + 'px';
    } else {
      // Never scale up; that clips the right side
      table.style.transformOrigin = '';
      table.style.transform = '';
      container.style.height = '';
    }

    return scale;
  };

  if (!table.scaleObserver) {
    table.scaleObserver = new ResizeObserver(() => fit());
    table.scaleObserver.observe(table);
    table.scaleObserver.observe(container);
  }

  return fit();
}
