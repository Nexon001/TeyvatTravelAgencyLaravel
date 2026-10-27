@extends('layouts.app')

@php $nations = include resource_path('data/nations.php'); @endphp

@section('title', 'Your Journey Across Teyvat')
@section('description', 'Teyvat Travel Services crafts handcrafted itineraries across all seven nations of Teyvat — Mondstadt, Liyue, Inazuma, Sumeru, Fontaine, Natlan and Snezhnaya.')
@section('body-class', 'chapter-profile')

@section('content')

{{-- HERO --}}
@php
    // Hero stats are computed from resources/data/nations.php, so they update themselves when data changes.
    $allTrips = collect($nations)->flatMap(fn ($n) => $n['itineraries']);
    $minDays  = $allTrips->min('days');
    $maxDays  = $allTrips->max('days');
@endphp
<section class="hero-landing" style="background-image:url('{{ asset('images/nations/mondstadt-hero.jpg') }}');">
    <div class="container hero-landing-inner">
        <p class="hero-kicker">Explore. Discover. Experience.</p>
        <h1>Your Journey Across Teyvat</h1>
        <p class="hero-sub">Handcrafted travel itineraries across the seven nations. Let us take you there.</p>

        <div class="hero-actions">
            <a class="btn btn-gold btn-lg" href="#destinations">Explore Destinations <svg class="icon"><use href="#i-arrow-right"/></svg></a>
            <a class="btn btn-ghost-light btn-lg" href="#itineraries">View Itineraries</a>
        </div>

        <ul class="hero-stats" data-count-up>
            <li><strong>{{ count($nations) }}</strong><span>Nations to explore</span></li>
            <li><strong>{{ $allTrips->count() }}</strong><span>Curated itineraries</span></li>
            <li><strong>{{ $minDays === $maxDays ? $minDays : $minDays.'–'.$maxDays }}</strong><span>Days per journey</span></li>
        </ul>
    </div>
</section>

{{-- THE SEVEN NATIONS --}}
<section class="section" id="destinations">
    <div class="container">
        <div class="section-head-row">
            <div>
                <span class="eyebrow">The Seven Nations</span>
                <h2>Discover the Wonders of Teyvat</h2>
            </div>
        </div>
        <div class="tile-grid">
            @foreach ($nations as $slug => $n)
                <a class="tile" href="{{ route($slug) }}">
                    <img src="{{ asset('images/nations/'.$slug.'-hero.jpg') }}" alt="{{ $n['name'] }}" loading="lazy">
                    <div class="tile-label">
                        <div>
                            <h3>{{ $n['name'] }}</h3>
                            <p>{{ $n['keywords'] }}</p>
                        </div>
                        <span class="tile-arrow"><svg class="icon"><use href="#i-arrow-right"/></svg></span>
                    </div>
                </a>
            @endforeach
            <div class="tile tile--cta">
                <h3>Can't decide?</h3>
                <p>Tell the desk what you're after and we'll draft a route.</p>
                <a class="btn btn-gold btn-sm" href="#contact">Contact Us</a>
            </div>
        </div>
    </div>
</section>

{{-- FEATURED ITINERARIES --}}
<section class="section section--alt" id="itineraries">
    <div class="container">
        <div class="section-head-row">
            <div>
                <span class="eyebrow">Itineraries</span>
                <h2>Featured Itineraries</h2>
            </div>
            <span class="scroll-hint">Scroll <svg class="icon"><use href="#i-arrow-right"/></svg></span>
        </div>
        <div class="trip-scroller">
            @foreach ($nations as $slug => $n)
                @php $featured = array_merge($n['itineraries'][0], ['image' => 'images/nations/'.$slug.'-hero.jpg']); @endphp
                @include('partials.trip-card', ['it' => $featured, 'n' => $n, 'slug' => $slug, 'order' => $loop->index, 'uid' => $slug.'-featured', 'variant' => 'home'])
            @endforeach
        </div>
    </div>
</section>

{{-- ABOUT US --}}
<section class="section" id="about">
    <div class="container about-grid">
        <div>
            <span class="eyebrow">Who we are</span>
            <h2>Bringing the Seven Nations closer to you</h2>
            <p>Teyvat Travel Services is a travel agency built for explorers, dreamers, and adventurers. We specialize in guided travel across all seven nations of Teyvat, offering safe, comfortable, and memorable journeys.</p>
            <p>We're an independent, sole-proprietor agency: one desk, one director, and a short list of trusted local partners in each nation. Whether you're chasing the wind, exploring ancient ruins, or simply looking for a new place to call home — we're here to make your journey effortless and unforgettable.</p>
            <div class="stat-row">
                <div class="stat-item"><span class="icon-badge"><svg class="icon"><use href="#i-compass"/></svg></span><div><strong>7</strong><span>Nations</span></div></div>
                <div class="stat-item"><span class="icon-badge"><svg class="icon"><use href="#i-shield"/></svg></span><div><strong>100%</strong><span>Guided &amp; Safe</span></div></div>
                <div class="stat-item"><span class="icon-badge"><svg class="icon"><use href="#i-clock"/></svg></span><div><strong>24/7</strong><span>Support</span></div></div>
            </div>
        </div>
        <div class="quote-card">
            <svg class="icon quote-icon"><use href="#i-compass"/></svg>
            <blockquote>“Different lands, same sky.”</blockquote>
            <span class="quote-rule"></span>
        </div>
    </div>
