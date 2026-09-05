<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model\ValueObjects;

enum ProductStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case ARCHIVED = 'archived';

    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isArchived(): bool
    {
        return $this === self::ARCHIVED;
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Entwurf',
            self::ACTIVE => 'Aktiv',
            self::ARCHIVED => 'Archiviert',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::ACTIVE => 'success',
            self::ARCHIVED => 'danger',
        };
    }
}
