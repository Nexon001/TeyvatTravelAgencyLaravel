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
        <li><a href="{{ route('home') }}#faq">FAQ</a></li>
        <li><a href="{{ route('home') }}#contact">Contact</a></li>
      </ul>
      <div class="nav-actions">
        {{-- Backend hooks: these buttons fire a "teyvat:action" event (see script at the bottom). --}}
        <button type="button" class="btn btn-outline btn-pill btn-sm" data-action="login">Log in</button>
        <button type="button" class="btn btn-outline btn-pill btn-sm" data-action="signup">Sign up</button>
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
        <li><a href="{{ route('home') }}#faq">FAQs</a></li>
        <li><a href="{{ route('home') }}#contact">Contact us</a></li>
        <li><a href="mailto:{{ config('app.desk_email') }}">{{ config('app.desk_email') }}</a></li>
      </ul>
    </div>
  </div>
  <div class="container footer-bottom">
    <span>© 2026 Teyvat Travel Services. All rights reserved.</span>
    <span>Sole proprietorship · est. Mondstadt</span>
  </div>
</footer>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<dialog class="login-dialog" id="login-dialog" aria-labelledby="auth-title" aria-describedby="auth-description">
  <div class="login-dialog__content">
    <button class="login-dialog__close" id="auth-close" type="button" aria-label="Close login form" data-close-login>
      <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
    <h2 id="auth-title">Login</h2>
    <p class="login-dialog__intro" id="auth-description">Welcome back, traveler. Sign in to continue.</p>

    <section class="auth-view" id="login-view">
      <form class="login-form" id="login-form">
        <label class="login-field" for="login-username">Username</label>
        <div class="login-input-wrap">
          <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-4 2.8-6 7-6s7 2 7 6"/></svg>
          <input id="login-username" name="username" type="text" autocomplete="username" placeholder="Type your username" data-auth-focus required>
        </div>

        <label class="login-field" for="login-password">Password</label>
        <div class="login-input-wrap">
          <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
          <input id="login-password" name="password" type="password" autocomplete="current-password" placeholder="Type your password" required>
        </div>

        <div class="login-form__options">
          <button class="login-link" type="button" data-action="forgot-password">Forgot password?</button>
        </div>

        <button class="login-submit" type="submit">Login</button>
        <p class="login-feedback" id="login-feedback" role="status" aria-live="polite" hidden></p>
      </form>
      <div class="login-divider"><span>Or sign in using</span></div>
      <div class="login-socials" aria-label="Social sign-in options">
        <button class="login-social login-social--facebook" type="button" aria-label="Continue with Facebook" data-action="social-login" data-provider="Facebook">f</button>
        <button class="login-social login-social--twitter" type="button" aria-label="Continue with Twitter" data-action="social-login" data-provider="Twitter">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 5.9a8 8 0 0 1-2.36.65 4.12 4.12 0 0 0 1.8-2.27 8.22 8.22 0 0 1-2.6.99 4.1 4.1 0 0 0-7 3.74 11.64 11.64 0 0 1-8.45-4.28 4.1 4.1 0 0 0 1.27 5.47 4.06 4.06 0 0 1-1.86-.51v.05a4.1 4.1 0 0 0 3.29 4.02 4.1 4.1 0 0 1-1.85.07 4.11 4.11 0 0 0 3.83 2.85A8.24 8.24 0 0 1 2 18.38a11.62 11.62 0 0 0 6.29 1.84c7.55 0 11.68-6.25 11.68-11.68l-.01-.53A8.34 8.34 0 0 0 22 5.9Z"/></svg>
        </button>
        <button class="login-social login-social--google" type="button" aria-label="Continue with Google" data-action="social-login" data-provider="Google">G</button>
      </div>
      <p class="login-signup">Don't have an account? <button class="login-link" type="button" data-auth-switch="signup">Sign up</button></p>
      <div class="toast toast--dialog" id="auth-toast" role="status" aria-live="polite"></div>
    </section>

    <section class="auth-view" id="signup-view" hidden>
      <form class="login-form" id="signup-form">
        <label class="login-field" for="signup-name">Full name</label>
        <div class="login-input-wrap">
          <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-4 2.8-6 7-6s7 2 7 6"/></svg>
          <input id="signup-name" name="name" type="text" autocomplete="name" placeholder="Your full name" data-auth-focus required>
        </div>

        <label class="login-field" for="signup-email">Email</label>
        <div class="login-input-wrap">
          <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="5.5" width="17" height="13" rx="2.5"/><path d="m4 7 8 6 8-6"/></svg>
          <input id="signup-email" name="email" type="email" autocomplete="email" placeholder="you@example.com" required>
        </div>

        <label class="login-field" for="signup-username">Username</label>
        <div class="login-input-wrap">
          <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-4 2.8-6 7-6s7 2 7 6"/></svg>
          <input id="signup-username" name="username" type="text" autocomplete="username" placeholder="Choose a username" required>
        </div>

        <label class="login-field" for="signup-password">Password</label>
        <div class="login-input-wrap">
          <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
          <input id="signup-password" name="password" type="password" autocomplete="new-password" placeholder="Create a password" minlength="8" required>
        </div>

        <label class="login-field" for="signup-password-confirmation">Confirm password</label>
        <div class="login-input-wrap">
          <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
          <input id="signup-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Confirm your password" minlength="8" required>
        </div>

        <button class="login-submit" type="submit">Create account</button>
        <p class="login-feedback" id="signup-feedback" role="status" aria-live="polite" hidden></p>
      </form>
      <p class="login-signup">Already have an account? <button class="login-link" type="button" data-auth-switch="login">Log in</button></p>
      <div class="toast toast--dialog" id="signup-toast" role="status" aria-live="polite"></div>
    </section>
  </div>
</dialog>

{{-- Scripts: one file per feature in public/js. toast.js must load first (others call Teyvat.toast). --}}
@foreach (['toast', 'nav', 'actions', 'auth', 'itineraries', 'hero', 'lightbox', 'faq'] as $script)
<script src="{{ $jsUrl($script) }}" defer></script>
@endforeach
@yield('scripts')
</body>
</html>