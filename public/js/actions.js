/* actions.js — backend-ready buttons.
 *
 * Every element with a data-action attribute (Book Now, search icon, ...) dispatches a
 * cancelable "teyvat:action" event whose detail holds the element's data-* attributes.
 * Until the backend exists a placeholder toast is shown. To take over, listen for the event
 * and call preventDefault() to suppress the toast, e.g.:
 *
 *   document.addEventListener('teyvat:action', function (e) {
 *     if (e.detail.action === 'book-now') {
 *       e.preventDefault();
 *       // fetch('/bookings', { method: 'POST', body: JSON.stringify({ itinerary: e.detail.itinerary }) })
 *     }
 *   });
 */
(function () {
  'use strict';
  var messages = {
    'book-now': 'Online booking is coming soon.',
    'search': 'Site search is coming soon.'
  };

  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-action]');
    if (!el) return;
    e.preventDefault();

    var evt = new CustomEvent('teyvat:action', {
      bubbles: true,
      cancelable: true,
      detail: Object.assign({}, el.dataset)
    });
    el.dispatchEvent(evt);

    if (!evt.defaultPrevented && window.Teyvat && window.Teyvat.toast) {
      window.Teyvat.toast(messages[el.dataset.action] || 'This feature is coming soon.');
    }
  });
})();