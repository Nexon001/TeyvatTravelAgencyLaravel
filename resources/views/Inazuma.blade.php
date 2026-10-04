@extends('layouts.app')

@section('title', 'Inazuma — Chapter III')
@section('description', 'Plan a trip to Inazuma with Teyvat Travel Services: itineraries, field notes and seasonal guidance for the Inazuma chapter of your journey.')
@section('body-class', 'chapter-inazuma')

@section('content')
    <section class="hero">
        <div class="container hero-inner">
            <div>
                <h1>Inazuma</h1>
                <p class="hero-tagline">The Realm of Eternal Thunder</p>
            </div>
            <p class="hero-lede">A scattered archipelago under frequently dramatic skies, Inazuma moves at the pace of the
                tide and the ferry schedule, and asks travellers to do the same.</p>
            <div class="hero-banner">
                <img src="{{ asset('images/nations/inazuma-hero.jpg') }}"
                    alt="A torii-style gate on the water beneath a storm-lit sky">
            </div>
        </div>
    </section>
    <section aria-label="At a glance">
        <div class="container">
            <dl class="facts">
                <div class="fact">
                    <dt>Best season</dt>
                    <dd>Early summer, clearest straits</dd>
                </div>
                <div class="fact">
                    <dt>Known for</dt>
                    <dd>Island-hopping ferries &amp; storm-lit skies</dd>
                </div>
                <div class="fact">
                    <dt>Signature taste</dt>
                    <dd>Coastal rice &amp; seaweed fare</dd>
                </div>
                <div class="fact">
                    <dt>Travel pace</dt>
                    <dd>Ceremonial, tide-timed</dd>
                </div>
            </dl>
        </div>
    </section>
    <section class="content">
        <div class="container content-grid">
            <div class="journal">
                <p class="dropcap">Inazuma is a nation of islands linked by ferry lines, and the crossing itself is often
                    the best part of the day: open water, changeable skies, and the kind of dramatic thunderheads that
                    locals track the way other places track rainfall. Storm-watching from a covered tea house is a
                    legitimate pastime here, not a workaround.</p>
                <p>Tradition is kept carefully on every island — archery ranges, tea ceremonies and shrine paths are
                    maintained not as museum pieces but as things islanders actually still do, and visitors are usually
                    welcome to sit in.</p>
                <p>Each island has its own micro-climate and its own small specialty, so the archipelago rewards travellers
                    who island-hop slowly rather than rushing the whole chain in one pass.</p>
            </div>
            <aside class="field-note">
                <h3>Field notes</h3>
                <p>Ferry schedules shift with the tide, not the clock — check the day's posted times each morning.</p>
                <p>Storm season is beautiful but crossings pause; build a buffer day into the itinerary.</p>
                <p>Shrine paths ask for quiet on approach; conversation resumes past the gate.</p>
            </aside>
        </div>
    </section>
    @include('partials.signature-package', [
        'title' => 'The Realm of Eternal Thunder',
        'price' => '₱18,000 / person · 4 Days, 3 Nights',
        'description' => 'Discover a culturally rich archipelago featuring striking purple foliage, traditional shrines, and serene coastal views.',
        'stops' => ['Narukami Island Grand Shrine', 'Ritou Trading Port', 'Tenshukaku'],
    ])

    <section class="itineraries">
        <div class="container">
            <div class="section-head">
                <h2>Itineraries our travellers return for</h2>
                <a href="{{ route('home') }}">Ask the desk to tailor one →</a>
            </div>
            <div class="card-row">
                <article class="package-card">
                    <div class="len">6 days / 5 nights</div>
                    <h3>Five-Island Ferry Circuit</h3>
                    <p>A paced hop across five islands, each with a one-night stay and a local guide.</p>
                </article>
                <article class="package-card">
                    <div class="len">3 days / 2 nights</div>
                    <h3>Storm-Watching Retreat</h3>
                    <p>A covered-veranda tea house stay timed to the season's clearest storm views.</p>
                </article>
                <article class="package-card">
                    <div class="len">2 days / 1 night</div>
                    <h3>Shrine Path Pilgrimage</h3>
                    <p>A guided walk along a working shrine trail, with an archery lesson included.</p>
                </article>
            </div>
        </div>
    </section>
    @include('partials.gallery', [
        'nation' => 'inazuma',
        'photos' => [
            ['src' => 'images/nations/gallery/inazuma-1.jpg', 'caption' => "A ferry crossing between two islands"],
            ['src' => 'images/nations/gallery/inazuma-2.jpg', 'caption' => "Storm clouds gathering over the strait"],
            ['src' => 'images/nations/gallery/inazuma-3.jpg', 'caption' => "A shrine path under a quiet grove"],
            ['src' => 'images/nations/gallery/inazuma-4.jpg', 'caption' => "Fishing boats resting at low tide"],
        ]
    ])

    <section class="cta-band">
        <div class="container cta-inner">
            <div>
                <h2>Plan your route to Inazuma</h2>
                <p>Because ferries run on tide tables rather than fixed timetables, we build every Inazuma route with a
                    buffer day — ask us how.</p>
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a class="btn btn-brass" href="{{ route('home') }}">Request a dossier</a>
                <a class="btn btn-outline" href="mailto:desk@teyvattravel.co">Email the desk</a>
            </div>
        </div>
    </section>
@endsection