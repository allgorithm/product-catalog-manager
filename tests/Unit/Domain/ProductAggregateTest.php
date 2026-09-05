<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Catalog\Events\ProductCreated;
use App\Domain\Catalog\Events\ProductStatusChanged;
use App\Domain\Catalog\Exceptions\DomainValidationException;
use App\Domain\Catalog\Model\Product;
use App\Domain\Catalog\Model\ValueObjects\CategoryId;
use App\Domain\Catalog\Model\ValueObjects\Color;
use App\Domain\Catalog\Model\ValueObjects\Money;
use App\Domain\Catalog\Model\ValueObjects\ProductId;
use App\Domain\Catalog\Model\ValueObjects\ProductMedia;
use App\Domain\Catalog\Model\ValueObjects\Size;
use App\Domain\Catalog\Model\ValueObjects\Sku;
use App\Domain\Catalog\Model\ValueObjects\VariantId;
use App\Domain\Catalog\Specifications\ProductCanBeActivatedSpecification;
use PHPUnit\Framework\TestCase;

final class ProductAggregateTest extends TestCase
{
    public function test_it_creates_product_and_records_event(): void
    {
        $productId = ProductId::generate();
        $sku = Sku::fromString('PROD-100');
        $basePrice = Money::fromFloat(49.99);
        $categoryId = CategoryId::fromString('cat-1');

        $product = Product::create(
            $productId,
            $sku,
            'Premium T-Shirt',
            $categoryId,
            $basePrice
        );

        $this->assertTrue($product->status->isDraft());
        $this->assertSame('Premium T-Shirt', $product->name);
        $this->assertTrue($product->sku->equals($sku));
        $this->assertTrue($product->basePrice->equals($basePrice));

        $events = $product->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProductCreated::class, $events[0]);
    }

    public function test_it_adds_variants_and_prevents_duplicate_combination(): void
    {
        $product = Product::create(
            ProductId::generate(),
            Sku::fromString('PROD-100'),
            'T-Shirt',
            CategoryId::fromString('cat-1'),
            Money::fromFloat(19.99)
        );

        $variant1 = $product->addVariant(
            VariantId::generate(),
            Sku::fromString('PROD-100-M-BLK'),
            Size::fromString('M'),
            Color::fromName('Black', '#000000'),
            Money::fromFloat(21.99)
        );

        $this->assertCount(1, $product->variants());
        $this->assertTrue($variant1->hasCombination(Size::fromString('M'), Color::fromName('Black')));

        // Expect exception on duplicate Size + Color
        $this->expectException(DomainValidationException::class);
        $product->addVariant(
            VariantId::generate(),
            Sku::fromString('PROD-100-M-BLK-2'),
            Size::fromString('M'),
            Color::fromName('Black'),
            null
        );
    }

    public function test_it_cannot_be_activated_without_variants(): void
    {
        $product = Product::create(
            ProductId::generate(),
            Sku::fromString('PROD-200'),
            'Sneaker',
            CategoryId::fromString('cat-shoes'),
            Money::fromFloat(89.99)
        );

        $spec = new ProductCanBeActivatedSpecification;
        $this->assertFalse($spec->isSatisfiedBy($product));

        $this->expectException(DomainValidationException::class);
        $product->activate($spec);
    }

    public function test_it_activates_successfully_when_specification_is_satisfied(): void
    {
        $product = Product::create(
            ProductId::generate(),
            Sku::fromString('PROD-300'),
            'Hoodie',
            CategoryId::fromString('cat-apparel'),
            Money::fromFloat(59.99)
        );

        $product->addVariant(
            VariantId::generate(),
            Sku::fromString('PROD-300-L-GRY'),
            Size::fromString('L'),
            Color::fromName('Heather Grey')
        );

        $product->attachMedia(ProductMedia::create('https://cdn.example.com/hoodie.jpg', 'Front View', true));

        $spec = new ProductCanBeActivatedSpecification;
        $this->assertTrue($spec->isSatisfiedBy($product));

        $product->activate($spec);
        $this->assertTrue($product->status->isActive());

        $events = $product->releaseEvents();
        $statusEvents = array_filter($events, fn ($e) => $e instanceof ProductStatusChanged);
        $this->assertCount(1, $statusEvents);
    }

    public function test_it_cannot_modify_archived_product(): void
    {
        $product = Product::create(
            ProductId::generate(),
            Sku::fromString('PROD-400'),
            'Cap',
            CategoryId::fromString('cat-accessories'),
            Money::fromFloat(15.00)
        );

        $product->archive();
        $this->assertTrue($product->status->isArchived());

        $this->expectException(DomainValidationException::class);
        $product->addVariant(
            VariantId::generate(),
            Sku::fromString('PROD-400-ONE'),
            Size::fromString('OneSize'),
            Color::fromName('Black')
        );
    }
}
