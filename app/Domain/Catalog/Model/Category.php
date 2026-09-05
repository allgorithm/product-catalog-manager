<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model;

use App\Domain\Catalog\Model\ValueObjects\CategoryId;

final class Category
{
    public function __construct(
        public readonly CategoryId $id,
        public string $name,
        public string $slug,
        public ?string $description = null
    ) {}

    public static function create(
        CategoryId $id,
        string $name,
        string $slug,
        ?string $description = null
    ): self {
        return new self($id, $name, $slug, $description);
    }
}
