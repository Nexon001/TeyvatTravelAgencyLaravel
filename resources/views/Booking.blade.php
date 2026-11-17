@extends('layouts.app')

@section('title', 'Book your stay')
@section('description', 'Choose a 2 to 6 day stay (or a custom length) and pick your dates on the live availability calendar.')
@section('body-class', 'chapter-profile')

@php
    // Same cache-busting idea as the layout's $jsUrl (that closure isn't visible inside child sections).
    $versioned = fn (string $path) => asset($path).(file_exists(public_path($path)) ? '?v='.filemtime(public_path($path)) : '');
    $minDays = (int) config('booking.min_days');
    $maxPackage = (int) config('booking.max_package_days');
    $maxCustom = (int) config('booking.max_custom_days');
    $nightsLabel = fn (int $days) => ($days - 1).' '.(($days - 1) === 1 ? 'night' : 'nights');
@endphp

@push('head')
<link rel="stylesheet" href="{{ $versioned('css/booking.css') }}">
@endpush

@section('content')
<section class="bk-hero">
  <div class="container">
    {{-- Goes back to the page the traveller came from (script in booking.js); plain link to the home page otherwise. --}}
    <a class="btn btn-ghost-light btn-pill btn-sm bk-back" id="bk-back" href="{{ route('home') }}">
      <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>Back
    </a>
    <h1>Plan your stay</h1>
    <p>Choose a nation and itinerary, then your dates. Orange days have one or two slots left while red days are fully booked.</p>
  </div>
</section>

