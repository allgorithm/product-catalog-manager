<?php

declare(strict_types=1);

namespace App\Application\Catalog\Commands;

final readonly class BulkChangeProductStatusCommand
{
    /**
     * @param  list<string>  $productIds
     */
    public function __construct(
        public array $productIds,
        public string $targetStatus
    ) {}
}
