/* lightbox.js — nation pages: click a gallery photo to enlarge; arrows / keys to browse. */
(function () {
  'use strict';
  if (!document.querySelector('.gallery-thumb')) return;

  var lightboxes = {};
  document.querySelectorAll('.lightbox').forEach(function (lb) {
    lightboxes[lb.dataset.lightbox] = lb;
  });
  var current = { key: null, index: 0, thumbs: [] };

  function showSlide() {
    var lb = lightboxes[current.key];
    var thumb = current.thumbs[current.index];
    var img = thumb.querySelector('img');
    var caption = thumb.dataset.caption || img.alt || '';
    var lbImg = lb.querySelector('.lightbox-image');
    lbImg.src = img.currentSrc || img.src;
    lbImg.alt = caption;
    lb.querySelector('.lightbox-caption').textContent = caption;
  }

  function open(key, index) {
    var lb = lightboxes[key];
    if (!lb) { console.warn('[lightbox.js] no lightbox for gallery', key); return; }
    current.key = key;
    current.thumbs = Array.prototype.slice.call(
      document.querySelectorAll('.gallery[data-gallery="' + key + '"] .gallery-thumb'));
    current.index = index;
    showSlide();
    lb.classList.add('is-open');
    lb.setAttribute('aria-hidden', 'false');
    document.body.classList.add('lightbox-open');
    lb.querySelector('.lightbox-close').focus();
  }

  function close() {
    if (!current.key) return;
    var lb = lightboxes[current.key];
    lb.classList.remove('is-open');
    lb.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('lightbox-open');
    current.key = null;
  }

  function step(delta) {
    if (!current.key) return;
    var len = current.thumbs.length;
    current.index = (current.index + delta + len) % len;
    showSlide();
  }

  document.querySelectorAll('.gallery').forEach(function (gallery) {
    var key = gallery.dataset.gallery;
    Array.prototype.slice.call(gallery.querySelectorAll('.gallery-thumb')).forEach(function (btn, i) {
      btn.addEventListener('click', function () { open(key, i); });
    });
  });

  document.querySelectorAll('.lightbox').forEach(function (lb) {
    lb.querySelector('.lightbox-close').addEventListener('click', close);
    lb.querySelector('.lightbox-backdrop').addEventListener('click', close);
    lb.querySelector('.lightbox-prev').addEventListener('click', function () { step(-1); });
    lb.querySelector('.lightbox-next').addEventListener('click', function () { step(1); });
  });

  document.addEventListener('keydown', function (e) {
    if (!current.key) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowLeft') step(-1);
    if (e.key === 'ArrowRight') step(1);
  });
})();