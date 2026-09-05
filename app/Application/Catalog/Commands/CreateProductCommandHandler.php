<?php

declare(strict_types=1);

namespace App\Application\Catalog\Commands;

use App\Application\Catalog\Ports\EventBusPort;
use App\Domain\Catalog\Model\Product;
use App\Domain\Catalog\Model\ValueObjects\CategoryId;
use App\Domain\Catalog\Model\ValueObjects\Color;
use App\Domain\Catalog\Model\ValueObjects\Money;
use App\Domain\Catalog\Model\ValueObjects\ProductId;
use App\Domain\Catalog\Model\ValueObjects\ProductMedia;
use App\Domain\Catalog\Model\ValueObjects\ProductStatus;
use App\Domain\Catalog\Model\ValueObjects\Size;
use App\Domain\Catalog\Model\ValueObjects\Sku;
use App\Domain\Catalog\Model\ValueObjects\VariantId;
use App\Domain\Catalog\Repositories\ProductRepositoryInterface;
use App\Domain\Catalog\Specifications\ProductCanBeActivatedSpecification;

final readonly class CreateProductCommandHandler
{
    public function __construct(
        private ProductRepositoryInterface $repository,
        private EventBusPort $eventBus,
        private ProductCanBeActivatedSpecification $canBeActivatedSpec
    ) {}

    public function handle(CreateProductCommand $command): ProductId
    {
        $productId = $command->id ? ProductId::fromString($command->id) : ProductId::generate();
        $sku = Sku::fromString($command->sku);
        $categoryId = $command->categoryId ? CategoryId::fromString($command->categoryId) : null;
        $basePrice = Money::fromFloat($command->basePrice, $command->currency);
        $initialStatus = ProductStatus::from($command->status);

        // Always initialize in draft to attach variants & media before potential activation
        $product = Product::create($productId, $sku, $command->name, $categoryId, $basePrice, ProductStatus::DRAFT);

        // Add variants
        foreach ($command->variants as $variantData) {
            $variantId = VariantId::generate();
            $variantSku = Sku::fromString($variantData['sku']);
            $size = Size::fromString($variantData['size']);
            $color = Color::fromName($variantData['color_name'], $variantData['color_hex'] ?? null);
            $price = isset($variantData['price']) && $variantData['price'] !== null
                ? Money::fromFloat((float) $variantData['price'], $command->currency)
                : null;

            $product->addVariant($variantId, $variantSku, $size, $color, $price);
        }

        // Attach media
        foreach ($command->media as $mediaItem) {
            $product->attachMedia(ProductMedia::create(
                url: $mediaItem['url'],
                altText: $mediaItem['alt_text'] ?? null,
                isPrimary: (bool) ($mediaItem['is_primary'] ?? false)
            ));
        }

        // Transition to requested status if not draft
        if ($initialStatus !== ProductStatus::DRAFT) {
            $product->transitionTo($initialStatus, $this->canBeActivatedSpec);
        }

        $this->repository->save($product);
        $this->eventBus->dispatchAll($product->releaseEvents());

        return $productId;
    }
}
