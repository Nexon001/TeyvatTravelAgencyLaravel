/* toast.js — shared "toast" message. Loaded first; other scripts call Teyvat.toast('...'). */
(function () {
  'use strict';
  window.Teyvat = window.Teyvat || {};

  var el = document.getElementById('toast');
  var authEl = document.getElementById('auth-toast');
  var signupEl = document.getElementById('signup-toast');
  var timer;
  var activeEl;

  window.Teyvat.toast = function (message) {
    var dialog = document.getElementById('login-dialog');
    var signupView = document.getElementById('signup-view');
    var dialogToast = signupView && !signupView.hidden ? signupEl : authEl;
    var target = dialog && dialog.open && dialogToast ? dialogToast : el;
    if (!target) { console.warn('[toast.js] #toast element not found'); return; }

    if (activeEl && activeEl !== target) {
      activeEl.classList.remove('is-visible');
    }

    activeEl = target;
    activeEl.textContent = message;
    activeEl.classList.add('is-visible');
    clearTimeout(timer);
    timer = setTimeout(function () {
      activeEl.classList.remove('is-visible');
      activeEl = null;
    }, 2800);
  };

  var dialog = document.getElementById('login-dialog');
  if (dialog && (authEl || signupEl)) {
    dialog.addEventListener('close', function () {
      if (activeEl !== authEl && activeEl !== signupEl) return;

      clearTimeout(timer);
      activeEl.classList.remove('is-visible');
      activeEl = null;
    });
  };
})();