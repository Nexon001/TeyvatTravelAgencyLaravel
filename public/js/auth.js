/* auth.js — opens the login dialog and keeps its front-end-only form honest. */
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

  if (!dialog || !loginView || !signupView || !title || !description || !closeButton || !loginForm || !signupForm || !loginFeedback || !signupFeedback) {
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
    showFeedback(loginFeedback, "Login isn't available yet. Your details haven't been sent.");
    clearTimeout(loginFeedbackTimer);
    loginFeedbackTimer = setTimeout(function () {
      clearFeedback(loginFeedback);
    }, 2800);
  });

  signupForm.addEventListener('submit', function (e) {
    e.preventDefault();

    var password = signupForm.elements.password.value;
    var passwordConfirmation = signupForm.elements.password_confirmation.value;
    if (password !== passwordConfirmation) {
      showFeedback(signupFeedback, 'The passwords do not match.');
      signupForm.elements.password_confirmation.focus();
      return;
    }

    showFeedback(signupFeedback, "Account registration isn't available yet. Your details haven't been sent.");
  });

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
})();
