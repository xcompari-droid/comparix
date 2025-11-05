<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'merchant',
        'price',
        'currency',
        'url_affiliate',
        'in_stock',
        'last_seen_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'in_stock' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    /**
     * Get the product that owns the offer.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the affiliate clicks for the offer.
     */
    public function affiliateClicks(): HasMany
    {
        return $this->hasMany(AffiliateClick::class);
    }
}
