<section class="content" style="padding-top:0; padding-bottom:0;">
    <div class="container">
        <div class="signature-package">
            <span class="signature-package-tag">Signature Package</span>
            <div class="signature-package-head">
                <h2>{{ $title }}</h2>
                <p class="signature-package-price">{{ $price }}</p>
            </div>
            <div class="signature-package-body">
                <p class="signature-package-desc">{{ $description }}</p>
                <div class="signature-package-stops">
                    <h3>Featured stops</h3>
                    <ul>
                        @foreach($stops as $stop)
                            <li>{{ $stop }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>