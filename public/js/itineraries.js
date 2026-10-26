/* itineraries.js — nation pages: inline "View Details" panels and the sort dropdown. */
(function () {
  'use strict';

  // View Details / Hide Details (no separate page)
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-toggle-details]');
    if (!btn) return;
    var panel = document.getElementById(btn.getAttribute('aria-controls'));
    if (!panel) { console.warn('[itineraries.js] details panel not found for', btn); return; }
    var open = btn.getAttribute('aria-expanded') === 'true';
    btn.setAttribute('aria-expanded', open ? 'false' : 'true');
    panel.hidden = open;
    var label = btn.querySelector('.label');
    if (label) label.textContent = open ? 'View Details' : 'Hide Details';
  });

  // Sort the cards using each card's data-price / data-nights / data-order attributes
  var sort = document.querySelector('[data-sort]');
  if (!sort) return;
  var list = document.querySelector(sort.dataset.sort);
  if (!list) { console.warn('[itineraries.js] sort target not found:', sort.dataset.sort); return; }
  var cards = Array.prototype.slice.call(list.children);

  sort.addEventListener('change', function () {
    var mode = sort.value;
    cards.slice().sort(function (a, b) {
      if (mode === 'price-asc') return a.dataset.price - b.dataset.price;
      if (mode === 'price-desc') return b.dataset.price - a.dataset.price;
      if (mode === 'duration') return a.dataset.nights - b.dataset.nights;
      return a.dataset.order - b.dataset.order;   // popularity = original order
    }).forEach(function (card) { list.appendChild(card); });
  });
})();