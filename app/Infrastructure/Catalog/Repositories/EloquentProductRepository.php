<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog\Repositories;

use App\Domain\Catalog\Model\Product;
use App\Domain\Catalog\Model\ValueObjects\ProductId;
use App\Domain\Catalog\Model\ValueObjects\Sku;
use App\Domain\Catalog\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Catalog\Mappers\ProductDataMapper;
use App\Models\ProductMediaModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use Illuminate\Support\Facades\DB;

final class EloquentProductRepository implements ProductRepositoryInterface
{
    public function save(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $productModel = ProductModel::updateOrCreate(
                ['id' => $product->id->value],
                [
                    'sku' => $product->sku->value,
                    'name' => $product->name,
                    'category_id' => $product->categoryId?->value,
                    'base_price_cents' => $product->basePrice->amountInCents,
                    'currency' => $product->basePrice->currency,
                    'status' => $product->status->value,
                ]
            );

            // Sync variants: delete missing, upsert current
            $existingVariantIds = array_keys($product->variants());
            ProductVariantModel::where('product_id', $product->id->value)
                ->whereNotIn('id', $existingVariantIds)
                ->delete();

            foreach ($product->variants() as $variant) {
                ProductVariantModel::updateOrCreate(
                    ['id' => $variant->id->value],
                    [
                        'product_id' => $product->id->value,
                        'sku' => $variant->sku->value,
                        'size' => $variant->size->value,
                        'color_name' => $variant->color->name,
                        'color_hex' => $variant->color->hexCode,
                        'price_cents' => $variant->price?->amountInCents,
                        'currency' => $variant->price?->currency ?? $product->basePrice->currency,
                    ]
                );
            }

            // Sync media
            ProductMediaModel::where('product_id', $product->id->value)->delete();
            $sortOrder = 0;
            foreach ($product->media() as $media) {
                ProductMediaModel::create([
                    'product_id' => $product->id->value,
                    'url' => $media->url,
                    'alt_text' => $media->altText,
                    'is_primary' => $media->isPrimary,
                    'sort_order' => $sortOrder++,
                ]);
            }
        });
    }

    public function findById(ProductId $id): ?Product
    {
        $model = ProductModel::with(['variants', 'media', 'category'])->find($id->value);

        return $model ? ProductDataMapper::toDomain($model) : null;
    }

    public function findBySku(Sku $sku): ?Product
    {
        $model = ProductModel::with(['variants', 'media', 'category'])
            ->where('sku', $sku->value)
            ->first();

        return $model ? ProductDataMapper::toDomain($model) : null;
    }

    public function delete(ProductId $id): void
    {
        ProductModel::destroy($id->value);
    }
}
