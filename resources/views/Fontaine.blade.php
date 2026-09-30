@extends('layouts.app')

@section('title', 'Fontaine — Chapter V')
@section('description', 'Plan a trip to Fontaine with Teyvat Travel Co.: itineraries, field notes and seasonal guidance for the Fontaine chapter of your journey.')
@section('body-class', 'chapter-fontaine')

@section('content')
    <section class="hero">
        <div class="container hero-inner">
            <div>
                <h1>Fontaine</h1>
                <p class="hero-tagline">A city that settles its arguments by the light of chandeliers.</p>
            </div>
            <p class="hero-lede">Fontaine is a canal city built around the theatre of justice and the theatre of the stage
                in roughly equal measure, with waterways doing double duty as streets.</p>
            <div class="hero-banner">
                <img src="{{ asset('images/nations/fontaine-hero.jpg') }}"
                    alt="An arched bridge over a canal with light radiating above">
            </div>
        </div>
    </section>
    <section aria-label="At a glance">
        <div class="container">
            <dl class="facts">
                <div class="fact">
                    <dt>Best season</dt>
                    <dd>Year-round; opera peaks in winter</dd>
                </div>
                <div class="fact">
                    <dt>Known for</dt>
                    <dd>Canal courts &amp; underwater promenades</dd>
                </div>
                <div class="fact">
                    <dt>Signature taste</dt>
                    <dd>Courthouse-district patisserie</dd>
                </div>
                <div class="fact">
                    <dt>Travel pace</dt>
                    <dd>Theatrical, precise</dd>
                </div>
            </dl>
        </div>
    </section>
    <section class="content">
        <div class="container content-grid">
            <div class="journal">
                <p class="dropcap">Fontaine runs on canals the way other cities run on streets — boats double as taxis, and
                    the arched bridges are as much civic landmarks as any building. The famous courthouse district sits
                    right at the water's edge, and public hearings are treated as something close to popular theatre.</p>
                <p>Speaking of theatre: the opera house is the other centre of gravity, and its season shapes the city's
                    calendar as much as any court date does. Dress for the evening if you're headed there; the audience is
                    part of the performance.</p>
                <p>Beneath the surface, viewing galleries let visitors walk under the canal itself, watching the hulls of
                    passing boats overhead — a strange, quiet contrast to the busy theatre of the streets above.</p>
            </div>
            <aside class="field-note">
                <h3>Field notes</h3>
                <p>Canal boats keep to fixed lanes; hail one from a marked dock rather than the water's edge.</p>
                <p>Public court sessions are open to visitors but fill fast on notable cases — arrive early.</p>
                <p>The underwater promenade runs cooler than the street level; bring a light jacket.</p>
            </aside>
        </div>
    </section>
    <section class="itineraries">
        <div class="container">
            <div class="section-head">
                <h2>Itineraries our travellers return for</h2>
                <a href="{{ route('home') }}">Ask the desk to tailor one →</a>
            </div>
            <div class="card-row">
                <article class="package-card">
                    <div class="len">4 days / 3 nights</div>
                    <h3>Canal &amp; Courtroom Walk</h3>
                    <p>A guided route through the canal district and a public hearing, with a patisserie stop.</p>
                </article>
                <article class="package-card">
                    <div class="len">3 days / 2 nights</div>
                    <h3>Opera Weekend</h3>
                    <p>Two evening performances with dinner reservations timed around each show.</p>
                </article>
                <article class="package-card">
                    <div class="len">2 days / 1 night</div>
                    <h3>Underwater Promenade Tour</h3>
                    <p>A quieter itinerary centred on the below-surface galleries and canal-side cafés.</p>
                </article>
            </div>
        </div>
    </section>
    @include('partials.gallery', [
        'nation' => 'fontaine',
        'photos' => [
            ['src' => 'images/nations/gallery/fontaine-1.jpg', 'caption' => "Aerial views of Fontaine’s canals"],
            ['src' => 'images/nations/gallery/fontaine-2.jpg', 'caption' => "Exploring Fontaine’s underwater ruins"],
            ['src' => 'images/nations/gallery/fontaine-3.jpg', 'caption' => "A grand view of Fontaine’s skyline"],
            ['src' => 'images/nations/gallery/fontaine-4.jpg', 'caption' => "Elegant streets of Fontaine’s city"],
        ]
    ])

    <section class="cta-band">
        <div class="container cta-inner">
            <div>
                <h2>Plan your route to Fontaine</h2>
                <p>Opera tickets and courtroom seating both sell out around headline cases and premieres — we book both the
                    moment your dates are confirmed.</p>
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a class="btn btn-brass" href="{{ route('home') }}">Request a dossier</a>
                <a class="btn btn-outline" href="mailto:desk@teyvattravel.co">Email the desk</a>
            </div>
        </div>
    </section>
@endsection