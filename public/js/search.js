/* search.js — home page hero search (front-end only for now): jumps to the chosen nation. */
(function () {
  'use strict';
  var form = document.querySelector('[data-search-form]');
  if (!form) return;

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var destination = form.elements['nation'].value;   // the option value is the nation page URL

    if (destination) {
      window.location.href = destination + '#itineraries';
      return;
    }

    var section = document.getElementById('destinations');
    if (section) section.scrollIntoView({ behavior: 'smooth' });
    if (window.Teyvat && window.Teyvat.toast) window.Teyvat.toast('Pick a nation to start your search.');
  });
})();