</section>

{{-- VALUES --}}
<section class="section section--alt section--tight">
    <div class="container">
        <span class="eyebrow">Our values</span>
        <h2>What We Stand For</h2>
        <div class="values">
            <div class="value">
                <span class="icon-badge"><svg class="icon"><use href="#i-shield"/></svg></span>
                <h3>Trust</h3>
                <p>Your safety and comfort are our top priority.</p>
            </div>
            <div class="value">
                <span class="icon-badge"><svg class="icon"><use href="#i-award"/></svg></span>
                <h3>Authenticity</h3>
                <p>Real experiences, not just photos.</p>
            </div>
            <div class="value">
                <span class="icon-badge"><svg class="icon"><use href="#i-heart"/></svg></span>
                <h3>Accessibility</h3>
                <p>Quality travel for every adventurer.</p>
            </div>
            <div class="value">
                <span class="icon-badge"><svg class="icon"><use href="#i-users"/></svg></span>
                <h3>Community</h3>
                <p>Supporting local cultures and communities.</p>
            </div>
        </div>
    </div>
</section>

{{-- OUR JOURNEY --}}
<section class="section">
    <div class="container journey">
        <figure class="journey-media">
            <img src="{{ asset('images/nations/gallery/mondstadt-2.jpg') }}" alt="Mondstadt, where Teyvat Travel Services was founded" loading="lazy">
        </figure>
        <div>
            <h2>Our Journey</h2>
            <p>Teyvat Travel Services began with a single traveller's notebook — routes tried, tea houses worth trusting, windmills worth timing a visit around. Friends started asking to borrow the notes, and borrowing the notes turned into asking for a whole itinerary.</p>
            <p>Every route we sell has still been walked, sailed, or sledded by the Director first.</p>
            <ol class="timeline-h">
                <li><strong>2015</strong><span>First solo journey across all seven nations</span></li>
                <li><strong>2018</strong><span>Founded in Mondstadt</span></li>
                <li><strong>2021</strong><span>Full itinerary catalogue launched</span></li>
                <li><strong>2026</strong><span>Booking &amp; payment system consolidated</span></li>
            </ol>
        </div>
    </div>
</section>

{{-- THE DIRECTOR --}}
<section class="section section--alt" id="director">
    <div class="container director">
        <div class="director-portrait">
            <svg viewBox="0 0 320 320" role="img" aria-label="Illustrated portrait of the Director framed by a compass rose">
                <circle cx="160" cy="160" r="150" fill="var(--surface)" stroke="var(--line)"/>
                <circle cx="160" cy="160" r="118" fill="none" stroke="var(--accent-soft)" stroke-width="2"/>
                <g stroke="var(--accent)" stroke-width="2">
                    <line x1="160" y1="30" x2="160" y2="55"/><line x1="160" y1="265" x2="160" y2="290"/>
                    <line x1="30" y1="160" x2="55" y2="160"/><line x1="265" y1="160" x2="290" y2="160"/>
                </g>
                <path d="M160,72 L178,158 L160,244 L142,158 Z" fill="var(--accent)"/>
                <circle cx="160" cy="158" r="6" fill="var(--surface)"/>
                <path d="M112,250 q48,-34 96,0 v18 h-96 Z" fill="var(--navy)"/>
                <circle cx="160" cy="188" r="34" fill="var(--navy)"/>
                <path d="M126,170 q34,-26 68,0" fill="none" stroke="var(--surface)" stroke-width="3"/>
            </svg>
        </div>
        <div>
            <span class="eyebrow">Meet the Director</span>
            <h2>The person behind every itinerary</h2>
            <p>The Director founded Teyvat Travel Services after years of independent travel across all seven nations, and remains personally involved in every itinerary the agency sells. Reachable directly through the booking desk, the Director reviews each traveller's route before it's confirmed — a habit the agency has never automated away, even as the booking system itself has moved online.</p>
            <blockquote class="pull-quote">“A good itinerary isn't a list of places. It's a route someone already trusts, handed to someone who doesn't know it yet.”</blockquote>
            <ul class="detail-list quick-facts">
                <li><svg class="icon"><use href="#i-pin"/></svg><span><strong>Based in:</strong> Mondstadt, with partner desks across all seven nations.</span></li>
                <li><svg class="icon"><use href="#i-clock"/></svg><span><strong>Booking window:</strong> most itineraries are confirmed within 3–5 business days of inquiry.</span></li>
                <li><svg class="icon"><use href="#i-compass"/></svg><span><strong>Custom routes:</strong> any itinerary can be combined into a multi-nation journey.</span></li>
            </ul>
        </div>
    </div>
