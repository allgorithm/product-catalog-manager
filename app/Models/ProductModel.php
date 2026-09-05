<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Catalog\Model\ValueObjects\ProductStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductModel extends Model
{
    use HasUuids;

    protected $table = 'products';

    protected $fillable = [
        'id',
        'sku',
        'name',
        'category_id',
        'base_price_cents',
        'currency',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'base_price_cents' => 'integer',
            'status' => ProductStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariantModel::class, 'product_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMediaModel::class, 'product_id')->orderBy('sort_order');
    }

    public function primaryMedia(): BelongsTo
    {
        return $this->belongsTo(ProductMediaModel::class, 'id', 'product_id')->where('is_primary', true);
    }
}
