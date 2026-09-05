<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model\ValueObjects;

use InvalidArgumentException;

final readonly class Color
{
    public function __construct(
        public string $name,
        public ?string $hexCode = null
    ) {
        $trimmed = trim($this->name);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Color name cannot be empty.');
        }

        if ($this->hexCode !== null && ! preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $this->hexCode)) {
            throw new InvalidArgumentException("Invalid hex color code: {$this->hexCode}");
        }
    }

    public static function fromName(string $name, ?string $hexCode = null): self
    {
        return new self($name, $hexCode);
    }

    public function equals(Color $other): bool
    {
        return strtolower($this->name) === strtolower($other->name);
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
