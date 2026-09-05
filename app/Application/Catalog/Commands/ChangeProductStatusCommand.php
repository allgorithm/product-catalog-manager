<?php

declare(strict_types=1);

namespace App\Application\Catalog\Commands;

final readonly class ChangeProductStatusCommand
{
    public function __construct(
        public string $productId,
        public string $targetStatus
    ) {}
}
