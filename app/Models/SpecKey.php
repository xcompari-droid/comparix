<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpecKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_type_id',
        'name',
        'slug',
        'unit',
        'weight',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    /**
     * Get the product type that owns the spec key.
     */
    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    /**
     * Get the spec values for the spec key.
     */
    public function specValues(): HasMany
    {
        return $this->hasMany(SpecValue::class);
    }
}
