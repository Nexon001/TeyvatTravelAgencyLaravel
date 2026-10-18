@php
    $nationRoutes = ['mondstadt', 'liyue', 'inazuma', 'sumeru', 'fontaine', 'natlan', 'snezhnaya'];
    $navActive = fn (string ...$names) => request()->routeIs(...$names) ? 'aria-current="page"' : '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title') | Teyvat Travel Services</title>
<meta name="description" content="@yield('description')">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="@yield('body-class')">
@include('partials.sprite')
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header">
  <div class="container nav-row">
    <a class="brand" href="{{ route('home') }}">
      <svg class="brand-mark" viewBox="0 0 48 48" fill="none" aria-hidden="true">
        <circle cx="24" cy="24" r="21" stroke="#d9a548" stroke-width="2"/>
        <path d="M24 9l3.2 11.8L39 24l-11.8 3.2L24 39l-3.2-11.8L9 24l11.8-3.2z" fill="#d9a548"/>
        <circle cx="24" cy="24" r="2.6" fill="#161b27"/>
      </svg>
      <span class="brand-text">Teyvat Travel Services<small>Journeys through the Seven Nations</small></span>
    </a>

    <button class="nav-toggle" type="button" aria-expanded="false" aria-label="Open navigation"><span></span></button>

    <nav class="site-nav" aria-label="Primary">
      <ul>
        <li class="has-dropdown">
          <a href="javascript:void(0);" role="button" aria-haspopup="true" {!! $navActive(...$nationRoutes) !!}>Destinations <svg class="icon caret"><use href="#i-chevron"/></svg></a>
          <ul class="dropdown">
            @foreach ($nationRoutes as $r)
              <li><a href="{{ route($r) }}" {!! $navActive($r) !!}>{{ ucfirst($r) }}</a></li>
            @endforeach
          </ul>
        </li>
        <li><a href="{{ route('home') }}#itineraries">Itineraries</a></li>
        <li><a href="{{ route('home') }}#about">About Us</a></li>
      </ul>
      <div class="nav-actions">
        {{-- Backend hooks: both buttons fire a "teyvat:action" event (see script at the bottom). --}}
        <button type="button" class="nav-icon-btn" data-action="search" aria-label="Search"><svg class="icon"><use href="#i-search"/></svg></button>
        <button type="button" class="btn btn-gold btn-pill btn-sm" data-action="book-now">Book Now</button>
      </div>
    </nav>
  </div>
</header>

<main id="main">
  @yield('content')
</main>

<footer class="site-footer">
  <div class="container footer-grid">
    <div>
      <h4>Teyvat Travel Services</h4>
      <p class="footer-blurb">Independent, itinerary-first travel planning across all seven nations — one desk, one director, one dossier per traveller.</p>
    </div>
    <div>
      <h4>Destinations</h4>
      <ul>
        @foreach ($nationRoutes as $r)
          <li><a href="{{ route($r) }}">{{ ucfirst($r) }}</a></li>
        @endforeach
      </ul>
    </div>
    <div>
      <h4>Agency</h4>
      <ul>
        <li><a href="{{ route('home') }}#about">About us</a></li>
        <li><a href="{{ route('home') }}#director">About the Director</a></li>
        <li><a href="{{ route('home') }}#itineraries">Featured itineraries</a></li>
        <li><a href="mailto:desk@teyvattravel.co">desk@teyvattravel.co</a></li>
      </ul>
    </div>
  </div>
  <div class="container footer-bottom">
    <span>© 2026 Teyvat Travel Services. All rights reserved.</span>
    <span>Sole proprietorship · est. Mondstadt</span>
  </div>
