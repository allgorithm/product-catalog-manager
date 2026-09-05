<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model\ValueObjects;

use InvalidArgumentException;

final readonly class Size
{
    public function __construct(public string $value)
    {
        $trimmed = trim($this->value);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Size cannot be empty.');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function equals(Size $other): bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
