/* booking-link.js — sends every "Book Now" button (data-action="book-now") to the booking page.
 * Uses the teyvat:action hook from actions.js: preventDefault() suppresses the "coming soon" toast.
 * An itinerary button (data-itinerary="...") passes its id along as ?itinerary=... */
(function () {
  'use strict';

  document.addEventListener('teyvat:action', function (e) {
    if (e.detail.action !== 'book-now') return;
    var base = document.body.dataset.bookingUrl;
    if (!base) return;
    e.preventDefault();

    var url = new URL(base, window.location.href);
    var onBookingPage = url.pathname === window.location.pathname;

    if (onBookingPage) {
      var app = document.getElementById('booking-app');
      if (app) app.scrollIntoView({ behavior: 'smooth', block: 'start' });
      return;
    }
    if (e.detail.itinerary) url.searchParams.set('itinerary', e.detail.itinerary);
    window.location.href = url.toString();
  });
})();
