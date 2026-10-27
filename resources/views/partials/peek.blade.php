{{--
    Hover photo popup. Reusable on any element.
    Usage:   <li class="has-peek" tabindex="0">
                 ...your content...
                 @include('partials.peek', ['src' => asset('images/nations/gallery/liyue-2.jpg')])
             </li>
    Params:  $src    (required) full image URL
             $pos    (optional) '' = above, left-aligned (default)   'below'   'right' = aligned to the right edge
    The host element needs the class "has-peek" and tabindex="0" (keyboard + tap support).
    Styles live in public/css/style.css under "hover photo popup".
--}}
<span class="peek{{ ($pos ?? '') !== '' ? ' peek--'.str_replace(' ', ' peek--', $pos) : '' }}" aria-hidden="true">
    <img src="{{ $src }}" alt="" loading="lazy" width="520" height="293">
</span>
