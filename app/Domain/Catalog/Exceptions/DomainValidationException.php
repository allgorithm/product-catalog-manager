<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Exceptions;

use DomainException;

final class DomainValidationException extends DomainException
{
    public static function forEmptyName(): self
    {
        return new self('Product name cannot be empty.');
    }

    public static function forDuplicateVariant(string $size, string $color): self
    {
        return new self("A variant with Size [{$size}] and Color [{$color}] already exists for this product.");
    }

    public static function forCannotActivate(string $reason): self
    {
        return new self("Product cannot be activated: {$reason}");
    }

    public static function forArchivedModification(): self
    {
        return new self('An archived product cannot be modified directly. Please unarchive first.');
    }
}
