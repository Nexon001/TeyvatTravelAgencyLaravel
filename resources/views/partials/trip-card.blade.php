{{--
    One itinerary card.
    Expects: $it (itinerary array), $n (nation array), $slug, $order, $uid, $variant ('nation' | 'home')
--}}
@php
    $nights = $it['nights'];
    $duration = $it['days'].' Days / '.$nights.' '.($nights === 1 ? 'Night' : 'Nights');
    $detailsId = 'details-'.$uid;
    $nationUrl = route($slug).'#itineraries';
@endphp
<article class="trip-card" data-price="{{ $it['price'] }}" data-nights="{{ $nights }}" data-order="{{ $order }}">
    <div class="trip-media">
        @if ($variant === 'home')
            <a href="{{ $nationUrl }}" tabindex="-1" aria-hidden="true">
                <img src="{{ asset($it['image']) }}" alt="" loading="lazy">
            </a>
        @else
            <img src="{{ asset($it['image']) }}" alt="{{ $it['title'] }}" loading="lazy">
        @endif
        @if (!empty($it['signature']))
            <span class="trip-ribbon">Signature</span>
        @endif
        <span class="trip-badge">{{ $duration }}</span>
    </div>

    <div class="trip-body">
        <h3 class="trip-title">
            @if ($variant === 'home')
                <a href="{{ $nationUrl }}">{{ $it['title'] }}</a>
            @else
                {{ $it['title'] }}
            @endif
        </h3>
        <p class="trip-meta"><svg class="icon"><use href="#i-pin"/></svg>{{ $n['name'] }}</p>
        <p class="trip-meta"><svg class="icon"><use href="#i-tag"/></svg>{{ $it['tags'] }}</p>
        <p class="trip-price">₱{{ number_format($it['price']) }} <small>/ person</small></p>

        @if ($variant === 'nation')
            <div class="trip-actions">
                <button type="button" class="btn btn-gold btn-sm" data-toggle-details aria-expanded="false" aria-controls="{{ $detailsId }}">
                    <span class="label">View Details</span> <svg class="icon"><use href="#i-arrow-right"/></svg>
                </button>
                <button type="button" class="btn btn-outline btn-sm" data-action="book-now" data-itinerary="{{ $uid }}">Book Now</button>
            </div>

            <div class="trip-details" id="{{ $detailsId }}" hidden>
                <p>{{ $it['desc'] }}</p>
                @if (!empty($it['stops']))
                    <h4>Featured stops</h4>
                    <ul class="detail-list">
                        @foreach ($it['stops'] as $stop)
                            <li><svg class="icon"><use href="#i-pin"/></svg>{{ $stop }}</li>
                        @endforeach
                    </ul>
                @endif
                <h4>Inclusions</h4>
                <ul class="detail-list">
                    <li><svg class="icon"><use href="#i-check"/></svg>{{ $nights }} {{ $nights === 1 ? 'night' : 'nights' }} accommodation</li>
                    <li><svg class="icon"><use href="#i-check"/></svg>Daily breakfast</li>
                    <li><svg class="icon"><use href="#i-check"/></svg>Guided tour</li>
                    <li><svg class="icon"><use href="#i-check"/></svg>Transport (land &amp; local)</li>
                    <li><svg class="icon"><use href="#i-check"/></svg>Travel insurance</li>
                </ul>
            </div>
        @else
            <div class="trip-actions">
                <button type="button" class="btn btn-gold btn-sm btn-block" data-action="book-now" data-itinerary="{{ $uid }}">Book Now</button>
            </div>
        @endif
    </div>
</article>