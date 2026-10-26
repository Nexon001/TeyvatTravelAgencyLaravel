/* nav.js — navigation: mobile hamburger menu + the desktop "Destinations" dropdown. */
(function () {
  'use strict';

  /* ---- Mobile menu: hamburger toggle, close on link tap / Escape / outside tap ---- */
  var toggle = document.querySelector('.nav-toggle');
  var nav = document.querySelector('.site-nav');

  if (toggle && nav) {
    var setOpen = function (open) {
      nav.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
      document.body.classList.toggle('nav-open', open);
    };

    toggle.addEventListener('click', function () {
      setOpen(!nav.classList.contains('is-open'));
    });

    // close after tapping a link (the "Destinations" trigger is a button, so it does not close the menu)
    nav.addEventListener('click', function (e) {
      if (e.target.closest('a[href]')) setOpen(false);
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') setOpen(false);
    });

    document.addEventListener('click', function (e) {
      if (nav.classList.contains('is-open') && !e.target.closest('.site-header')) setOpen(false);
    });

    // reset if the window is resized up to desktop width (keep in sync with the CSS breakpoint)
    window.matchMedia('(min-width: 901px)').addEventListener('change', function (m) {
      if (m.matches) setOpen(false);
    });
  }

  /* ---- Desktop dropdown: opens on hover (CSS) and on click / Enter / Space (here) ---- */
  var dropdown = document.querySelector('.has-dropdown');
  var trigger = dropdown && dropdown.querySelector('.nav-trigger');

  if (dropdown && trigger) {
    var setDropdown = function (open) {
      dropdown.classList.toggle('is-open', open);
      trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    trigger.addEventListener('click', function () {
      setDropdown(!dropdown.classList.contains('is-open'));
    });

    document.addEventListener('click', function (e) {
      if (!e.target.closest('.has-dropdown')) setDropdown(false);
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') setDropdown(false);
    });

    // keyboard users tabbing out of the menu close it
    dropdown.addEventListener('focusout', function (e) {
      if (!dropdown.contains(e.relatedTarget)) setDropdown(false);
    });
  }
})();