@extends('layouts.app')

@section('title', 'Mondstadt')
@section('description', 'Plan a trip to Mondstadt with Teyvat Travel Co.: itineraries, field notes and seasonal guidance for the Mondstadt chapter of your journey.')
@section('body-class', 'chapter-mondstadt')

@section('content')
<section class="hero">
<div class="container hero-inner">
<div>
<h1>Mondstadt</h1>
<p class="hero-tagline">Where the wind writes the map for you.</p>
</div>
<p class="hero-lede">A hill-country of open squares, vineyard terraces and clifftop windmills, Mondstadt rewards travellers who are happy to let a festival, a breeze, or a stranger's recommendation reroute the day.</p>
<div class="hero-banner">
<img src="{{ asset('images/nations/mondstadt-hero.jpg') }}" alt="Rolling hills with a windmill under an open sky">
</div>
</div>
</section>
<section aria-label="At a glance">
<div class="container">
<dl class="facts">
<div class="fact"><dt>Best season</dt><dd>Late spring to early autumn</dd></div>
<div class="fact"><dt>Known for</dt><dd>Vineyard valleys &amp; clifftop windmills</dd></div>
<div class="fact"><dt>Signature taste</dt><dd>Hillside orchard wine</dd></div>
<div class="fact"><dt>Travel pace</dt><dd>Easygoing, festival-led</dd></div>
</dl>
</div>
</section>
<section class="content">
<div class="container content-grid">
<div class="journal">
<p class="dropcap">The first thing most travellers notice is how loudly Mondstadt welcomes them: an open plaza with a fountain at its centre, a lute being played badly and joyfully by someone off-duty, a festival banner going up for no occasion in particular. The city treats hospitality as a civic virtue, and that spirit tends to follow visitors out into the countryside.</p>
<p>Beyond the walls, the land rolls into vineyard terraces and windmill-topped hills that catch the coastal wind almost constantly — which is exactly why the windmills are there. Footpaths thread between the vines, and most estates are happy to let a passing traveller taste whatever is being pressed that week.</p>
<p>Evenings belong to the plaza again: long shared tables, a lot of bread, and conversation that outlasts the meal. Travellers who arrive with no plan for the evening rarely regret it — someone will have one for you.</p>
</div>
<aside class="field-note">
<h3>Field notes</h3>
<p>Pack a light layer for the hilltops; the wind off the cliffs runs cooler than the valley floor.</p>
<p>The best windmill views come an hour before sunset, when the light sits flat across the vine rows.</p>
<p>If a local invites you to a table, sit — refusing is considered stranger than accepting.</p>
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
<h3>Windmill Valley Circuit</h3>
<p>A slow loop through the vineyard hills, ending at a working windmill at golden hour.</p>
</article>
<article class="package-card">
<div class="len">3 days / 2 nights</div>
<h3>Harvest Festival Weekend</h3>
<p>Timed to the autumn harvest, with plaza feasts and open-cellar tastings.</p>
</article>
<article class="package-card">
<div class="len">2 days / 1 night</div>
<h3>Cliffside Trail Trek</h3>
<p>A guided coastal walk above the vine terraces, with a picnic lunch at the ridge.</p>
</article>
</div>
</div>
</section>
<section class="cta-band">
<div class="container cta-inner">
<div>
<h2>Plan your route to Mondstadt</h2>
<p>Our desk holds standing relationships with three hillside estates and can build a route around whichever festival lands during your travel window.</p>
</div>
<div style="display:flex; gap:12px; flex-wrap:wrap;">
<a class="btn btn-brass" href="{{ route('home') }}">Request a dossier</a>
<a class="btn btn-outline" href="mailto:desk@teyvattravel.co">Email the desk</a>
</div>
</div>
</section>
@endsection
