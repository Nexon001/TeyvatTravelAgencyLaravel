@extends('layouts.app')

@section('title', 'Admin dashboard')
@section('description', 'Administration area.')
@section('body-class', 'chapter-profile')

@php
    $versioned = fn (string $path) => asset($path).(file_exists(public_path($path)) ? '?v='.filemtime(public_path($path)) : '');
@endphp

@push('head')
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="{{ $versioned('css/auth.css') }}">
<style>
  /* Match the admin area to the site's navy, cream, and gold palette. */
  .auth-page > .container{ max-width:none; padding-inline:clamp(12px,1.5vw,24px); }
  .admin-greeting{
    display:flex; grid-column:1/-1; align-items:center; padding-bottom:18px;
    border-bottom:1px solid rgba(250,247,241,.18);
  }
  .admin-greeting .admin-title{ margin:0; color:var(--cream); font-size:clamp(1.5rem,2.2vw,1.8rem); }
  .admin-workspace{
    display:grid; grid-template-columns:minmax(220px,260px) minmax(0,1fr);
    column-gap:clamp(24px,3vw,42px); row-gap:20px;
    min-height:clamp(560px,calc(100vh - 300px),760px);
    margin-top:0; padding:clamp(24px,3vw,42px);
    background:radial-gradient(circle at 100% 0,rgba(217,165,72,.16),transparent 30%),linear-gradient(145deg,var(--navy),var(--navy-deep));
    border:1px solid rgba(217,165,72,.28); border-radius:20px;
    box-shadow:0 20px 48px rgba(22,27,39,.2);
  }
  .admin-sidebar{ display:flex; flex-direction:column; gap:18px; padding:8px 24px 8px 0; border-right:1px solid rgba(250,247,241,.18); }
  .admin-sidebar__label{ margin:0; color:var(--gold); font-size:.68rem; font-weight:700; letter-spacing:.14em; }
  .admin-tabs{
    display:flex; flex-direction:column; gap:12px;
  }
  .admin-tab{
    display:flex; align-items:center; gap:11px; width:100%; min-height:52px; padding:12px 14px;
    border:1px solid rgba(250,247,241,.22); border-radius:12px;
    background:rgba(255,255,255,.06); color:rgba(250,247,241,.78);
    font:600 .88rem var(--font-body); text-align:left; cursor:pointer;
    transition:background-color .2s ease, border-color .2s ease, color .2s ease, transform .2s ease, box-shadow .2s ease;
  }
  .admin-tab:hover{
    transform:translateY(-2px); border-color:rgba(217,165,72,.65);
    background:rgba(217,165,72,.12); color:var(--cream);
  }
  .admin-tab[aria-selected="true"]{
    transform:translateX(3px); border-color:var(--gold);
    background:linear-gradient(145deg,#e8bd67,#ce963e); color:var(--navy-deep);
    box-shadow:0 7px 18px rgba(217,165,72,.2);
  }
  .admin-sidebar__note{ margin:auto 0 0; color:rgba(250,247,241,.55); font-size:.74rem; line-height:1.5; }
  .admin-content{ display:flex; min-width:0; }
  .admin-panel{ display:flex; flex:1; flex-direction:column; width:100%; min-height:420px; padding:24px 0; }
  .admin-panel[hidden]{ display:none; }
  .admin-panel--active{ animation:admin-panel-enter .24s ease-out both; }
  .admin-panel--active > .admin-card,
  .admin-panel--active .admin-inquiry-layout > .admin-card,
  .admin-panel--active .admin-stat-card,
  .admin-panel--active .admin-chart-card{
    animation:admin-card-enter .42s cubic-bezier(.2,.75,.25,1) both;
  }
  .admin-panel--active .admin-inquiry-layout > .admin-card:nth-child(2),
  .admin-panel--active .admin-chart-card:nth-child(2){ animation-delay:.08s; }
  @keyframes admin-panel-enter{
    from{ opacity:0; transform:translateY(8px); }
    to{ opacity:1; transform:translateY(0); }
  }
  @keyframes admin-card-enter{
    from{ opacity:0; transform:translateY(18px) scale(.985); }
    to{ opacity:1; transform:translateY(0) scale(1); }
  }
  .admin-panel > .admin-panel__heading{ margin:0 0 5px; color:var(--cream); font-family:var(--font-display); font-size:1.35rem; }
  .admin-home-heading .admin-panel__heading{ color:var(--cream); }
  .admin-inquiry-empty .admin-panel__heading{ color:var(--ink); }
  .admin-panel__intro{ margin:0 0 20px; color:rgba(250,247,241,.72); font-size:.9rem; }
  .admin-home-heading{ display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:12px; }
  .admin-stat-grid{ display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:20px; }
  .admin-stat-card,.admin-chart-card{
    padding:18px; border:1px solid rgba(217,165,72,.24); border-top:3px solid var(--gold);
    border-radius:var(--radius); background:linear-gradient(145deg,#fff,#fbf7ef);
    box-shadow:0 14px 30px rgba(10,14,22,.16); color:var(--ink);
  }
  .admin-stat-card{ display:grid; min-height:128px; gap:11px; padding:22px; }
  .admin-stat-card__label{ color:var(--ink-soft); font-size:.78rem; font-weight:600; }
  .admin-stat-card__value{ color:var(--navy); font-family:var(--font-display); font-size:1.7rem; font-weight:700; line-height:1; }
  .admin-stat-card__hint{ color:var(--ink-soft); font-size:.72rem; }
  .admin-home-charts{ display:grid; flex:1; grid-template-columns:minmax(0,1.1fr) minmax(0,.9fr); gap:22px; margin-top:22px; }
  .admin-chart-card{ display:flex; min-width:0; min-height:340px; flex-direction:column; padding:24px; }
  .admin-chart-card__heading{ margin:0; color:var(--ink); font-family:var(--font-display); font-size:1.05rem; }
  .admin-chart-card__caption{ margin:4px 0 18px; color:var(--ink-soft); font-size:.76rem; }
  .admin-chart-bars{ display:grid; flex:1; grid-template-columns:repeat(6,minmax(0,1fr)); gap:14px; min-height:230px; align-items:stretch; }
  .admin-chart-column{ display:flex; min-width:0; flex-direction:column; align-items:center; justify-content:flex-end; gap:7px; }
  .admin-chart-column__value{ color:var(--ink-soft); font-size:.66rem; font-variant-numeric:tabular-nums; }
  .admin-chart-column__track{ display:flex; flex:1; width:100%; align-items:flex-end; overflow:hidden; border-radius:7px 7px 3px 3px; background:var(--cream); }
  .admin-chart-column__bar{ width:100%; height:var(--bar-height); border-radius:7px 7px 3px 3px; background:linear-gradient(180deg,#efbd5d,var(--gold-deep)); }
  .admin-chart-column__month{ color:var(--ink-soft); font-size:.66rem; }
  .admin-ranking{ display:grid; flex:1; align-content:space-between; gap:20px; margin:0; padding:0; list-style:none; }
  .admin-ranking__heading{ display:flex; justify-content:space-between; gap:12px; margin-bottom:7px; color:var(--ink); font-size:.76rem; }
  .admin-ranking__heading strong{ flex:none; color:var(--gold-deep); font-variant-numeric:tabular-nums; }
  .admin-ranking__track{ height:8px; overflow:hidden; border-radius:999px; background:var(--cream); }
  .admin-ranking__bar{ display:block; width:var(--rank-width); height:100%; border-radius:inherit; background:linear-gradient(90deg,var(--gold),var(--gold-deep)); }
  .admin-card{
    padding:22px; background:linear-gradient(145deg,#fff,#fbf7ef);
    border:1px solid rgba(217,165,72,.24); border-top:3px solid var(--gold);
    border-radius:var(--radius); box-shadow:0 14px 30px rgba(10,14,22,.16);
  }
  .admin-panel > .admin-card{ display:flex; flex:1; flex-direction:column; }
  .admin-form-grid{ display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
  .admin-field{ display:grid; gap:7px; color:var(--ink-soft); font-size:.82rem; font-weight:600; }
  .admin-field--wide{ grid-column:1/-1; }
  .admin-field input,.admin-field select,.admin-field textarea{
    width:100%; min-height:44px; padding:10px 12px; border:1px solid rgba(28,33,48,.15);
    border-radius:var(--radius-sm); background:var(--bg); color:var(--ink);
    font:400 .9rem var(--font-body);
  }
  .admin-field textarea{ min-height:120px; resize:vertical; }
  .admin-field input:focus-visible,.admin-field select:focus-visible,.admin-field textarea:focus-visible{
    border-color:var(--gold-deep); outline:2px solid rgba(217,165,72,.28); outline-offset:1px;
  }
  .admin-form-actions{ display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-top:18px; }
  .admin-submit{
    padding:11px 17px; border:1px solid var(--gold-deep); border-radius:999px; background:var(--gold);
    color:var(--navy-deep); font:700 .86rem var(--font-body); cursor:pointer;
    transition:transform .18s ease, background-color .18s ease, box-shadow .18s ease;
  }
  .admin-submit:hover{ transform:translateY(-2px); background:#e5b95f; box-shadow:0 6px 16px rgba(185,133,43,.24); }
  .admin-form-status{ margin:0; color:var(--ink-soft); font-size:.82rem; }
  .admin-empty{
    margin:18px 0 0; padding:18px; border:1px dashed rgba(185,133,43,.4); border-radius:var(--radius-sm);
    background:var(--cream); color:var(--ink-soft); font-size:.88rem;
  }
  .admin-table-wrap{ flex:1; margin-top:18px; overflow-x:auto; border:1px solid var(--line); border-radius:var(--radius-sm); }
  .admin-table{ width:100%; height:100%; border-collapse:collapse; text-align:left; font-size:.86rem; }
  .admin-table th,.admin-table td{ padding:12px 14px; border-bottom:1px solid var(--line); white-space:nowrap; }
  .admin-table th{ background:var(--navy); color:var(--cream); font-size:.76rem; }
  .admin-table tr:last-child td{ border-bottom:0; }
  .admin-inquiry-layout{ display:grid; flex:1; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
  .admin-inquiry-empty{ display:grid; align-content:center; min-height:250px; }
  .admin-note{ margin:0 0 16px; color:var(--ink-soft); font-size:.85rem; }

  @media (max-width:980px){
    .admin-workspace{ grid-template-columns:190px minmax(0,1fr); gap:16px 20px; min-height:0; padding:18px; }
    .admin-home-charts{ flex:initial; }
    .admin-stat-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); }
    .admin-home-charts{ grid-template-columns:1fr; }
  }
  @media (max-width:640px){
    .auth-page > .container{ max-width:100%; padding-inline:18px; }
    .admin-greeting{ padding:0; border-bottom:0; }
    .admin-workspace{ display:flex; min-height:0; flex-direction:column; gap:16px; padding:14px 14px 18px; border-radius:16px; }
    .admin-sidebar{ gap:10px; padding:0 0 14px; border-right:0; border-bottom:1px solid rgba(250,247,241,.18); }
    .admin-content{ display:block; }
    .admin-panel{ min-height:0; }
    .admin-panel > .admin-card,.admin-inquiry-layout{ flex:initial; }
    .admin-tabs{ display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; }
    .admin-tab{ min-height:42px; padding:8px; font-size:.78rem; }
    .admin-card{ padding:17px; }
    .admin-form-grid,.admin-inquiry-layout{ grid-template-columns:1fr; }
    .admin-chart-card,.admin-stat-card{ min-height:0; padding:17px; }
    .admin-chart-bars{ gap:6px; height:170px; }
    .admin-field--wide{ grid-column:auto; }
  }
  @media (prefers-reduced-motion:reduce){
    .admin-panel--active,.admin-panel--active > .admin-card,
    .admin-panel--active .admin-inquiry-layout > .admin-card,
    .admin-panel--active .admin-stat-card,.admin-panel--active .admin-chart-card{ animation:none; }
    .admin-tab,.admin-submit{ transition:none; }
  }
</style>
@endpush

@section('content')
<section class="auth-page">
  <div class="container">
    {{-- Sidebar tabs switch between the home dashboard and admin work areas. --}}
    <div class="admin-workspace">
      <header class="admin-greeting">
        <h1 class="admin-title">Hello Director!</h1>
      </header>

      <aside class="admin-sidebar">
        <p class="admin-sidebar__label">WORKSPACE</p>
        <div class="admin-tabs" role="tablist" aria-label="Admin sections" aria-orientation="vertical">
          <button class="admin-tab" id="admin-tab-home" type="button" role="tab" aria-controls="admin-panel-home" aria-selected="true" tabindex="0" data-admin-tab="home">Home</button>
          <button class="admin-tab" id="admin-tab-bookings" type="button" role="tab" aria-controls="admin-panel-bookings" aria-selected="false" tabindex="-1" data-admin-tab="bookings">Bookings</button>
          <button class="admin-tab" id="admin-tab-itineraries" type="button" role="tab" aria-controls="admin-panel-itineraries" aria-selected="false" tabindex="-1" data-admin-tab="itineraries">Itineraries</button>
          <button class="admin-tab" id="admin-tab-inquiries" type="button" role="tab" aria-controls="admin-panel-inquiries" aria-selected="false" tabindex="-1" data-admin-tab="inquiries">Inquiries</button>
          <button class="admin-tab" id="admin-tab-customers" type="button" role="tab" aria-controls="admin-panel-customers" aria-selected="false" tabindex="-1" data-admin-tab="customers">Customers</button>
        </div>
        <p class="admin-sidebar__note">Travel operations overview.</p>
      </aside>

      <div class="admin-content">
        {{-- Demo overview charts and top itineraries. --}}
        <section class="admin-panel admin-panel--active" id="admin-panel-home" role="tabpanel" aria-labelledby="admin-tab-home" data-admin-panel="home">
          <div class="admin-home-heading">
            <div>
              <h2 class="admin-panel__heading">Home</h2>
              <p class="admin-panel__intro">Travel activity at a glance.</p>
            </div>
          </div>

          <div class="admin-stat-grid" data-count-up>
            <article class="admin-stat-card">
              <span class="admin-stat-card__label">Bookings</span>
              <strong class="admin-stat-card__value">1,284</strong>
              <span class="admin-stat-card__hint">All bookings</span>
            </article>
            <article class="admin-stat-card">
              <span class="admin-stat-card__label">Confirmed</span>
              <strong class="admin-stat-card__value">1,068</strong>
              <span class="admin-stat-card__hint">Confirmed status</span>
            </article>
            <article class="admin-stat-card">
              <span class="admin-stat-card__label">Travelers</span>
              <strong class="admin-stat-card__value">936</strong>
              <span class="admin-stat-card__hint">Registered accounts</span>
            </article>
            <article class="admin-stat-card">
              <span class="admin-stat-card__label">Itineraries</span>
              <strong class="admin-stat-card__value">21</strong>
              <span class="admin-stat-card__hint">Across seven nations</span>
            </article>
          </div>

          <div class="admin-home-charts">
            <section class="admin-chart-card">
              <h3 class="admin-chart-card__heading">Monthly bookings</h3>
              <p class="admin-chart-card__caption">Booking counts · January to June</p>
              <div class="admin-chart-bars" role="img" aria-label="Monthly bookings: January 42, February 61, March 54, April 78, May 69, June 92.">
                <div class="admin-chart-column" aria-hidden="true">
                  <span class="admin-chart-column__value">42</span>
                  <span class="admin-chart-column__track"><span class="admin-chart-column__bar" style="--bar-height:46%"></span></span>
                  <span class="admin-chart-column__month">Jan</span>
                </div>
                <div class="admin-chart-column" aria-hidden="true">
                  <span class="admin-chart-column__value">61</span>
                  <span class="admin-chart-column__track"><span class="admin-chart-column__bar" style="--bar-height:66%"></span></span>
                  <span class="admin-chart-column__month">Feb</span>
                </div>
                <div class="admin-chart-column" aria-hidden="true">
                  <span class="admin-chart-column__value">54</span>
                  <span class="admin-chart-column__track"><span class="admin-chart-column__bar" style="--bar-height:59%"></span></span>
                  <span class="admin-chart-column__month">Mar</span>
                </div>
                <div class="admin-chart-column" aria-hidden="true">
                  <span class="admin-chart-column__value">78</span>
                  <span class="admin-chart-column__track"><span class="admin-chart-column__bar" style="--bar-height:85%"></span></span>
                  <span class="admin-chart-column__month">Apr</span>
                </div>
                <div class="admin-chart-column" aria-hidden="true">
                  <span class="admin-chart-column__value">69</span>
                  <span class="admin-chart-column__track"><span class="admin-chart-column__bar" style="--bar-height:75%"></span></span>
                  <span class="admin-chart-column__month">May</span>
                </div>
                <div class="admin-chart-column" aria-hidden="true">
                  <span class="admin-chart-column__value">92</span>
                  <span class="admin-chart-column__track"><span class="admin-chart-column__bar" style="--bar-height:100%"></span></span>
                  <span class="admin-chart-column__month">Jun</span>
                </div>
              </div>
            </section>

            <section class="admin-chart-card">
              <h3 class="admin-chart-card__heading">Best-selling itineraries</h3>
              <p class="admin-chart-card__caption">Booking counts by itinerary</p>
              <ol class="admin-ranking">
                <li>
                  <div class="admin-ranking__heading"><span>The City of Freedom Expedition</span><strong>92</strong></div>
                  <div class="admin-ranking__track"><span class="admin-ranking__bar" style="--rank-width:100%"></span></div>
                </li>
                <li>
                  <div class="admin-ranking__heading"><span>The Harbor of Stone and Commerce</span><strong>78</strong></div>
                  <div class="admin-ranking__track"><span class="admin-ranking__bar" style="--rank-width:85%"></span></div>
                </li>
                <li>
                  <div class="admin-ranking__heading"><span>Five-Island Ferry Circuit</span><strong>64</strong></div>
                  <div class="admin-ranking__track"><span class="admin-ranking__bar" style="--rank-width:70%"></span></div>
                </li>
                <li>
                  <div class="admin-ranking__heading"><span>Festival Nights Package</span><strong>53</strong></div>
                  <div class="admin-ranking__track"><span class="admin-ranking__bar" style="--rank-width:58%"></span></div>
                </li>
                <li>
                  <div class="admin-ranking__heading"><span>Desert Caravan Expedition</span><strong>41</strong></div>
                  <div class="admin-ranking__track"><span class="admin-ranking__bar" style="--rank-width:45%"></span></div>
                </li>
              </ol>
            </section>
          </div>
        </section>

        {{-- Booking filters and table preview. --}}
        <section class="admin-panel" id="admin-panel-bookings" role="tabpanel" aria-labelledby="admin-tab-bookings" data-admin-panel="bookings" hidden>
        <h2 class="admin-panel__heading">Bookings</h2>
        <p class="admin-panel__intro">Find a booking and review its status.</p>
        <div class="admin-card">
          <form class="admin-form-grid" data-admin-demo-form>
            <label class="admin-field" for="booking-search">Booking reference or customer
              <input id="booking-search" name="search" type="search" placeholder="e.g. TT-1042 or name">
            </label>
            <label class="admin-field" for="booking-status">Booking status
              <select id="booking-status" name="status">
                <option value="">All statuses</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </label>
            <div class="admin-form-actions admin-field--wide">
              <button class="admin-submit" type="submit">Filter bookings</button>
              <p class="admin-form-status" role="status" aria-live="polite" data-admin-status hidden></p>
            </div>
          </form>
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead><tr><th>Reference</th><th>Customer</th><th>Stay</th><th>Status</th><th>Actions</th></tr></thead>
              <tbody><tr><td class="admin-empty" colspan="5">Booking records are coming soon.</td></tr></tbody>
            </table>
          </div>
        </div>
        </section>

      {{-- Itinerary editor form. --}}
      <section class="admin-panel" id="admin-panel-itineraries" role="tabpanel" aria-labelledby="admin-tab-itineraries" data-admin-panel="itineraries" hidden>
        <h2 class="admin-panel__heading">Itineraries</h2>
        <p class="admin-panel__intro">Draft a travel package with its destination and trip details.</p>
        <form class="admin-card" data-admin-demo-form>
          <div class="admin-form-grid">
            <label class="admin-field" for="itinerary-title">Package name
              <input id="itinerary-title" name="title" type="text" placeholder="A journey through Mondstadt" required>
            </label>
            <label class="admin-field" for="itinerary-nation">Destination
              <select id="itinerary-nation" name="nation" required>
                <option value="">Choose a nation</option>
                <option>Mondstadt</option><option>Liyue</option><option>Inazuma</option>
                <option>Sumeru</option><option>Fontaine</option><option>Natlan</option><option>Snezhnaya</option>
              </select>
            </label>
            <label class="admin-field" for="itinerary-days">Number of days
              <input id="itinerary-days" name="days" type="number" min="1" max="30" placeholder="5" required>
            </label>
            <label class="admin-field" for="itinerary-price">Starting price (₱ per person)
              <input id="itinerary-price" name="price" type="number" min="0" step="100" placeholder="12500" required>
            </label>
            <label class="admin-field admin-field--wide" for="itinerary-description">Package description
              <textarea id="itinerary-description" name="description" placeholder="Describe the trip highlights..." required></textarea>
            </label>
          </div>
          <div class="admin-form-actions">
            <button class="admin-submit" type="submit">Save itinerary</button>
            <p class="admin-form-status" role="status" aria-live="polite" data-admin-status hidden></p>
          </div>
        </form>
        </section>

      {{-- Inquiry inbox and reply composer. --}}
      <section class="admin-panel" id="admin-panel-inquiries" role="tabpanel" aria-labelledby="admin-tab-inquiries" data-admin-panel="inquiries" hidden>
        <h2 class="admin-panel__heading">Inquiries</h2>
        <p class="admin-panel__intro">Review contact messages and prepare a reply.</p>
        <div class="admin-inquiry-layout">
          <div class="admin-card admin-inquiry-empty">
            <h3 class="admin-panel__heading">Inbox</h3>
            <p class="admin-empty">The inquiry inbox and replies are coming soon.</p>
          </div>
          <form class="admin-card" data-admin-demo-form>
            <p class="admin-note">Reply composer</p>
            <div class="admin-form-grid">
              <label class="admin-field admin-field--wide" for="inquiry-email">Recipient email
                <input id="inquiry-email" name="email" type="email" placeholder="traveler@example.com" required>
              </label>
              <label class="admin-field admin-field--wide" for="inquiry-subject">Subject
                <input id="inquiry-subject" name="subject" type="text" placeholder="Re: Your travel inquiry" required>
              </label>
              <label class="admin-field admin-field--wide" for="inquiry-reply">Reply message
                <textarea id="inquiry-reply" name="reply" placeholder="Write a reply..." required></textarea>
              </label>
            </div>
            <div class="admin-form-actions">
              <button class="admin-submit" type="submit">Send reply</button>
              <p class="admin-form-status" role="status" aria-live="polite" data-admin-status hidden></p>
            </div>
          </form>
        </div>
        </section>

      {{-- Customer search and table preview. --}}
      <section class="admin-panel" id="admin-panel-customers" role="tabpanel" aria-labelledby="admin-tab-customers" data-admin-panel="customers" hidden>
        <h2 class="admin-panel__heading">Customers</h2>
        <p class="admin-panel__intro">Find a registered account. Customer details are view-only.</p>
        <div class="admin-card">
          <form class="admin-form-grid" data-admin-demo-form>
            <label class="admin-field" for="customer-search">Name or email
              <input id="customer-search" name="search" type="search" placeholder="Search customers">
            </label>
            <label class="admin-field" for="customer-role">Account type
              <select id="customer-role" name="role">
                <option value="">All account types</option>
                <option value="customer">Customer</option>
                <option value="admin">Administrator</option>
              </select>
            </label>
            <div class="admin-form-actions admin-field--wide">
              <button class="admin-submit" type="submit">Search customers</button>
              <p class="admin-form-status" role="status" aria-live="polite" data-admin-status hidden></p>
            </div>
          </form>
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead><tr><th>Name</th><th>Email</th><th>Account type</th><th>Joined</th></tr></thead>
              <tbody><tr><td class="admin-empty" colspan="4">Customer records are coming soon.</td></tr></tbody>
            </table>
          </div>
        </div>
        </section>
      </div>
    </div>
  </div>
</section>

<script>
  // Keep the tabs and panels in sync, including keyboard navigation.
  (function () {
    var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-admin-tab]'));
    var panels = Array.prototype.slice.call(document.querySelectorAll('[data-admin-panel]'));

    function activateTab(tab, moveFocus) {
      tabs.forEach(function (item) {
        var isSelected = item === tab;
        item.setAttribute('aria-selected', String(isSelected));
        item.tabIndex = isSelected ? 0 : -1;
      });

      panels.forEach(function (panel) {
        var isActive = panel.dataset.adminPanel === tab.dataset.adminTab;
        panel.hidden = !isActive;
        panel.classList.toggle('admin-panel--active', isActive);
      });

      if (moveFocus) {
        tab.focus();
      }
    }

    tabs.forEach(function (tab, index) {
      tab.addEventListener('click', function () {
        activateTab(tab, false);
      });

      tab.addEventListener('keydown', function (event) {
        var nextIndex = index;
        if (event.key === 'ArrowDown' || event.key === 'ArrowRight') {
          nextIndex = (index + 1) % tabs.length;
        } else if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') {
          nextIndex = (index - 1 + tabs.length) % tabs.length;
        } else if (event.key === 'Home') {
          nextIndex = 0;
        } else if (event.key === 'End') {
          nextIndex = tabs.length - 1;
        } else {
          return;
        }

        event.preventDefault();
        activateTab(tabs[nextIndex], true);
      });
    });

    // Preview forms show that no data is saved or sent yet.
    document.querySelectorAll('[data-admin-demo-form]').forEach(function (form) {
      form.addEventListener('submit', function (event) {
        event.preventDefault();
        var status = form.querySelector('[data-admin-status]');
        if (status) {
          status.textContent = 'This feature is coming soon. This form does not save or send data yet.';
          status.hidden = false;
        }
      });
    });
  })();
</script>
@endsection
