<section class="content" style="padding-top:8px;">
    <div class="container">
        <div class="section-head-row">
            <h2 style="margin:0;">Gallery</h2>
        </div>
        <div class="gallery" data-gallery="{{ $nation }}">
            @foreach($photos as $photo)
                <button type="button" class="gallery-thumb" data-caption="{{ $photo['caption'] }}">
                    <img src="{{ asset($photo['src']) }}" alt="{{ $photo['caption'] }}">
                    <span class="gallery-thumb-caption">{{ $photo['caption'] }}</span>
                </button>
            @endforeach
        </div>
    </div>
</section>

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