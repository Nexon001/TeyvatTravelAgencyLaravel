/* faq.js — FAQ accordion on the home page.
 *
 * The markup is plain <details>/<summary> (see CompanyProfile.blade.php), so every question already
 * opens and closes with no JavaScript at all, and screen readers and the keyboard work out of the box.
 * This script only adds three enhancements:
 *   1. a smooth height animation when an answer opens or closes,
 *   2. "one open at a time" — opening a question closes the previously open one,
 *   3. deep links — /#faq-booking-2 opens that exact question and scrolls to it.
 * Visitors who prefer reduced motion get the same behaviour without the animation.
 */
(function () {
  'use strict';

  var items = document.querySelectorAll('.faq-item');
  if (!items.length) return;

  var DURATION = 260;
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Slide an item's answer open or closed, then leave the <details> in its final state.
  function toggle(item, open) {
    var panel = item.querySelector('.faq-answer');
    if (!panel) { item.open = open; return; }

    // If the user clicks again mid-animation, carry on from the current height instead of jumping.
    var current = item._anim ? panel.offsetHeight : null;
    if (item._anim) item._anim.cancel();

    if (reduceMotion || !panel.animate) {          // no animation available or wanted
      item.open = open;
      return;
    }

    var from, to;
    if (open) {
      item.open = true;                            // must be open for scrollHeight to be measurable
      from = current !== null ? current : 0;
      to = panel.scrollHeight;
    } else {
      from = current !== null ? current : panel.offsetHeight;
      to = 0;
    }

    var anim = panel.animate(
      { height: [from + 'px', to + 'px'], opacity: [open ? 0 : 1, open ? 1 : 0] },
      { duration: DURATION, easing: 'ease' }
    );
    item._anim = anim;

    anim.onfinish = function () {
      item.open = open;
      if (item._anim === anim) item._anim = null;
    };
    anim.oncancel = function () { if (item._anim === anim) item._anim = null; };
  }

  items.forEach(function (item) {
    var summary = item.querySelector('summary');
    if (!summary) return;

    summary.addEventListener('click', function (e) {
      e.preventDefault();                          // we drive open/close ourselves

      var willOpen = !(item.open && !item._closing);
      item._closing = !willOpen;

      if (willOpen) {
        items.forEach(function (other) {
          if (other !== item && other.open) {
            other._closing = true;
            toggle(other, false);
          }
        });
      }
      toggle(item, willOpen);
    });
  });

  // Deep link: /#faq-booking-2 opens that question (the CSS scroll-margin keeps it below the header).
  function openFromHash() {
    var id = decodeURIComponent(window.location.hash.slice(1));
    if (!id) return;
    var target = document.getElementById(id);
    if (target && target.classList.contains('faq-item')) {
      items.forEach(function (other) { if (other !== target) other.open = false; });
      target.open = true;
      target._closing = false;
      target.scrollIntoView({ block: 'center' });
    }
  }

  openFromHash();
  window.addEventListener('hashchange', openFromHash);
})();
