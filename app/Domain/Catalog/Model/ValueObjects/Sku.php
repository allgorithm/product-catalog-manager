<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model\ValueObjects;

use InvalidArgumentException;

final readonly class Sku
{
    public function __construct(public string $value)
    {
        $trimmed = trim($this->value);
        if ($trimmed === '') {
            throw new InvalidArgumentException('SKU cannot be empty.');
        }

        if (! preg_match('/^[A-Za-z0-9\-_.]+$/', $trimmed)) {
            throw new InvalidArgumentException("Invalid SKU format: [{$trimmed}]. SKU can only contain alphanumeric characters, dashes, dots and underscores.");
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function equals(Sku $other): bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
