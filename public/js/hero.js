/* hero.js — home page hero: counts the stats up from 0 when they scroll into view.
 * The real numbers are rendered by Blade (computed from resources/data/nations.php), so the page still shows
 * the correct values if JavaScript is off. Works for single numbers ("21") and ranges ("2–6").
 * Skipped for visitors who prefer reduced motion, and when the script arrives late on a slow connection
 * (so visible numbers never jump back to zero). Loaded on every page; exits immediately if there is no hero. */
(function () {
  'use strict';

  var list = document.querySelector('[data-count-up]');
  if (!list) return;

  var items = Array.prototype.slice.call(list.querySelectorAll('strong'));
  if (!items.length) return;

  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduceMotion || !window.requestAnimationFrame) return;

  var DURATION = 1300;   // ms
  var MAX_LOAD_TIME = 1200;   // ms since navigation start; if this script arrives later (slow connection), skip the animation

  // Slow connection: the real numbers are already on screen. Resetting them to 0 now would make them jump,
  // so leave them alone. (If the stats are still below the fold, nobody has seen them yet, so animating is safe.)
  var rect = list.getBoundingClientRect();
  var onScreen = rect.top < window.innerHeight && rect.bottom > 0;
  var arrivedLate = window.performance && typeof performance.now === 'function' && performance.now() > MAX_LOAD_TIME;
  if (onScreen && arrivedLate) return;

  // "2–6" -> ['', '2', '–', '6', '']  (odd positions are the numbers, even positions are the text between them)
  var data = items.map(function (el) {
    var finalText = el.textContent.trim();
    return { el: el, finalText: finalText, parts: finalText.split(/(\d+)/) };
  });

  function render(progress) {
    data.forEach(function (d) {
      d.el.textContent = d.parts.map(function (part, i) {
        return i % 2 ? String(Math.round(Number(part) * progress)) : part;
      }).join('');
    });
  }

  function easeOut(t) { return 1 - Math.pow(1 - t, 3); }

  function run() {
    var start = null;
    var finished = false;

    function finish() {
      if (finished) return;
      finished = true;
      data.forEach(function (d) { d.el.textContent = d.finalText; });   // always end on the exact value
    }

    function frame(now) {
      if (finished) return;
      if (start === null) start = now;
      var t = Math.min((now - start) / DURATION, 1);
      render(easeOut(t));
      if (t < 1) window.requestAnimationFrame(frame); else finish();
    }

    window.requestAnimationFrame(frame);
    // Safety net: if the browser throttles animation frames (background tab, battery saver,
    // screenshot tools), the real numbers still appear shortly after the animation should have ended.
    window.setTimeout(finish, DURATION + 700);
  }

  render(0);   // start at zero so the final numbers never flash before the count begins

  if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      if (entries[0].isIntersecting) {
        observer.disconnect();
        run();
      }
    }, { threshold: 0.2 });
    observer.observe(list);
  } else {
    run();
  }
})();
