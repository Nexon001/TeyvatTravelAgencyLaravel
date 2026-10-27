@php
    $nationRoutes = ['mondstadt', 'liyue', 'inazuma', 'sumeru', 'fontaine', 'natlan', 'snezhnaya'];
    $navActive = fn (string ...$names) => request()->routeIs(...$names) ? 'aria-current="page"' : '';
    // /js/<name>.js with ?v=<last-modified> so edited files are never served from a stale browser cache
    $jsUrl = fn (string $name) => asset('js/'.$name.'.js')
        .(file_exists(public_path('js/'.$name.'.js')) ? '?v='.filemtime(public_path('js/'.$name.'.js')) : '');
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
          <button type="button" class="nav-trigger" aria-haspopup="true" aria-expanded="false" {!! $navActive(...$nationRoutes) !!}>Destinations <svg class="icon caret"><use href="#i-chevron"/></svg></button>
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

{{-- Scripts: one file per feature in public/js. toast.js must load first (others call Teyvat.toast). --}}
@foreach (['toast', 'nav', 'actions', 'itineraries', 'hero', 'lightbox'] as $script)
<script src="{{ $jsUrl($script) }}" defer></script>
@endforeach
@yield('scripts')
</body>
</html>