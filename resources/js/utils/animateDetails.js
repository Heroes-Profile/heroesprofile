// Slides every <details> open and shut instead of snapping.
// One listener for the whole page, so it also covers <details> that Vue adds later.
// Add data-no-animate to a <details> to opt out. Skipped for visitors who ask for reduced motion.

const DURATION = 250;
const EASING = 'cubic-bezier(0.2, 0, 0, 1)';

function size(element) {
  return { width: element.offsetWidth + 'px', height: element.offsetHeight + 'px' };
}

function finish(details) {
  details.style.overflow = '';
  details.classList.remove('is-closing');
  details._slide = null;
}

function slide(details) {
  // A click during an animation turns it around from wherever it is
  const opening = details._slide ? details._slide.closing : !details.open;
  const start = size(details);
  if (details._slide) {
    details._slide.animation.cancel();
  }

  // Measure the end state without painting it. A closing details stays open until the slide ends.
  details.open = opening;
  const end = size(details);
  details.open = true;

  details.style.overflow = 'hidden';
  // Lets the chevron turn back now rather than when the slide ends (see app.css)
  details.classList.toggle('is-closing', !opening);
  const animation = details.animate([start, end], { duration: DURATION, easing: EASING });
  details._slide = { animation, closing: !opening };

  if (opening) {
    // The content drops in a little as it fades up
    [...details.children].filter(child => child.tagName !== 'SUMMARY').forEach(child => {
      child.animate(
        [{ opacity: 0, transform: 'translateY(-0.5rem)' }, { opacity: 1, transform: 'none' }],
        { duration: DURATION, easing: EASING }
      );
    });
  }

  // A cancelled slide (clicked again mid-way) rejects, and the new slide takes over
  animation.finished.then(() => {
    if (!opening) {
      details.open = false;
    }
    finish(details);
  }, () => {});
}

export function animateDetails() {
  document.addEventListener('click', event => {
    const summary = event.target.closest('summary');
    const details = summary && summary.parentElement;
    if (!details || details.tagName !== 'DETAILS' || 'noAnimate' in details.dataset) {
      return;
    }
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !details.animate) {
      return;
    }
    event.preventDefault();
    slide(details);
  });
}
