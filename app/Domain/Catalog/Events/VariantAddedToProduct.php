<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Events;

use App\Domain\Catalog\Model\ValueObjects\ProductId;
use App\Domain\Catalog\Model\ValueObjects\Sku;
use App\Domain\Catalog\Model\ValueObjects\VariantId;
use DateTimeImmutable;

final readonly class VariantAddedToProduct
{
    public DateTimeImmutable $occurredOn;

    public function __construct(
        public ProductId $productId,
        public VariantId $variantId,
        public Sku $variantSku
    ) {
        $this->occurredOn = new DateTimeImmutable;
    }
}
