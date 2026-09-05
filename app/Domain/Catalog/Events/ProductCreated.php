<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Events;

use App\Domain\Catalog\Model\ValueObjects\ProductId;
use App\Domain\Catalog\Model\ValueObjects\Sku;
use DateTimeImmutable;

final readonly class ProductCreated
{
    public DateTimeImmutable $occurredOn;

    public function __construct(
        public ProductId $productId,
        public Sku $sku,
        public string $name
    ) {
        $this->occurredOn = new DateTimeImmutable;
    }
}
