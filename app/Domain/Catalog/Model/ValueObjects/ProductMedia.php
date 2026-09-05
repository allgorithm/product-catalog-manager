<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model\ValueObjects;

use InvalidArgumentException;

final readonly class ProductMedia
{
    public function __construct(
        public string $url,
        public ?string $altText = null,
        public bool $isPrimary = false
    ) {
        if (trim($this->url) === '') {
            throw new InvalidArgumentException('Media URL cannot be empty.');
        }
    }

    public static function create(string $url, ?string $altText = null, bool $isPrimary = false): self
    {
        return new self($url, $altText, $isPrimary);
    }
}
