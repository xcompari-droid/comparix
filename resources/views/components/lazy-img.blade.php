<picture>
    @if(isset($webp))
        <source type="image/webp" srcset="{{ $webp }}">
    @endif
    @if(isset($srcset))
        <source srcset="{{ $srcset }}">
    @endif
        <img
            src="{{ (!empty($src) && $src !== asset('img/blank.gif')) ? ($placeholder ?? $src) : asset('images/placeholder.webp') }}"
            data-src="{{ !empty($src) ? $src : asset('images/placeholder.webp') }}"
            class="lazy w-full h-auto {{ $class ?? '' }}"
            alt="{{ $alt ?? 'Fără imagine' }}"
            loading="lazy" decoding="async">
</picture>
