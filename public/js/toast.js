/* toast.js — shared "toast" message. Loaded first; other scripts call Teyvat.toast('...'). */
(function () {
  'use strict';
  window.Teyvat = window.Teyvat || {};

  var el = document.getElementById('toast');
  var timer;

  window.Teyvat.toast = function (message) {
    if (!el) { console.warn('[toast.js] #toast element not found'); return; }
    el.textContent = message;
    el.classList.add('is-visible');
    clearTimeout(timer);
    timer = setTimeout(function () { el.classList.remove('is-visible'); }, 2800);
  };
})();