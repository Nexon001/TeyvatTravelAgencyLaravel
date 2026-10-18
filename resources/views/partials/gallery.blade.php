{{-- Horizontal photo strip + lightbox. Expects: $nation (slug), $photos (list of ['src', 'caption']) --}}
<div class="gallery" data-gallery="{{ $nation }}">
@foreach ($photos as $photo)
    <button type="button" class="gallery-thumb" data-caption="{{ $photo['caption'] }}">
        <img src="{{ asset($photo['src']) }}" alt="{{ $photo['caption'] }}" loading="lazy">
        <span class="gallery-thumb-caption">{{ $photo['caption'] }}</span>
    </button>
@endforeach
</div>

<div class="lightbox" data-lightbox="{{ $nation }}" aria-hidden="true">
    <div class="lightbox-backdrop"></div>
    <button type="button" class="lightbox-close" aria-label="Close gallery">✕</button>
    <button type="button" class="lightbox-prev" aria-label="Previous photo">‹</button>
    <button type="button" class="lightbox-next" aria-label="Next photo">›</button>
    <div class="lightbox-inner" role="dialog" aria-modal="true" aria-label="{{ ucfirst($nation) }} gallery">
        <img class="lightbox-image" src="" alt="">
        <p class="lightbox-caption"></p>
    </div>
</div>