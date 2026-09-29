@extends('layouts.app')

@section('title', 'Natlan')
@section('description', 'Plan a trip to Natlan with Teyvat Travel Co.: itineraries, field notes and seasonal guidance for the Natlan chapter of your journey.')
@section('body-class', 'chapter-natlan')

@section('content')
<section class="hero">
<div class="container hero-inner">
<div>
<h1>Natlan</h1>
<p class="hero-tagline">A nation that measures a life by how it's celebrated.</p>
</div>
<p class="hero-lede">Natlan is built in fire-lit terraces and measures its calendar in festivals, with drum processions that turn nearly any evening into an event worth staying up for.</p>
<div class="hero-banner">
<img src="{{ asset('images/nations/natlan-hero.jpg') }}" alt="A stepped plaza lit by fire under a warm evening sky">
</div>
</div>
</section>
<section aria-label="At a glance">
<div class="container">
<dl class="facts">
<div class="fact"><dt>Best season</dt><dd>Festival season, warm dry evenings</dd></div>
<div class="fact"><dt>Known for</dt><dd>Terraced plazas &amp; drum processions</dd></div>
<div class="fact"><dt>Signature taste</dt><dd>Smoke-grilled festival fare</dd></div>
<div class="fact"><dt>Travel pace</dt><dd>Warm, communal, loud</dd></div>
</dl>
</div>
</section>
<section class="content">
<div class="container content-grid">
<div class="journal">
<p class="dropcap">Natlan's terraced plazas step down a hillside like a stadium built for celebration rather than sport, and on most nights that's roughly what they're used for — drum-led processions circle the tiers, torches are lit in sequence, and the whole hill glows by the time the procession reaches the top.</p>
<p>Meals here are communal by default: long grills set up at the plaza edges, smoke drifting over shared tables, and food passed around rather than plated individually. Arriving hungry is a reasonable strategy.</p>
<p>Beyond the plazas, highland trails open onto wide, dry country suited to saddle travel, and riding out from the terraces at dawn — before the day's heat and noise arrive — is one of the quieter ways to see Natlan.</p>
</div>
<aside class="field-note">
<h3>Field notes</h3>
<p>Festival nights run loud and late; light sleepers may prefer lodging a tier or two off the main plaza.</p>
<p>Grilled fare is shared, not ordered individually — follow your table's lead on portions.</p>
<p>Highland rides start at dawn to beat the heat; saddle time is roughly four hours.</p>
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
<div class="len">3 days / 2 nights</div>
<h3>Festival Nights Package</h3>
<p>Plaza-side lodging timed to a drum procession, with a shared grill dinner.</p>
</article>
<article class="package-card">
<div class="len">4 days / 3 nights</div>
<h3>Highland Saddle Trail</h3>
<p>A guided riding route through the dry highland country above the terraces.</p>
</article>
<article class="package-card">
<div class="len">2 days / 1 night</div>
<h3>Drum &amp; Flame Culinary Tour</h3>
<p>A tasting circuit through the plaza's best smoke-grill stalls.</p>
</article>
</div>
</div>
</section>
<section class="cta-band">
<div class="container cta-inner">
<div>
<h2>Plan your route to Natlan</h2>
<p>Ask us which week lands on a major procession — the terraces are worth timing your whole trip around.</p>
</div>
<div style="display:flex; gap:12px; flex-wrap:wrap;">
<a class="btn btn-brass" href="{{ route('home') }}">Request a dossier</a>
<a class="btn btn-outline" href="mailto:desk@teyvattravel.co">Email the desk</a>
</div>
</div>
</section>
@endsection
