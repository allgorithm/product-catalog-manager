<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model\ValueObjects;

use InvalidArgumentException;

final readonly class CategoryId
{
    public function __construct(public string $value)
    {
        if (trim($this->value) === '') {
            throw new InvalidArgumentException('CategoryId cannot be empty.');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function equals(CategoryId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
