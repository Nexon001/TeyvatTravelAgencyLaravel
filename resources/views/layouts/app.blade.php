@php
  $navActive = fn(string $routeName) => request()->routeIs($routeName) ? 'aria-current="page"' : '';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title') | Teyvat Travel Co.</title>
  <meta name="description" content="@yield('description')">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body class="@yield('body-class')">
  <a class="skip-link" href="#main">Skip to content</a>
  <header class="site-header">
    <div class="container nav-row">
      <a class="brand" href="{{ route('home') }}">
        <svg aria-hidden="true" fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
          <circle cx="24" cy="24" r="21" stroke="#f8f3e4" stroke-width="2"></circle>
          <path d="M24 10 L28 22 L24 38 L20 22 Z" fill="#a1791f"></path>
          <circle cx="24" cy="24" fill="#f8f3e4" r="3"></circle>
        </svg>
        <span>Teyvat Travel Co.<span class="co">Journeys through the Seven Nations</span></span>
      </a>
      <button aria-expanded="false" aria-label="Toggle navigation" class="nav-toggle" type="button">
        <span></span>
      </button>
      <nav aria-label="Primary" class="site-nav">
        <ul>
          <li><a href="{{ route('home') }}" {!! $navActive('home') !!}>Company Profile</a></li>
          <li class="has-dropdown">
            <a href="javascript:void(0);" role="button" aria-haspopup="true">Destinations<span
                class="dropdown-caret">▾</span></a>
            <ul class="dropdown">
              <li><a href="{{ route('mondstadt') }}" {!! $navActive('mondstadt') !!}>Mondstadt</a></li>
              <li><a href="{{ route('liyue') }}" {!! $navActive('liyue') !!}>Liyue</a></li>
              <li><a href="{{ route('inazuma') }}" {!! $navActive('inazuma') !!}>Inazuma</a></li>
              <li><a href="{{ route('sumeru') }}" {!! $navActive('sumeru') !!}>Sumeru</a></li>
              <li><a href="{{ route('fontaine') }}" {!! $navActive('fontaine') !!}>Fontaine</a></li>
              <li><a href="{{ route('natlan') }}" {!! $navActive('natlan') !!}>Natlan</a></li>
              <li><a href="{{ route('snezhnaya') }}" {!! $navActive('snezhnaya') !!}>Snezhnaya</a></li>
            </ul>
          </li>
        </ul>
      </nav>
    </div>
  </header>
  <main id="main">
    @yield('content')
  </main>
  <footer class="site-footer">
    <div class="container footer-grid">
      <div>
        <h4>Teyvat Travel Co.</h4>
        <p style="max-width:34ch; color:var(--ink-soft); font-size:.92rem;">Independent, itinerary-first travel planning
          across all seven nations — one desk, one director, one dossier per traveller.</p>
      </div>
      <div>
        <h4>Destinations</h4>
        <ul>
          <li><a href="{{ route('mondstadt') }}">Mondstadt</a></li>
          <li><a href="{{ route('liyue') }}">Liyue</a></li>
          <li><a href="{{ route('inazuma') }}">Inazuma</a></li>
          <li><a href="{{ route('sumeru') }}">Sumeru</a></li>
          <li><a href="{{ route('fontaine') }}">Fontaine</a></li>
          <li><a href="{{ route('natlan') }}">Natlan</a></li>
          <li><a href="{{ route('snezhnaya') }}">Snezhnaya</a></li>
        </ul>
      </div>
      <div>
        <h4>Agency</h4>
        <ul>
          <li><a href="{{ route('home') }}">Company profile</a></li>
          <li><a href="{{ route('home') }}#director">About the Director</a></li>
          <li><a href="mailto:desk@teyvattravel.co">desk@teyvattravel.co</a></li>
        </ul>
      </div>
    </div>
    <div class="container footer-bottom">
      <span>© 2026 Teyvat Travel Co. All rights reserved.</span>
      <span>Sole proprietorship · est. Mondstadt</span>
    </div>
  </footer>
  <script>
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

      toggle.addEventListener('click', function () {
        setOpen(!nav.classList.contains('is-open'));
      });

      // close after tapping any link in the menu
      nav.addEventListener('click', function (e) {
        if (e.target.closest('a')) setOpen(false);
      });

      // close on Escape or when tapping outside the header
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') setOpen(false);
      });
      document.addEventListener('click', function (e) {
        if (nav.classList.contains('is-open') && !e.target.closest('.site-header')) setOpen(false);
      });

      // reset if the window is resized up to desktop width
      window.matchMedia('(min-width: 861px)').addEventListener('change', function (m) {
        if (m.matches) setOpen(false);
      });
    })();
  </script>
  @yield('scripts')
</body>

</html>