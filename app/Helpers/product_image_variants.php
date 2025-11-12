<?php
// Helper pentru generarea automată a srcset și WebP pentru imagini de produs

if (!function_exists('product_image_variants')) {
    /**
     * Returnează array cu src, srcset și webp pentru imaginea unui produs
     * @param string $imageUrl
     * @return array
     */
    function product_image_variants($imageUrl)
    {
        if (!$imageUrl) return [
            'src' => null,
            'srcset' => null,
            'webp' => null,
        ];
        $ext = pathinfo($imageUrl, PATHINFO_EXTENSION);
        $base = preg_replace('/\.' . preg_quote($ext, '/') . '$/', '', $imageUrl);
        // Exemplu: /images/products/iphone-15.jpg => /images/products/iphone-15.webp
        $webp = $base . '.webp';
        // Exemplu: /images/products/iphone-15.jpg => /images/products/iphone-15-400.jpg, -800.jpg, -1200.jpg
        $srcset = $base . '-400.' . $ext . ' 400w, ' . $base . '-800.' . $ext . ' 800w, ' . $base . '-1200.' . $ext . ' 1200w';
        return [
            'src' => $imageUrl,
            'srcset' => $srcset,
            'webp' => $webp,
        ];
    }
}
