<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateClick extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'offer_id',
        'session_id',
        'referer',
        'utm',
    ];

    protected $casts = [
        'utm' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the product associated with the click.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the offer associated with the click.
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }
}
