<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Comparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'product_ids',
        'created_by',
    ];

    protected $casts = [
        'product_ids' => 'array',
    ];

    /**
     * Get the user who created the comparison.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the products in this comparison.
     */
    public function products(): Collection
    {
        return Product::whereIn('id', $this->product_ids)->get();
    }
}
