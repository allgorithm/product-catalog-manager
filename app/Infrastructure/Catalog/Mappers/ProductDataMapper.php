<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog\Mappers;

use App\Domain\Catalog\Model\Category;
use App\Domain\Catalog\Model\Product;
use App\Domain\Catalog\Model\ProductVariant;
use App\Domain\Catalog\Model\ValueObjects\CategoryId;
use App\Domain\Catalog\Model\ValueObjects\Color;
use App\Domain\Catalog\Model\ValueObjects\Money;
use App\Domain\Catalog\Model\ValueObjects\ProductId;
use App\Domain\Catalog\Model\ValueObjects\ProductMedia;
use App\Domain\Catalog\Model\ValueObjects\ProductStatus;
use App\Domain\Catalog\Model\ValueObjects\Size;
use App\Domain\Catalog\Model\ValueObjects\Sku;
use App\Domain\Catalog\Model\ValueObjects\VariantId;
use App\Models\CategoryModel;
use App\Models\ProductModel;

final class ProductDataMapper
{
    public static function toDomain(ProductModel $model): Product
    {
        $product = new Product(
            id: ProductId::fromString($model->id),
            sku: Sku::fromString($model->sku),
            name: $model->name,
            categoryId: $model->category_id ? CategoryId::fromString($model->category_id) : null,
            basePrice: Money::fromCents($model->base_price_cents, $model->currency),
            status: $model->status instanceof ProductStatus ? $model->status : ProductStatus::from((string) $model->status)
        );

        // Map variants
        if ($model->relationLoaded('variants')) {
            $domainVariants = [];
            foreach ($model->variants as $variantModel) {
                $domainVariants[] = new ProductVariant(
                    id: VariantId::fromString($variantModel->id),
                    sku: Sku::fromString($variantModel->sku),
                    size: Size::fromString($variantModel->size),
                    color: Color::fromName($variantModel->color_name, $variantModel->color_hex),
                    price: $variantModel->price_cents !== null
                        ? Money::fromCents($variantModel->price_cents, $variantModel->currency)
                        : null
                );
            }
            $product->setVariants($domainVariants);
        }

        // Map media
        if ($model->relationLoaded('media')) {
            $domainMedia = [];
            foreach ($model->media as $mediaModel) {
                $domainMedia[] = new ProductMedia(
                    url: $mediaModel->url,
                    altText: $mediaModel->alt_text,
                    isPrimary: (bool) $mediaModel->is_primary
                );
            }
            $product->setMedia($domainMedia);
        }

        return $product;
    }

    public static function toCategoryDomain(CategoryModel $model): Category
    {
        return new Category(
            id: CategoryId::fromString($model->id),
            name: $model->name,
            slug: $model->slug,
            description: $model->description
        );
    }
}
