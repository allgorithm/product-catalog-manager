<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model\ValueObjects;

use InvalidArgumentException;

final readonly class ProductId
{
    public function __construct(public string $value)
    {
        if (trim($this->value) === '') {
            throw new InvalidArgumentException('ProductId cannot be empty.');
        }
    }

    public static function generate(): self
    {
        // Simple robust UUIDv4 / UUIDv7 generator or random string
        return new self(sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0x0FFF) | 0x4000,
            mt_rand(0, 0x3FFF) | 0x8000,
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF)
        ));
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function equals(ProductId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