<div class="container bk-page no-js" id="booking-app" data-config="{{ json_encode($config) }}">
  <noscript>
    <div class="contact-alert contact-alert--err">
      Booking needs JavaScript. Please turn it on, or email us at
      <a href="mailto:{{ config('app.desk_email') }}">{{ config('app.desk_email') }}</a>.
    </div>
  </noscript>

  <form class="bk-form" id="bk-form" action="{{ route('booking.store') }}" method="POST">
    @csrf
    <input type="hidden" name="check_in" id="bk-check-in">
    <input type="hidden" name="days" id="bk-days">

    <div class="bk-steps bk-layout" id="bk-steps">

      {{-- 1. Nation + itinerary --}}
      <section class="bk-card bk-where" aria-labelledby="bk-where-title">
        <div class="bk-card__head">
          <h2 id="bk-where-title">Where to?</h2>
          <p class="bk-note">Choose a nation</p>
        </div>

        <div class="bk-nation-grid" role="radiogroup" aria-labelledby="bk-where-title">
          @foreach ($nations as $slug => $n)
            <label class="bk-nation">
              <input type="radio" name="nation" value="{{ $slug }}" data-name="{{ $n['name'] }}" @checked($preselect['nation'] === $slug)>
              <span class="bk-nation__body">
                <img src="{{ asset('images/nations/'.$slug.'-hero.jpg') }}" alt="" loading="lazy">
                <strong>{{ $n['name'] }}</strong>
                <small>{{ $n['keywords'] }}</small>
              </span>
            </label>
          @endforeach
        </div>

        <div class="bk-trips" id="bk-trips">
          <h3 class="bk-trips__title">Itinerary</h3>
          <p class="bk-where-empty" id="bk-trips-empty">Pick a nation to see its itineraries.</p>

          @foreach ($nations as $slug => $n)
            <div class="bk-trip-group" data-nation="{{ $slug }}" role="radiogroup" aria-label="{{ $n['name'] }} itineraries" hidden>
              @foreach ($n['itineraries'] as $i => $it)
                <label class="bk-trip">
                  <input type="radio" name="itinerary" value="{{ $slug }}-{{ $i }}" data-nation="{{ $slug }}" data-days="{{ $it['days'] }}" data-title="{{ $it['title'] }}" @checked($preselect['itinerary'] === $slug.'-'.$i)>
                  <span class="bk-trip__body">
                    <img src="{{ asset($it['image']) }}" alt="" loading="lazy">
                    <span class="bk-trip__text">
                      @if (!empty($it['signature']))
                        <span class="bk-trip__ribbon">Signature</span>
                      @endif
                      <strong>{{ $it['title'] }}</strong>
                      <small>{{ $it['days'] }} {{ $it['days'] === 1 ? 'day' : 'days' }} / {{ $it['nights'] }} {{ $it['nights'] === 1 ? 'night' : 'nights' }}</small>
                      <small class="bk-trip__price">&#8369;{{ number_format($it['price']) }} <span>per person</span></small>
                    </span>
                  </span>
                </label>
              @endforeach
            </div>
          @endforeach

          <label class="bk-trip bk-trip--custom" id="bk-trip-custom" hidden>
            <input type="radio" name="itinerary" value="custom" data-title="Custom route">
            <span class="bk-trip__body">
              <span class="bk-trip__icon" aria-hidden="true"><svg class="icon"><use href="#i-compass"/></svg></span>
              <span class="bk-trip__text">
                <strong>Custom route</strong>
                <small>Our desk plans it with you. Pick your own stay length.</small>
              </span>
            </span>
          </label>
        </div>
      </section>

      <div class="bk-flow">

      {{-- 2. Stay length --}}
      <section class="bk-card bk-length" aria-labelledby="bk-length-title">
        <div class="bk-card__head">
          <h2 id="bk-length-title">How long will you stay?</h2>
          <p class="bk-note" id="bk-length-note" data-default="Packages: {{ $minDays }} to {{ $maxPackage }} days">Packages: {{ $minDays }} to {{ $maxPackage }} days</p>
        </div>

        <div class="bk-stay-grid" role="radiogroup" aria-labelledby="bk-length-title">
          @foreach (range($minDays, $maxPackage) as $days)
            <label class="bk-opt">
              <input type="radio" name="stay" value="{{ $days }}" @checked($days === 3)>
              <span class="bk-opt__body">
                <strong>{{ $days }} days</strong>
                <span>{{ $nightsLabel($days) }}</span>
              </span>
            </label>
          @endforeach
          @if ($maxCustom > $maxPackage)
            <label class="bk-opt">
              <input type="radio" name="stay" value="custom">
              <span class="bk-opt__body">
                <strong>Custom</strong>
                <span>A little longer</span>
              </span>
            </label>
          @endif
        </div>

        @if ($maxCustom > $maxPackage)
          <div class="bk-custom" id="bk-custom">
            <label for="bk-custom-days">Number of days</label>
            <div class="bk-stepper">
              <button type="button" data-step="-1" aria-label="One day fewer" disabled>&minus;</button>
              <input type="number" id="bk-custom-days" inputmode="numeric" min="{{ $maxPackage + 1 }}" max="{{ $maxCustom }}" value="{{ $maxPackage + 1 }}" disabled aria-describedby="bk-custom-hint">
              <button type="button" data-step="1" aria-label="One day more" disabled>+</button>
            </div>
            <p class="bk-hint" id="bk-custom-hint">Over {{ $maxPackage }} days is confirmed by our team ({{ $maxPackage + 1 }} to {{ $maxCustom }} days).</p>
          </div>
        @endif
      </section>

      <div class="bk-main">

        {{-- 3. Calendar --}}
        <section class="bk-card bk-calendar" aria-labelledby="bk-month-title">
          <div class="bk-cal-head">
            <button type="button" class="bk-nav-btn" id="bk-prev" aria-label="Previous month">
              <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
            </button>
            <h2 class="bk-cal-title" id="bk-month-title" aria-live="polite">&nbsp;</h2>
            <button type="button" class="bk-nav-btn" id="bk-next" aria-label="Next month">
              <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
            </button>
          </div>

          <div class="bk-dow" aria-hidden="true">
            <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
          </div>
          <div class="bk-grid" id="bk-grid" role="group" aria-labelledby="bk-month-title" aria-busy="true">
            <p class="bk-grid-fallback">Loading availability&hellip; If nothing appears, refresh the page or email <a href="mailto:{{ config('app.desk_email') }}">{{ config('app.desk_email') }}</a>.</p>
          </div>
          <p class="bk-load-error" id="bk-load-error" role="alert" hidden>
            We couldn't load availability. <button type="button" class="bk-link" id="bk-retry">Try again</button>
          </p>

          <ul class="bk-legend" aria-label="Calendar key">
            <li><span class="bk-dot bk-dot--ok"></span>Open</li>
            <li><span class="bk-dot bk-dot--warn"></span>1 to 2 slots left</li>
            <li><span class="bk-dot bk-dot--full"></span>Fully booked</li>
            <li><span class="bk-swatch bk-swatch--in">In</span>Check-in</li>
            <li><span class="bk-swatch bk-swatch--out">Out</span>Check-out</li>
            <li><span class="bk-swatch bk-swatch--range"></span>Your stay</li>
          </ul>
        </section>

        <aside class="bk-side">
          {{-- 4. Stay summary --}}
          <section class="bk-card bk-summary" aria-labelledby="bk-sum-title">
            <h2 id="bk-sum-title">Your stay</h2>
            <p class="bk-sum-length" id="sum-length">3 days, 2 nights</p>
            <span class="bk-badge bk-badge--idle" id="sum-badge">Choose a check-in day</span>
            <dl class="bk-sum-dates">
              <div class="bk-sum-dest"><dt>Destination</dt><dd id="sum-dest">&mdash;</dd></div>
              <div><dt>Check-in</dt><dd id="sum-in">&mdash;</dd></div>
              <div><dt>Check-out</dt><dd id="sum-out">&mdash;</dd></div>
            </dl>
            <div class="bk-sum-msg" id="sum-msg" role="status" aria-live="polite"></div>
          </section>

          {{-- 5. Details --}}
          <section class="bk-card bk-details" id="details" aria-labelledby="bk-details-title">
            <h2 id="bk-details-title">Your details</h2>
            <div class="bk-fields">
              {{-- Honeypot: people never see this; bots fill it in and are turned away. --}}
              <div class="contact-hp" aria-hidden="true">
                <label for="bk-extra">Leave this empty</label>
                <input id="bk-extra" name="bk_extra" type="text" tabindex="-1" autocomplete="off">
              </div>
              <div class="contact-field">
                <label for="bk-name">Full name</label>
                <input id="bk-name" name="name" type="text" autocomplete="name" maxlength="120" required>
                <span class="contact-error" id="bk-err-name" data-error-for="name"></span>
              </div>
              <div class="contact-field">
                <label for="bk-email">Email</label>
                <input id="bk-email" name="email" type="email" autocomplete="email" inputmode="email" maxlength="255" required>
                <span class="contact-error" id="bk-err-email" data-error-for="email"></span>
              </div>
              <div class="contact-field">
                <label for="bk-phone">Phone</label>
                <input id="bk-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" maxlength="30" pattern="[0-9+\(\)\s.\-]{7,30}" title="Digits, spaces, + or - (7 to 30 characters)" required>
                <span class="contact-error" id="bk-err-phone" data-error-for="phone"></span>
              </div>
            </div>
            <div class="contact-alert contact-alert--err" id="bk-form-error" role="alert" hidden></div>
            <button type="submit" class="btn btn-gold btn-lg btn-block" id="bk-submit" disabled>Confirm booking</button>
            <p class="bk-fine">Our team will use these details to reach you about your stay.</p>
          </section>
        </aside>
      </div>
      </div>{{-- /.bk-flow --}}
    </div>

    {{-- Confirmation --}}
    <section class="bk-card bk-done" id="bk-done" aria-labelledby="bk-done-title" tabindex="-1" hidden>
      <span class="bk-done__icon" aria-hidden="true"><svg class="icon" viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>
      <h2 id="bk-done-title">Booking received</h2>
      <p class="bk-done__lead" id="done-lead"></p>
      <dl class="bk-done__grid">
        <div><dt>Reference</dt><dd id="done-ref"></dd></div>
        <div><dt>Destination</dt><dd id="done-dest"></dd></div>
        <div><dt>Stay</dt><dd id="done-length"></dd></div>
        <div><dt>Check-in</dt><dd id="done-in"></dd></div>
        <div><dt>Check-out</dt><dd id="done-out"></dd></div>
      </dl>
      <div class="bk-done__actions">
        <a class="btn btn-gold" href="{{ route('home') }}">Back to home</a>
        <button type="button" class="btn btn-outline" id="bk-again">Book another stay</button>
      </div>
    </section>

    {{-- Mobile summary bar (hidden on wide screens by CSS) --}}
    <div class="bk-bar" id="bk-bar">
      <div class="bk-bar__text">
        <strong id="bar-title">3 days, 2 nights</strong>
        <span id="bar-sub">Choose a check-in day</span>
      </div>
      <button type="button" class="btn btn-gold" id="bar-btn" disabled>Continue</button>
    </div>
  </form>
</div>
@endsection

@section('scripts')
<script src="{{ $versioned('js/booking.js') }}" defer></script>
@endsection
