<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Specifications;

use App\Domain\Catalog\Model\Product;

final class ProductCanBeActivatedSpecification
{
    /**
     * @return array{isSatisfied: bool, reason: ?string}
     */
    public function check(Product $product): array
    {
        if ($product->categoryId === null) {
            return [
                'isSatisfied' => false,
                'reason' => 'A category must be assigned before activating the product.',
            ];
        }

        if (count($product->variants()) === 0) {
            return [
                'isSatisfied' => false,
                'reason' => 'At least one variant (Size & Color) must be defined.',
            ];
        }

        if ($product->basePrice->amountInCents <= 0) {
            return [
                'isSatisfied' => false,
                'reason' => 'A base price greater than 0 must be set.',
            ];
        }

        return [
            'isSatisfied' => true,
            'reason' => null,
        ];
    }

    public function isSatisfiedBy(Product $product): bool
    {
        return $this->check($product)['isSatisfied'];
    }
}