</footer>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script>
/* ---- Mobile navigation ---- */
(function () {
  var toggle = document.querySelector('.nav-toggle');
  var nav = document.querySelector('.site-nav');
  if (!toggle || !nav) return;

  function setOpen(open) {
    nav.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
    document.body.classList.toggle('nav-open', open);
  }
  toggle.addEventListener('click', function () { setOpen(!nav.classList.contains('is-open')); });
  nav.addEventListener('click', function (e) {
    if (e.target.closest('a[href]:not([href^="javascript"])')) setOpen(false);
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
  document.addEventListener('click', function (e) {
    if (nav.classList.contains('is-open') && !e.target.closest('.site-header')) setOpen(false);
  });
  window.matchMedia('(min-width: 901px)').addEventListener('change', function (m) { if (m.matches) setOpen(false); });
})();
</script>

<script>
/* ---- Backend-ready buttons, itinerary details, sorting, hero search ---- */
(function () {
  var toast = document.getElementById('toast');
  var toastTimer;
  function showToast(msg) {
    toast.textContent = msg;
    toast.classList.add('is-visible');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { toast.classList.remove('is-visible'); }, 2800);
  }

  // Every [data-action] button dispatches a cancelable "teyvat:action" event.
  // When the backend is ready, listen for it and call event.preventDefault() to
  // suppress the placeholder message, e.g.:
  //   document.addEventListener('teyvat:action', function (e) {
  //     if (e.detail.action === 'book-now') { e.preventDefault(); /* fetch(...) */ }
  //   });
  var messages = { 'book-now': 'Online booking is coming soon.', 'search': 'Site search is coming soon.' };
  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-action]');
    if (!el) return;
    e.preventDefault();
    var evt = new CustomEvent('teyvat:action', { bubbles: true, cancelable: true, detail: Object.assign({}, el.dataset) });
    el.dispatchEvent(evt);
    if (!evt.defaultPrevented) showToast(messages[el.dataset.action] || 'This feature is coming soon.');
  });

  // View Details / Hide Details (inline, no extra page)
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-toggle-details]');
    if (!btn) return;
    var panel = document.getElementById(btn.getAttribute('aria-controls'));
    var open = btn.getAttribute('aria-expanded') === 'true';
    btn.setAttribute('aria-expanded', open ? 'false' : 'true');
    panel.hidden = open;
    btn.querySelector('.label').textContent = open ? 'View Details' : 'Hide Details';
  });

  // Sort itineraries on a nation page
  var sort = document.querySelector('[data-sort]');
  if (sort) {
    var list = document.querySelector(sort.dataset.sort);
    var cards = Array.prototype.slice.call(list.children);
    sort.addEventListener('change', function () {
      var mode = sort.value;
      cards.slice().sort(function (a, b) {
        if (mode === 'price-asc') return a.dataset.price - b.dataset.price;
        if (mode === 'price-desc') return b.dataset.price - a.dataset.price;
        if (mode === 'duration') return a.dataset.nights - b.dataset.nights;
        return a.dataset.order - b.dataset.order;
      }).forEach(function (c) { list.appendChild(c); });
    });
  }

  // Hero search: jumps to the chosen nation's itineraries (front-end only for now)
  var form = document.querySelector('[data-search-form]');
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var dest = form.elements['nation'].value;
      if (dest) { window.location.href = dest + '#itineraries'; return; }
      document.getElementById('destinations').scrollIntoView({ behavior: 'smooth' });
      showToast('Pick a nation to start your search.');
    });
  }
})();
</script>

<script>
/* ---- Gallery lightbox ---- */
(function () {
  var thumbsAll = document.querySelectorAll('.gallery-thumb');
  if (!thumbsAll.length) return;

  var lightboxes = {};
  document.querySelectorAll('.lightbox').forEach(function (lb) { lightboxes[lb.dataset.lightbox] = lb; });
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
  function openLightbox(key, index) {
    var lb = lightboxes[key];
    if (!lb) return;
    current.key = key;
    current.thumbs = Array.prototype.slice.call(document.querySelectorAll('.gallery[data-gallery="' + key + '"] .gallery-thumb'));
    current.index = index;
    showSlide();
    lb.classList.add('is-open');
    lb.setAttribute('aria-hidden', 'false');
    document.body.classList.add('lightbox-open');
    lb.querySelector('.lightbox-close').focus();
  }
  function closeLightbox() {
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

  document.querySelectorAll('.gallery').forEach(function (galleryEl) {
    var key = galleryEl.dataset.gallery;
    Array.prototype.slice.call(galleryEl.querySelectorAll('.gallery-thumb')).forEach(function (btn, idx) {
      btn.addEventListener('click', function () { openLightbox(key, idx); });
    });
  });
  document.querySelectorAll('.lightbox').forEach(function (lb) {
    lb.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
    lb.querySelector('.lightbox-backdrop').addEventListener('click', closeLightbox);
    lb.querySelector('.lightbox-prev').addEventListener('click', function () { step(-1); });
    lb.querySelector('.lightbox-next').addEventListener('click', function () { step(1); });
  });
  document.addEventListener('keydown', function (e) {
    if (!current.key) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') step(-1);
    if (e.key === 'ArrowRight') step(1);
  });
})();
</script>
@yield('scripts')
</body>
</html>