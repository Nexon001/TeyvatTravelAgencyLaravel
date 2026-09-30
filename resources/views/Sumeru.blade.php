@extends('layouts.app')

@section('title', 'Sumeru — Chapter IV')
@section('description', 'Plan a trip to Sumeru with Teyvat Travel Co.: itineraries, field notes and seasonal guidance for the Sumeru chapter of your journey.')
@section('body-class', 'chapter-sumeru')

@section('content')
    <section class="hero">
        <div class="container hero-inner">
            <div>
                <h1>Sumeru</h1>
                <p class="hero-tagline">A library that grew a rainforest, and a desert that remembers everything.</p>
            </div>
            <p class="hero-lede">Sumeru holds two landscapes at once — a dense rainforest and a wide desert — joined by a
                culture that treats curiosity as a form of hospitality.</p>
            <div class="hero-banner">
                <img src="{{ asset('images/nations/sumeru-hero.jpg') }}"
                    alt="Forest canopy on one side and desert dunes on the other, split by a domed structure">
            </div>
        </div>
    </section>
    <section aria-label="At a glance">
        <div class="container">
            <dl class="facts">
                <div class="fact">
                    <dt>Best season</dt>
                    <dd>Dry season, cooler desert nights</dd>
                </div>
                <div class="fact">
                    <dt>Known for</dt>
                    <dd>Rainforest canopy &amp; sandstone ruins</dd>
                </div>
                <div class="fact">
                    <dt>Signature taste</dt>
                    <dd>Spiced caravan cuisine</dd>
                </div>
                <div class="fact">
                    <dt>Travel pace</dt>
                    <dd>Curious, unhurried</dd>
                </div>
            </dl>
        </div>
    </section>
    <section class="content">
        <div class="container content-grid">
            <div class="journal">
                <p class="dropcap">The rainforest half of Sumeru is a canopy country: raised walkways run between tree
                    crowns, and the sound underneath the leaf cover is almost never silence. Scholars' outposts are tucked
                    into the branches at intervals, and most will happily show a visitor whatever they're currently
                    cataloguing.</p>
                <p>Head the other direction and the trees give way to open desert within a few hours' travel — sandstone
                    ruins, caravan routes, and skies that are astonishing after dark, once the day's heat has cleared.</p>
                <p>What ties the two landscapes together is a shared civic habit of asking questions. Debate is a form of
                    entertainment here, and travellers who bring a genuine question to a caravan fire or a canopy outpost
                    tend to get a very generous answer.</p>
            </div>
            <aside class="field-note">
                <h3>Field notes</h3>
                <p>Desert nights run cold despite the daytime heat — pack a mid-weight layer even in dry season.</p>
                <p>Canopy walkways have posted weight limits; groups over four usually cross in two passes.</p>
                <p>Caravan cooks take real pride in their spice work; asking about it is a welcome icebreaker.</p>
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
                    <div class="len">5 days / 4 nights</div>
                    <h3>Canopy &amp; Cloister Circuit</h3>
                    <p>Two nights in the rainforest canopy, two among the desert's scholar outposts.</p>
                </article>
                <article class="package-card">
                    <div class="len">4 days / 3 nights</div>
                    <h3>Desert Caravan Expedition</h3>
                    <p>A guided caravan crossing between sandstone ruin sites, cooked meals included.</p>
                </article>
                <article class="package-card">
                    <div class="len">2 days / 1 night</div>
                    <h3>Stargazer's Dune Camp</h3>
                    <p>A single dune-side night built entirely around the after-dark sky.</p>
                </article>
            </div>
        </div>
    </section>
    @include('partials.gallery', [
        'nation' => 'sumeru',
        'photos' => [
            ['src' => 'images/nations/gallery/sumeru-1.jpg', 'caption' => "An ancient structure in the desert"],
            ['src' => 'images/nations/gallery/sumeru-2.jpg', 'caption' => "A village in a stone hill"],
            ['src' => 'images/nations/gallery/sumeru-3.jpg', 'caption' => "A pyramid resting for the night"],
            ['src' => 'images/nations/gallery/sumeru-4.jpg', 'caption' => "The desert sky just after dark"],
        ]
    ])

    <section class="cta-band">
        <div class="container cta-inner">
            <div>
                <h2>Plan your route to Sumeru</h2>
                <p>Most travellers underestimate how different Sumeru's two landscapes feel within the same week — we build
                    in a rest day at the transition point.</p>
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a class="btn btn-brass" href="{{ route('home') }}">Request a dossier</a>
                <a class="btn btn-outline" href="mailto:desk@teyvattravel.co">Email the desk</a>
            </div>
        </div>
    </section>
@endsection