</section>

{{-- CONTACT --}}
<section class="section" id="contact">
    <div class="container contact">
        <div class="contact-intro">
            <span class="eyebrow">Contact</span>
            <h2>Tell the desk where you want to go</h2>
            <p>Questions about a route, a custom multi-nation journey, or a booking? Send a message and the Director's desk will reply, usually within 3–5 business days.</p>
        </div>

        <form class="contact-form" method="POST" action="{{ url('/contact') }}" novalidate>
            @csrf

            @if (session('status'))
                <div class="contact-alert contact-alert--ok" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="contact-alert contact-alert--err" role="alert">Please check the highlighted fields and try again.</div>
            @endif

            {{-- Honeypot: real visitors never see or fill this --}}
            <div class="contact-hp" aria-hidden="true">
                <label for="contact-website">Leave this field empty</label>
                <input type="text" id="contact-website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="contact-row">
                <div class="contact-field">
                    <label for="contact-name">Name <span aria-hidden="true">*</span></label>
                    <input type="text" id="contact-name" name="name" value="{{ old('name') }}" autocomplete="name" required>
                    @error('name') <small class="contact-error">{{ $message }}</small> @enderror
                </div>
                <div class="contact-field">
                    <label for="contact-email">Email <span aria-hidden="true">*</span></label>
                    <input type="email" id="contact-email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                    @error('email') <small class="contact-error">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="contact-field">
                <label for="contact-nation">Nation of interest</label>
                <select id="contact-nation" name="nation">
                    <option value="">Not sure yet</option>
                    @foreach ($nations as $slug => $n)
                        <option value="{{ $slug }}" @selected(old('nation', request('nation')) === $slug)>{{ $n['name'] }}</option>
                    @endforeach
                    <option value="multi" @selected(old('nation', request('nation')) === 'multi')>Multi-nation journey</option>
                </select>
                @error('nation') <small class="contact-error">{{ $message }}</small> @enderror
            </div>

            <div class="contact-field">
                <label for="contact-message">Message <span aria-hidden="true">*</span></label>
                <textarea id="contact-message" name="message" rows="5" required>{{ old('message') }}</textarea>
                @error('message') <small class="contact-error">{{ $message }}</small> @enderror
            </div>

            <button type="submit" class="btn btn-gold">Send Message <svg class="icon"><use href="#i-arrow-right"/></svg></button>
        </form>
    </div>

    <style>
        .contact { display: grid; gap: 2.5rem; grid-template-columns: 1fr; align-items: start; }
        @media (min-width: 860px) { .contact { grid-template-columns: 5fr 7fr; gap: 4rem; } }
        .contact-form { display: grid; gap: 1.1rem; background: var(--surface); border: 1px solid var(--line); border-radius: 16px; padding: 1.75rem; }
        .contact-row { display: grid; gap: 1.1rem; grid-template-columns: 1fr; }
        @media (min-width: 560px) { .contact-row { grid-template-columns: 1fr 1fr; } }
        .contact-field { display: grid; gap: .4rem; }
        .contact-field label { font-weight: 600; font-size: .9rem; color: var(--navy); }
        .contact-field input,
        .contact-field select,
        .contact-field textarea { width: 100%; font: inherit; color: inherit; background: #fff; border: 1px solid var(--line); border-radius: 10px; padding: .7rem .85rem; }
        .contact-field input:focus,
        .contact-field select:focus,
        .contact-field textarea:focus { outline: 2px solid var(--accent); outline-offset: 1px; }
        .contact-field textarea { resize: vertical; min-height: 8rem; }
        .contact-error { color: #b3261e; font-size: .82rem; }
        .contact-alert { border-radius: 10px; padding: .75rem 1rem; font-size: .92rem; }
        .contact-alert--ok { background: #e7f4ea; color: #1e5b2d; }
        .contact-alert--err { background: #fdeceb; color: #b3261e; }
        .contact-form .btn { justify-self: start; }
        .contact-hp { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }
    </style>
</section>

{{-- CTA --}}
<section class="cta-band">
    <div class="container cta-inner">
        <div class="cta-with-icon">
            <svg class="icon cta-icon"><use href="#i-compass"/></svg>
            <div>
                <h2>Ready for your next journey?</h2>
                <p>Explore the seven nations with Teyvat Travel Services.</p>
            </div>
        </div>
        <button type="button" class="btn btn-gold btn-pill" data-action="book-now">Book Now <svg class="icon"><use href="#i-arrow-right"/></svg></button>
    </div>
</section>

@endsection