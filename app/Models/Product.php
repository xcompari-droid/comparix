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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'category' => $this->productType->category->name ?? null,
            'price_min' => $this->offers()->min('price'),
            'rating' => $this->score,
            'specs' => $this->specValues->mapWithKeys(function ($specValue) {
                return [$specValue->specKey->name => $specValue->value_string ?? $specValue->value_number ?? $specValue->value_bool];
            })->toArray(),
            'popularity' => $this->affiliateClicks()->count(),
        ];
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
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useFallbackUrl('/images/product-placeholder.png');
    }
}
