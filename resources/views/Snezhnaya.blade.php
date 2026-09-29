@extends('layouts.app')

@section('title', 'Snezhnaya')
@section('description', 'Plan a trip to Snezhnaya with Teyvat Travel Co.: itineraries, field notes and seasonal guidance for the Snezhnaya chapter of your journey.')
@section('body-class', 'chapter-snezhnaya')

@section('content')
<section class="hero">
<div class="container hero-inner">
<div>
<h1>Snezhnaya</h1>
<p class="hero-tagline">The quiet at the edge of the map.</p>
</div>
<p class="hero-lede">Snezhnaya is the coldest and least-visited of the seven nations, a frost-built country of aurora-lit tundra where the pace of travel slows to match the season.</p>
<div class="hero-banner">
<img src="{{ asset('images/nations/snezhnaya-hero.jpg') }}" alt="Icy peaks under an aurora-lit night sky">
</div>
</div>
</section>
<section aria-label="At a glance">
<div class="container">
<dl class="facts">
<div class="fact"><dt>Best season</dt><dd>Deep winter, for aurora visibility</dd></div>
<div class="fact"><dt>Known for</dt><dd>Frost palaces &amp; aurora-lit tundra</dd></div>
<div class="fact"><dt>Signature taste</dt><dd>Fermented preserves &amp; warm spiced drinks</dd></div>
<div class="fact"><dt>Travel pace</dt><dd>Reserved, slow-paced</dd></div>
</dl>
</div>
</section>
<section class="content">
<div class="container content-grid">
<div class="journal">
<p class="dropcap">Few travellers make it as far as Snezhnaya, and the nation seems to prefer it that way: frost-carved architecture rises out of a tundra that stays lit, on clear nights, by slow curtains of aurora rather than streetlight. Everything about the pace here assumes the cold is in charge, and visitors adjust accordingly.</p>
<p>Sled travel connects most of the outlying lodges, and the ride itself — runners over packed snow, breath fogging, the sky doing something extraordinary overhead — is often the highlight travellers remember longest.</p>
<p>Warmth is treated as a shared resource: heated common rooms, communal preserves laid out for anyone passing through, and a culture of quiet hospitality that asks little of a guest beyond staying for the story being told.</p>
</div>
<aside class="field-note">
<h3>Field notes</h3>
<p>Aurora visibility is best on clear, moonless nights — lodges post a nightly forecast.</p>
<p>Sled routes are weather-dependent; build one flexible day into any winter itinerary.</p>
<p>Layer over insulate: several thin layers outperform one heavy coat in the dry cold.</p>
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
<h3>Aurora Lodge Escape</h3>
<p>A remote lodge stay timed to the season's clearest forecasted aurora nights.</p>
</article>
<article class="package-card">
<div class="len">3 days / 2 nights</div>
<h3>Frost Palace Trail</h3>
<p>A guided walk through the region's frost-carved architecture and warming halls.</p>
</article>
<article class="package-card">
<div class="len">2 days / 1 night</div>
<h3>Tundra Sled Expedition</h3>
<p>A single overnight sled route between two outlying lodges.</p>
</article>
</div>
</div>
</section>
<section class="cta-band">
<div class="container cta-inner">
<div>
<h2>Plan your route to Snezhnaya</h2>
<p>Snezhnaya bookings are weather-flexible by default — we hold a buffer night on every itinerary so a cloudy forecast never costs you the aurora.</p>
</div>
<div style="display:flex; gap:12px; flex-wrap:wrap;">
<a class="btn btn-brass" href="{{ route('home') }}">Request a dossier</a>
<a class="btn btn-outline" href="mailto:desk@teyvattravel.co">Email the desk</a>
</div>
</div>
</section>
@endsection
