<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'spec_key_id',
        'value_string',
        'value_number',
        'value_bool',
    ];

    protected $casts = [
        'value_number' => 'decimal:6',
        'value_bool' => 'boolean',
    ];

    /**
     * Get the product that owns the spec value.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the spec key that owns the spec value.
     */
    public function specKey(): BelongsTo
    {
        return $this->belongsTo(SpecKey::class);
    }
}
