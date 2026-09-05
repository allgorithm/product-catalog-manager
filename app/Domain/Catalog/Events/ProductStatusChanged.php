<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Events;

use App\Domain\Catalog\Model\ValueObjects\ProductId;
use App\Domain\Catalog\Model\ValueObjects\ProductStatus;
use DateTimeImmutable;

final readonly class ProductStatusChanged
{
    public DateTimeImmutable $occurredOn;

    public function __construct(
        public ProductId $productId,
        public ProductStatus $previousStatus,
        public ProductStatus $newStatus
    ) {
        $this->occurredOn = new DateTimeImmutable;
    }
}
