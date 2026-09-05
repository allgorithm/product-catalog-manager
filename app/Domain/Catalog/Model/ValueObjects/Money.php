<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model\ValueObjects;

use InvalidArgumentException;

final readonly class Money
{
    public function __construct(
        public int $amountInCents,
        public string $currency = 'EUR'
    ) {
        if ($this->amountInCents < 0) {
            throw new InvalidArgumentException('Price cannot be negative.');
        }

        if (trim($this->currency) === '') {
            throw new InvalidArgumentException('Currency must be specified.');
        }
    }

    public static function fromCents(int $cents, string $currency = 'EUR'): self
    {
        return new self($cents, strtoupper($currency));
    }

    public static function fromFloat(float $amount, string $currency = 'EUR'): self
    {
        return new self((int) round($amount * 100), strtoupper($currency));
    }

    public function toFloat(): float
    {
        return $this->amountInCents / 100;
    }

    public function equals(Money $other): bool
    {
        return $this->amountInCents === $other->amountInCents && $this->currency === $other->currency;
    }

    public function formatted(): string
    {
        return number_format($this->toFloat(), 2, ',', '.').' '.$this->currency;
    }
}
