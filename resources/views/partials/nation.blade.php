{{-- Full nation landing page body. Expects: $n (nation array from resources/data/nations.php), $slug --}}
@php
    $name = $n['name'];
    $factIcons = ['sun', 'star', 'cup', 'clock'];
    $featureIcons = ['landmark', 'compass', 'sparkles', 'heart'];
    $photos = [];
    foreach ($n['gallery'] as $i => $caption) {
        $photos[] = ['src' => 'images/nations/gallery/'.$slug.'-'.($i + 1).'.jpg', 'caption' => $caption];
    }
@endphp

{{-- HERO --}}
<section class="hero-nation" style="background-image:url('{{ asset('images/nations/'.$slug.'-hero.jpg') }}');">
    <div class="container hero-nation-inner">
        <p class="hero-eyebrow"><svg class="icon"><use href="#i-compass"/></svg>{{ $n['eyebrow'] }}</p>
        <h1>{{ $name }}</h1>
        <p class="hero-tagline">{{ $n['tagline'] }}</p>
        <p class="hero-lede">{{ $n['lede'] }}</p>
        <a class="btn btn-gold btn-pill" href="#itineraries">Explore Itineraries <svg class="icon"><use href="#i-arrow-right"/></svg></a>
    </div>
    <span class="hero-loc"><svg class="icon"><use href="#i-pin"/></svg>{{ $name }}</span>
</section>

{{-- AT A GLANCE --}}
<section class="facts-band" aria-label="At a glance">
    <div class="container">
        <ul class="facts">
            @foreach ($n['facts'] as $i => $fact)
                <li class="fact">
                    <span class="icon-badge"><svg class="icon"><use href="#i-{{ $factIcons[$i] }}"/></svg></span>
                    <div>
                        <span class="fact-label">{{ $fact[0] }}</span>
                        <strong class="fact-value">{{ $fact[1] }}</strong>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</section>
{{-- GALLERY --}}
<section class="section" id="gallery">
    <div class="container">
        <div class="section-head-row">
            <div>
                <span class="eyebrow">Gallery</span>
                <h2>{{ $name }} in Pictures</h2>
            </div>
            <span class="scroll-hint">Scroll <svg class="icon"><use href="#i-arrow-right"/></svg></span>
        </div>
        @include('partials.gallery', ['nation' => $slug, 'photos' => $photos])

        <div class="custom-strip">
            <span class="icon-badge"><svg class="icon"><use href="#i-mail"/></svg></span>
            <div>
                <strong>Need a custom itinerary?</strong>
                <p>We can tailor the trip to your preferences, group size, and schedule.</p>
            </div>
            <a class="btn btn-outline btn-sm" href="mailto:desk@teyvattravel.co">Contact Us <svg class="icon"><use href="#i-arrow-right"/></svg></a>
        </div>
    </div>
</section>
{{-- WHY VISIT --}}
<section class="section">
    <div class="container highlights">
        <div class="highlights-copy">
            <span class="eyebrow">Highlights</span>
            <h2>Why Visit {{ $name }}?</h2>
            @foreach ($n['story'] as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
            <ul class="feature-row">
                @foreach ($n['features'] as $i => $feature)
                    <li>
                        <span class="icon-badge"><svg class="icon"><use href="#i-{{ $featureIcons[$i] }}"/></svg></span>
                        <span>{{ $feature }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="highlights-side">
            <figure class="media-card">
                <img src="{{ asset('images/nations/gallery/'.$slug.'-2.jpg') }}" alt="{{ $n['gallery'][1] }}" loading="lazy">
                <figcaption>{{ $n['gallery'][1] }}</figcaption>
            </figure>
            <aside class="notes-card">
                <h3>Traveller's notes</h3>
                <ul class="detail-list">
                    @foreach ($n['notes'] as $note)
                        <li><svg class="icon"><use href="#i-check"/></svg>{{ $note }}</li>
                    @endforeach
                </ul>
            </aside>
        </div>
    </div>
</section>

{{-- ITINERARIES --}}
<section class="section section--alt" id="itineraries">
    <div class="container">
        <div class="section-head-row">
            <div>
                <span class="eyebrow">Itineraries</span>
                <h2>Available Itineraries</h2>
            </div>
            <label class="sort">
                <span>Sort by:</span>
                <select data-sort="#trip-list-{{ $slug }}" aria-label="Sort itineraries">
                    <option value="popularity">Popularity</option>
                    <option value="price-asc">Price: Low to High</option>
                    <option value="price-desc">Price: High to Low</option>
                    <option value="duration">Duration: Shortest</option>
                </select>
            </label>
        </div>
        <div class="trip-grid" id="trip-list-{{ $slug }}">
            @foreach ($n['itineraries'] as $i => $it)
                @include('partials.trip-card', ['it' => $it, 'n' => $n, 'slug' => $slug, 'order' => $i, 'uid' => $slug.'-'.$i, 'variant' => 'nation'])
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="cta-band">
    <div class="container cta-inner">
        <div>
            <p class="cta-kicker">Ready for your next adventure?</p>
            <h2>Explore more nations</h2>
            <p>Visit the other six nations and discover what makes Teyvat extraordinary.</p>
        </div>
        <a class="btn btn-gold btn-pill" href="{{ route('home') }}#destinations">View All Nations <svg class="icon"><use href="#i-arrow-right"/></svg></a>
    </div>
</section>