<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Repositories;

use App\Domain\Catalog\Model\Category;
use App\Domain\Catalog\Model\ValueObjects\CategoryId;

interface CategoryRepositoryInterface
{
    public function save(Category $category): void;

    public function findById(CategoryId $id): ?Category;

    /**
     * @return list<Category>
     */
    public function findAll(): array;
}
