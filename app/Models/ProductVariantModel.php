<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantModel extends Model
{
    use HasUuids;

    protected $table = 'product_variants';

    protected $fillable = [
        'id',
        'product_id',
        'sku',
        'size',
        'color_name',
        'color_hex',
        'price_cents',
        'currency',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }
}
