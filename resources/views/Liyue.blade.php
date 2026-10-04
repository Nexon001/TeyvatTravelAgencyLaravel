@extends('layouts.app')

@section('title', 'Liyue — Chapter II')
@section('description', 'Plan a trip to Liyue with Teyvat Travel Services: itineraries, field notes and seasonal guidance for the Liyue chapter of your journey.')
@section('body-class', 'chapter-liyue')

@section('content')
    <section class="hero">
        <div class="container hero-inner">
            <div>
                <h1>Liyue</h1>
                <p class="hero-tagline">The Harbor of Stone and Commerce.</p>
            </div>
            <p class="hero-lede">Liyue is a trading port grown up the side of a mountain range, where jade-toned cliffs meet
                a working harbour and a handshake still counts for more than paperwork.</p>
            <div class="hero-banner">
                <img src="{{ asset('images/nations/liyue-hero.jpg') }}"
                    alt="Steep harbour peaks above a working port at dusk">
            </div>
        </div>
    </section>
    <section aria-label="At a glance">
        <div class="container">
            <dl class="facts">
                <div class="fact">
                    <dt>Best season</dt>
                    <dd>Autumn, during the lantern season</dd>
                </div>
                <div class="fact">
                    <dt>Known for</dt>
                    <dd>Terraced port &amp; jade-toned peaks</dd>
                </div>
                <div class="fact">
                    <dt>Signature taste</dt>
                    <dd>Harbourside tea houses</dd>
                </div>
                <div class="fact">
                    <dt>Travel pace</dt>
                    <dd>Measured, mercantile</dd>
                </div>
            </dl>
        </div>
    </section>
    <section class="content">
        <div class="container content-grid">
            <div class="journal">
                <p class="dropcap">Liyue does business in the open. The harbour is stacked with cargo, the docks are loud
                    with negotiation, and the mountain peaks above it all give the port a sense of scale that keeps
                    everyone, buyers and sellers alike, reasonably honest. A contract signed here is treated as closer to a
                    vow than a formality.</p>
                <p>The city climbs the cliffs in terraces, so a walk from the harbour to the upper town is also a walk
                    through several centuries of architecture, each level built to outlast the one below it. Stone stairways
                    connect tea houses, guild halls and shrines tucked into the rock.</p>
                <p>Lantern season, in early autumn, is when the harbour is at its most photogenic: paper lanterns released
                    from the docks drift up past the cliffs, and every tea house extends its hours to match.</p>
            </div>
            <aside class="field-note">
                <h3>Field notes</h3>
                <p>Bargaining is expected in the harbour market; a flat asking price is treated as an opening offer.</p>
                <p>Book a tea house table a day ahead during lantern season — the good ones fill early.</p>
                <p>The cliffside pilgrimage trail is steep; the mountain-goat shortcuts are not shortcuts.</p>
            </aside>
        </div>
    </section>
    @include('partials.signature-package', [
        'title' => 'The Harbor of Stone and Commerce',
        'price' => '₱15,000 / person · 4 Days, 3 Nights',
        'description' => 'A bustling port city tour blending breathtaking mountain ranges, ancient folklore, and world-class culinary adventures.',
        'stops' => ['Liyue Harbor', 'Mt. Aocang', 'Wangshu Inn'],
    ])

    <section class="itineraries">
        <div class="container">
            <div class="section-head">
                <h2>Itineraries our travellers return for</h2>
                <a href="{{ route('home') }}">Ask the desk to tailor one →</a>
            </div>
            <div class="card-row">
                <article class="package-card">
                    <div class="len">3 days / 2 nights</div>
                    <h3>Harbour &amp; Night Market</h3>
                    <p>Docks by day, night market by lantern-light, with a private tea tasting.</p>
                </article>
                <article class="package-card">
                    <div class="len">5 days / 4 nights</div>
                    <h3>Peak Pilgrimage Trek</h3>
                    <p>A staged climb through the terraced town to the ridge shrines above the harbour.</p>
                </article>
                <article class="package-card">
                    <div class="len">2 days / 1 night</div>
                    <h3>Tea House Culinary Trail</h3>
                    <p>A walking tasting menu across four generations-old tea houses.</p>
                </article>
            </div>
        </div>
    </section>
    @include('partials.gallery', [
        'nation' => 'liyue',
        'photos' => [
            ['src' => 'images/nations/gallery/liyue-1.jpg', 'caption' => "Lanterns drifting above the harbour at dusk"],
            ['src' => 'images/nations/gallery/liyue-2.jpg', 'caption' => "Stone stairways climbing the terraced cliffs"],
            ['src' => 'images/nations/gallery/liyue-3.jpg', 'caption' => "The night market in full swing"],
            ['src' => 'images/nations/gallery/liyue-4.jpg', 'caption' => "A tea house balcony overlooking the docks"],
        ]
    ])

    <section class="cta-band">
        <div class="container cta-inner">
            <div>
                <h2>Plan your route to Liyue</h2>
                <p>We time harbour departures around the lantern releases so your itinerary never misses the one evening the
                    whole port turns out for.</p>
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a class="btn btn-brass" href="{{ route('home') }}">Request a dossier</a>
                <a class="btn btn-outline" href="mailto:desk@teyvattravel.co">Email the desk</a>
            </div>
        </div>
    </section>
@endsection