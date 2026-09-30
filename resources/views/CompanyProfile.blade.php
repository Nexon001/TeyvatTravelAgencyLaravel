@extends('layouts.app')

@section('title', 'Company Profile & About the Director')
@section('description', 'Teyvat Travel Co. is an independent, sole-proprietor travel agency covering all seven nations of Teyvat. Learn about the company and its founding Director.')
@section('body-class', 'chapter-profile')

@section('content')
    <section class="hero">
        <div class="container hero-inner profile-hero" style="padding-block:56px 40px;">
            <div>
                <div class="eyebrow-row">
                    <span class="seal">TT</span>
                    <span class="chapter-label">Company profile</span>
                </div>
                <h1>Teyvat Travel Co.</h1>
                <p class="hero-tagline">One desk. Seven nations. Every itinerary personally reviewed.</p>
                <p class="hero-lede">Teyvat Travel Co. is an independent, sole-proprietor travel agency built around a
                    simple premise: a traveller planning a route across the seven nations deserves an itinerary shaped by
                    someone who has actually walked it — not a catalogue assembled from a distance.</p>
                <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:8px;">
                    <a class="btn btn-brass" href="#director">Meet the Director</a>
                    <a class="btn btn-outline" href="#nations" style="color:var(--ink); border-color:var(--line);">Browse
                        the seven nations</a>
                </div>
            </div>
            <div class="hero-banner" style="aspect-ratio:1/1;">
                <svg aria-label="An illustrated silhouette of the Director holding a travel journal, framed by a compass rose"
                    role="img" viewBox="0 0 320 320" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="160" cy="160" fill="var(--paper-light)" r="150" stroke="var(--line)"></circle>
                    <circle cx="160" cy="160" fill="none" r="118" stroke="var(--accent-soft)" stroke-width="2"></circle>
                    <g stroke="var(--accent)" stroke-width="2">
                        <line x1="160" x2="160" y1="30" y2="55"></line>
                        <line x1="160" x2="160" y1="265" y2="290"></line>
                        <line x1="30" x2="55" y1="160" y2="160"></line>
                        <line x1="265" x2="290" y1="160" y2="160"></line>
                    </g>
                    <path d="M160,72 L178,158 L160,244 L142,158 Z" fill="var(--accent)"></path>
                    <circle cx="160" cy="158" fill="var(--paper-light)" r="6"></circle>
                    <path d="M112,250 q48,-34 96,0 v18 h-96 Z" fill="var(--ink)"></path>
                    <circle cx="160" cy="188" fill="var(--ink)" r="34"></circle>
                    <path d="M126,170 q34,-26 68,0" fill="none" stroke="var(--paper-light)" stroke-width="3"></path>
                </svg>
            </div>
        </div>
    </section>
    <section aria-label="At a glance">
        <div class="container">
            <dl class="facts">
                <div class="fact">
                    <dt>Founded</dt>
                    <dd>2018, in Mondstadt</dd>
                </div>
                <div class="fact">
                    <dt>Structure</dt>
                    <dd>Sole proprietorship</dd>
                </div>
                <div class="fact">
                    <dt>Coverage</dt>
                    <dd>All seven nations of Teyvat</dd>
                </div>
                <div class="fact">
                    <dt>Approach</dt>
                    <dd>One itinerary, one director, no franchising</dd>
                </div>
            </dl>
        </div>
    </section>
    <section class="content">
        <div class="container content-grid">
            <div class="journal">
                <h2>Our story</h2>
                <p class="dropcap">Teyvat Travel Co. began with a single traveller's notebook. Long before it was an agency,
                    it was a set of hand-kept journals — routes tried, tea houses worth trusting, windmills worth timing a
                    visit around — carried from Mondstadt's hills to Snezhnaya's tundra and back. Friends started asking to
                    borrow the notes. Eventually, borrowing the notes turned into asking for a whole itinerary.</p>
                <p>What started as a favour became a business, but the working method never really changed: every route the
                    agency sells has been walked, sailed, or sledded by the Director first. Nothing is listed on the
                    strength of a brochure alone.</p>
                <h2>How we work</h2>
                <p>As a sole proprietorship, Teyvat Travel Co. stays deliberately small. There is one desk, one director,
                    and a short list of trusted local partners in each nation — vineyard estates in Mondstadt, harbour
                    guides in Liyue, ferry operators in Inazuma, and so on. That size is a feature: every booking gets read
                    by a person who knows the destination, not routed through a call centre.</p>
                <p>The agency's day-to-day covers the full arc of a trip: fielding inquiries, checking date availability
                    against real capacity, confirming bookings, and following up on payments and travel documents — all
                    handled from the same desk that plans the route itself.</p>
                <h2 id="director">About the Director</h2>
                <p>The Director founded Teyvat Travel Co. after years of independent travel across all seven nations, and
                    remains personally involved in every itinerary the agency sells. Reachable directly through the booking
                    desk, the Director reviews each traveller's route before it's confirmed — a habit the agency has never
                    automated away, even as the booking system itself has moved online.</p>
                <p
                    style="font-family:var(--font-display); font-style:italic; font-size:1.15rem; color:var(--accent-deep); border-left:3px solid var(--accent); padding-left:16px; max-width:52ch;">
                    "A good itinerary isn't a list of places. It's a route someone already trusts, handed to someone who
                    doesn't know it yet."</p>
            </div>
            <aside class="field-note">
                <h3>Quick facts</h3>
                <p><strong>Based in:</strong> Mondstadt, with partner desks reachable across all seven nations.</p>
                <p><strong>Booking window:</strong> Most itineraries are confirmed within 3–5 business days of inquiry.</p>
                <p><strong>Custom routes:</strong> Any of the packages listed per nation can be combined into a multi-nation
                    journey — ask the desk.</p>
            </aside>
        </div>
    </section>
    <section class="itineraries" style="border-top:1px solid var(--line);">
        <div class="container">
            <div class="section-head">
                <h2>What we stand for</h2>
            </div>
            <div class="values-grid">
                <div class="value-card"><svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <path d="M6 3h9l5 5v13H6z"></path>
                        <path d="M15 3v5h5"></path>
                        <path d="M9 13h7M9 17h5"></path>
                    </svg>
                    <h3>Promises kept in writing</h3>
                    <p style="font-size:.9rem; color:var(--ink-soft); margin:0;">Every booking, date and payment is
                        confirmed in writing before a traveller sets out — no verbal-only arrangements.</p>
                </div>
                <div class="value-card"><svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M15 9l-2 6-6 2 2-6z"></path>
                    </svg>
                    <h3>Routes we've actually walked</h3>
                    <p style="font-size:.9rem; color:var(--ink-soft); margin:0;">Nothing is sold on brochure copy alone. If
                        it's on the itinerary, the Director has been there.</p>
                </div>
                <div class="value-card"><svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <path d="M2 12l5-4 4 3 3-3 5 4"></path>
                        <path d="M7 15l3 3 2-2 3 3 4-4"></path>
                    </svg>
                    <h3>Local partners, not franchises</h3>
                    <p style="font-size:.9rem; color:var(--ink-soft); margin:0;">We work with a short list of trusted
                        operators in each nation instead of anonymous, interchangeable vendors.</p>
                </div>
                <div class="value-card"><svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <path d="M4 5c3 0 6 1 8 3 2-2 5-3 8-3v14c-3 0-6 1-8 3-2-2-5-3-8-3z"></path>
                        <path d="M12 8v14"></path>
                    </svg>
                    <h3>One dossier per traveller</h3>
                    <p style="font-size:.9rem; color:var(--ink-soft); margin:0;">Every itinerary is assembled individually —
                        not pulled from a fixed package catalogue.</p>
                </div>
            </div>
        </div>
    </section>
    <section class="content" style="padding-top:0;">
        <div class="container">
            <h2>Milestones</h2>
            <ul class="timeline">
                <li><span class="yr">2015</span>A first solo journey across all seven nations, kept as a hand-written travel
                    journal.</li>
                <li><span class="yr">2018</span>Teyvat Travel Co. founded as a two-desk office in Mondstadt, booking trips
                    by referral only.</li>
                <li><span class="yr">2021</span>Full itinerary catalogue launched, covering guided routes in all seven
                    nations.</li>
                <li><span class="yr">2026</span>Booking, scheduling and payment tracking consolidated into a single online
                    platform.</li>
            </ul>
        </div>
    </section>
    <section class="itineraries" id="nations" style="border-top:1px solid var(--line); padding-top:40px;">
        <div class="container">
            <div class="section-head">
                <h2>The seven nations</h2><a href="mailto:desk@teyvattravel.co">Ask about a multi-nation route →</a>
            </div>
            <div class="nation-grid">
                <a class="nation-chip" href="{{ route('mondstadt') }}"><span class="n">Mondstadt</span><span class="e">Wind
                        &amp; vineyard hills</span></a>
                <a class="nation-chip" href="{{ route('liyue') }}"><span class="n">Liyue</span><span class="e">Harbour &amp;
                        mountain contracts</span></a>
                <a class="nation-chip" href="{{ route('inazuma') }}"><span class="n">Inazuma</span><span
                        class="e">Archipelago &amp; storm skies</span></a>
                <a class="nation-chip" href="{{ route('sumeru') }}"><span class="n">Sumeru</span><span class="e">Rainforest
                        &amp; desert scholarship</span></a>
                <a class="nation-chip" href="{{ route('fontaine') }}"><span class="n">Fontaine</span><span class="e">Canals
                        &amp; courtroom theatre</span></a>
                <a class="nation-chip" href="{{ route('natlan') }}"><span class="n">Natlan</span><span class="e">Fire-lit
                        terraces &amp; festival</span></a>
                <a class="nation-chip" href="{{ route('snezhnaya') }}"><span class="n">Snezhnaya</span><span class="e">Frost
                        palaces &amp; aurora tundra</span></a>
            </div>
        </div>
    </section>
    <section class="cta-band">
        <div class="container cta-inner">
            <div>
                <h2>Start with a conversation, not a form</h2>
                <p>Tell us which nations you're drawn to and roughly when you'd like to travel — the Director will reply
                    with a first-draft route.</p>
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a class="btn btn-brass" href="mailto:desk@teyvattravel.co">Email the desk</a>
                <a class="btn btn-outline" href="#nations">Browse destinations</a>
            </div>
        </div>
    </section>
@endsection