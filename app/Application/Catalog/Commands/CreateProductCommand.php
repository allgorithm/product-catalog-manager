<?php

declare(strict_types=1);

namespace App\Application\Catalog\Commands;

final readonly class CreateProductCommand
{
    /**
     * @param  list<array{sku: string, size: string, color_name: string, color_hex?: ?string, price?: ?float}>  $variants
     * @param  list<array{url: string, alt_text?: ?string, is_primary?: bool}>  $media
     */
    public function __construct(
        public string $name,
        public string $sku,
        public ?string $categoryId,
        public float $basePrice,
        public string $currency = 'EUR',
        public array $variants = [],
        public array $media = [],
        public string $status = 'draft',
        public ?string $id = null
    ) {}
}
