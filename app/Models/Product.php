<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, Searchable, InteractsWithMedia;

    protected $fillable = [
        'product_type_id',
        'name',
        'brand',
        'mpn',
        'ean',
        'short_desc',
        'image_url',
        'score',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        try {
            $this->loadMissing(['productType.category', 'specValues.specKey', 'offers', 'affiliateClicks']);
            
            return [
                'id' => $this->id,
                'name' => $this->name,
                'brand' => $this->brand,
                'category' => optional(optional($this->productType)->category)->name ?? null,
                'price_min' => $this->offers->min('price') ?? 0,
                'rating' => $this->score,
                'specs' => $this->specValues->mapWithKeys(function ($specValue) {
                    return [optional($specValue->specKey)->name ?? 'unknown' => $specValue->value_string ?? $specValue->value_number ?? $specValue->value_bool];
                })->toArray(),
                'popularity' => $this->affiliateClicks->count(),
            ];
        } catch (\Exception $e) {
            \Log::error('Scout indexing error for Product #' . $this->id . ': ' . $e->getMessage());
            // Fallback to basic data if relationships fail
            return [
                'id' => $this->id,
                'name' => $this->name,
                'brand' => $this->brand,
            ];
        }
    }

    /**
     * Get the product type that owns the product.
     */
    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    /**
     * Get the spec values for the product.
     */
    public function specValues(): HasMany
    {
        return $this->hasMany(SpecValue::class);
    }

    /**
     * Get the offers for the product.
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * Get the affiliate clicks for the product.
     */
    public function affiliateClicks(): HasMany
    {
        return $this->hasMany(AffiliateClick::class);
    }

    /**
     * Get the reviews for the product.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the favorites for the product.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the price alerts for the product.
     */
    public function priceAlerts(): HasMany
    {
        return $this->hasMany(PriceAlert::class);
    }

    /**
     * Get average rating from reviews.
     */
    public function averageRating(): float
    {
        return $this->reviews()->approved()->avg('rating') ?? 0;
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useFallbackUrl('/images/product-placeholder.png');
    }
}
