<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model;

use App\Domain\Catalog\Model\ValueObjects\Color;
use App\Domain\Catalog\Model\ValueObjects\Money;
use App\Domain\Catalog\Model\ValueObjects\Size;
use App\Domain\Catalog\Model\ValueObjects\Sku;
use App\Domain\Catalog\Model\ValueObjects\VariantId;

final class ProductVariant
{
    public function __construct(
        public readonly VariantId $id,
        public Sku $sku,
        public Size $size,
        public Color $color,
        public ?Money $price = null
    ) {}

    public static function create(
        VariantId $id,
        Sku $sku,
        Size $size,
        Color $color,
        ?Money $price = null
    ): self {
        return new self($id, $sku, $size, $color, $price);
    }

    public function updateDetails(Sku $sku, Size $size, Color $color, ?Money $price): void
    {
        $this->sku = $sku;
        $this->size = $size;
        $this->color = $color;
        $this->price = $price;
    }

    public function hasCombination(Size $size, Color $color): bool
    {
        return $this->size->equals($size) && $this->color->equals($color);
    }
}
