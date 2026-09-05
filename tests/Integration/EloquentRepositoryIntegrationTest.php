<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Domain\Catalog\Model\Category;
use App\Domain\Catalog\Model\Product;
use App\Domain\Catalog\Model\ValueObjects\CategoryId;
use App\Domain\Catalog\Model\ValueObjects\Color;
use App\Domain\Catalog\Model\ValueObjects\Money;
use App\Domain\Catalog\Model\ValueObjects\ProductId;
use App\Domain\Catalog\Model\ValueObjects\ProductMedia;
use App\Domain\Catalog\Model\ValueObjects\ProductStatus;
use App\Domain\Catalog\Model\ValueObjects\Size;
use App\Domain\Catalog\Model\ValueObjects\Sku;
use App\Domain\Catalog\Model\ValueObjects\VariantId;
use App\Infrastructure\Catalog\Repositories\EloquentCategoryRepository;
use App\Infrastructure\Catalog\Repositories\EloquentProductRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EloquentRepositoryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private EloquentProductRepository $productRepo;

    private EloquentCategoryRepository $categoryRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productRepo = new EloquentProductRepository;
        $this->categoryRepo = new EloquentCategoryRepository;
    }

    public function test_it_persists_and_reconstitutes_category_and_product_aggregate(): void
    {
        // 1. Create Category
        $categoryId = CategoryId::fromString('cat-shirts');
        $category = Category::create($categoryId, 'T-Shirts', 't-shirts', 'All shirts');
        $this->categoryRepo->save($category);

        $savedCategory = $this->categoryRepo->findById($categoryId);
        $this->assertNotNull($savedCategory);
        $this->assertSame('T-Shirts', $savedCategory->name);

        // 2. Create Product Aggregate
        $productId = ProductId::generate();
        $sku = Sku::fromString('SHIRT-BASE');
        $product = Product::create(
            $productId,
            $sku,
            'Classic Tee',
            $categoryId,
            Money::fromFloat(29.99),
            ProductStatus::DRAFT
        );

        $product->addVariant(
            VariantId::generate(),
            Sku::fromString('SHIRT-M-WHITE'),
            Size::fromString('M'),
            Color::fromName('White', '#ffffff'),
            Money::fromFloat(29.99)
        );

        $product->addVariant(
            VariantId::generate(),
            Sku::fromString('SHIRT-L-WHITE'),
            Size::fromString('L'),
            Color::fromName('White', '#ffffff'),
            Money::fromFloat(32.99)
        );

        $product->attachMedia(ProductMedia::create('https://cdn.test/shirt.jpg', 'Shirt Front', true));

        // Save through Eloquent Repository
        $this->productRepo->save($product);

        // 3. Reconstitute via findById
        $reconstituted = $this->productRepo->findById($productId);
        $this->assertNotNull($reconstituted);
        $this->assertSame('Classic Tee', $reconstituted->name);
        $this->assertTrue($reconstituted->sku->equals($sku));
        $this->assertSame(2999, $reconstituted->basePrice->amountInCents);
        $this->assertCount(2, $reconstituted->variants());
        $this->assertCount(1, $reconstituted->media());

        // 4. Test findBySku
        $bySku = $this->productRepo->findBySku($sku);
        $this->assertNotNull($bySku);
        $this->assertTrue($bySku->id->equals($productId));
    }
}
