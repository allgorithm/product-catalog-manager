<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog\Repositories;

use App\Domain\Catalog\Model\Category;
use App\Domain\Catalog\Model\ValueObjects\CategoryId;
use App\Domain\Catalog\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Catalog\Mappers\ProductDataMapper;
use App\Models\CategoryModel;

final class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function save(Category $category): void
    {
        CategoryModel::updateOrCreate(
            ['id' => $category->id->value],
            [
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
            ]
        );
    }

    public function findById(CategoryId $id): ?Category
    {
        $model = CategoryModel::find($id->value);

        return $model ? ProductDataMapper::toCategoryDomain($model) : null;
    }

    public function findAll(): array
    {
        return CategoryModel::all()
            ->map(fn (CategoryModel $model) => ProductDataMapper::toCategoryDomain($model))
            ->all();
    }
}
