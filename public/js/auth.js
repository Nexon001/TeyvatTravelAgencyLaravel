/* auth.js — the login / sign-up pop-up.
 *
 * Opens the dialog, switches between its two views, and sends both forms to the server
 * (POST /login and POST /register) without leaving the page. On success the page reloads, or goes to
 * the address the server names (an admin goes to the dashboard; a visitor who was sent away from a
 * protected page goes back to it). The dialog is only in the page for visitors who are not logged in.
 */
(function () {
  'use strict';

  var dialog = document.getElementById('login-dialog');
  var loginView = document.getElementById('login-view');
  var signupView = document.getElementById('signup-view');
  var title = document.getElementById('auth-title');
  var description = document.getElementById('auth-description');
  var closeButton = document.getElementById('auth-close');
  var loginForm = document.getElementById('login-form');
  var signupForm = document.getElementById('signup-form');
  var loginFeedback = document.getElementById('login-feedback');
  var signupFeedback = document.getElementById('signup-feedback');
  var opener;
  var loginFeedbackTimer;

  // Logged-in visitors get no dialog in the page: nothing to do.
  if (!dialog) return;

  if (!loginView || !signupView || !title || !description || !closeButton || !loginForm || !signupForm || !loginFeedback || !signupFeedback) {
    console.error('[auth.js] Login dialog markup is incomplete.');
    return;
  }

  document.addEventListener('teyvat:action', function (e) {
    if (e.detail.action !== 'login' && e.detail.action !== 'signup') return;

    e.preventDefault();
    opener = e.target;
    showView(e.detail.action);
    if (!dialog.open) {
      dialog.showModal();
    }
    focusView();
  });

  dialog.addEventListener('click', function (e) {
    var switchButton = e.target.closest('[data-auth-switch]');
    if (switchButton) {
      showView(switchButton.dataset.authSwitch, true);
      focusView();
      return;
    }

    if (e.target === dialog || e.target.closest('[data-close-login]')) {
      dialog.close();
    }
  });

  dialog.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      e.preventDefault();
      dialog.close();
    }
  });

  dialog.addEventListener('close', function () {
    clearFeedback(loginFeedback);
    clearFeedback(signupFeedback);
    loginForm.reset();
    signupForm.reset();
    showView('login');

    if (opener && opener.isConnected) {
      opener.focus();
    }
  });

  loginForm.addEventListener('submit', function (e) {
    e.preventDefault();
    submitForm(loginForm, loginFeedback);
  });

  signupForm.addEventListener('submit', function (e) {
    e.preventDefault();

    // Quick check before asking the server; the server checks again.
    var password = signupForm.elements.password.value;
    var passwordConfirmation = signupForm.elements.password_confirmation.value;
    if (password !== passwordConfirmation) {
      clearInvalid(signupForm);
      showFeedback(signupFeedback, 'The passwords do not match.');
      signupForm.elements.password_confirmation.setAttribute('aria-invalid', 'true');
      signupForm.elements.password_confirmation.focus();
      return;
    }

    submitForm(signupForm, signupFeedback);
  });

  /* ---------- talking to the server ---------- */

  function csrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
  }

  // A tab left open past the session lifetime has a stale token: fetch a fresh one.
  function refreshToken() {
    return fetch(dialog.dataset.csrfUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && data.token) meta.content = data.token;
      });
  }

  function post(form, retried) {
    return fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken()
      }
    }).then(function (res) {
      if (res.status === 419 && !retried && dialog.dataset.csrfUrl) {
        return refreshToken().then(function () { return post(form, true); });
      }
      return res.json().catch(function () { return {}; }).then(function (data) {
        return { ok: res.ok, status: res.status, data: data };
      });
    });
  }

  function firstError(result) {
    var data = result.data || {};
    if (data.errors) {
      var fields = Object.keys(data.errors);
      if (fields.length) return { field: fields[0], message: data.errors[fields[0]][0] };
    }
    if (result.status === 429) return { message: 'Too many attempts. Please wait a moment and try again.' };
    if (result.status === 419) return { message: 'Your session expired. Please reload the page and try again.' };
    return { message: 'Something went wrong. Please try again.' };
  }

  function submitForm(form, feedback) {
    var button = form.querySelector('[type="submit"]');
    if (button.disabled) return;   // ignore a second click while sending

    button.disabled = true;
    form.setAttribute('aria-busy', 'true');
    clearFeedback(feedback);
    clearInvalid(form);

    function release() {
      button.disabled = false;
      form.removeAttribute('aria-busy');
    }

    post(form).then(function (result) {
      if (result.ok) {
        var target = result.data && result.data.redirect;
        if (target) {
          window.location.href = target;
        } else {
          window.location.reload();
        }
        return;   // the button stays disabled until the page changes
      }

      var error = firstError(result);
      showFeedback(feedback, error.message);
      release();

      var field = error.field && form.elements[error.field];
      if (field) {
        field.setAttribute('aria-invalid', 'true');
        field.focus();
      }
    }).catch(function () {
      showFeedback(feedback, "We couldn't reach the server. Check your connection and try again.");
      release();
    });
  }

  function clearInvalid(form) {
    Array.prototype.forEach.call(form.querySelectorAll('[aria-invalid]'), function (input) {
      input.removeAttribute('aria-invalid');
    });
  }

  function showView(view, animate) {
    var isSignup = view === 'signup';
    var previousView = isSignup ? loginView : signupView;
    var nextView = isSignup ? signupView : loginView;
    previousView.hidden = true;
    nextView.hidden = false;

    if (animate) {
      nextView.classList.remove('auth-view--enter-forward', 'auth-view--enter-back');
      void nextView.offsetWidth;
      nextView.classList.add(isSignup ? 'auth-view--enter-forward' : 'auth-view--enter-back');
    }

    title.textContent = isSignup ? 'Create account' : 'Login';
    closeButton.setAttribute('aria-label', isSignup ? 'Close sign-up form' : 'Close login form');
    description.textContent = isSignup
      ? 'Join Teyvat Travel Services and start planning your journey.'
      : 'Welcome back, traveler. Sign in to continue.';
    clearFeedback(loginFeedback);
    clearFeedback(signupFeedback);
    clearInvalid(loginForm);
    clearInvalid(signupForm);
  }

  dialog.addEventListener('animationend', function (e) {
    if (e.target.classList.contains('auth-view')) {
      e.target.classList.remove('auth-view--enter-forward', 'auth-view--enter-back');
    }
  });

  function focusView() {
    var activeView = signupView.hidden ? loginView : signupView;
    var firstField = activeView.querySelector('[data-auth-focus]');
    if (firstField) {
      firstField.focus();
    }
  }

  function clearFeedback(element) {
    if (element === loginFeedback) {
      clearTimeout(loginFeedbackTimer);
    }

    element.hidden = true;
    element.textContent = '';
  }

  function showFeedback(element, message) {
    element.textContent = message;
    element.hidden = false;
  }

  /* ---------- opened by the server ---------- */
  // /login and /register send visitors to the home page with ?auth=login (or signup); a protected page or an
  // idle logout does the same and can add a message. Open the pop-up, then tidy the address bar.
  var autoOpen = dialog.dataset.authOpen;
  if (autoOpen === 'login' || autoOpen === 'signup') {
    showView(autoOpen);
    if (dialog.dataset.authMessage) {
      showFeedback(autoOpen === 'signup' ? signupFeedback : loginFeedback, dialog.dataset.authMessage);
    }
    dialog.showModal();
    focusView();

    if (window.history && window.history.replaceState) {
      var url = new URL(window.location.href);
      url.searchParams.delete('auth');
      window.history.replaceState(null, '', url.pathname + url.search + url.hash);
    }
  }
})();
