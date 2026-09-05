<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Model;

use App\Domain\Catalog\Events\ProductCreated;
use App\Domain\Catalog\Events\ProductStatusChanged;
use App\Domain\Catalog\Events\VariantAddedToProduct;
use App\Domain\Catalog\Exceptions\DomainValidationException;
use App\Domain\Catalog\Model\ValueObjects\CategoryId;
use App\Domain\Catalog\Model\ValueObjects\Color;
use App\Domain\Catalog\Model\ValueObjects\Money;
use App\Domain\Catalog\Model\ValueObjects\ProductId;
use App\Domain\Catalog\Model\ValueObjects\ProductMedia;
use App\Domain\Catalog\Model\ValueObjects\ProductStatus;
use App\Domain\Catalog\Model\ValueObjects\Size;
use App\Domain\Catalog\Model\ValueObjects\Sku;
use App\Domain\Catalog\Model\ValueObjects\VariantId;
use App\Domain\Catalog\Specifications\ProductCanBeActivatedSpecification;

final class Product
{
    /** @var array<string, ProductVariant> */
    private array $variants = [];

    /** @var list<ProductMedia> */
    private array $media = [];

    /** @var list<object> */
    private array $recordedEvents = [];

    public function __construct(
        public readonly ProductId $id,
        public Sku $sku,
        public string $name,
        public ?CategoryId $categoryId,
        public Money $basePrice,
        public ProductStatus $status = ProductStatus::DRAFT
    ) {
        if (trim($this->name) === '') {
            throw DomainValidationException::forEmptyName();
        }
    }

    public static function create(
        ProductId $id,
        Sku $sku,
        string $name,
        ?CategoryId $categoryId,
        Money $basePrice,
        ProductStatus $status = ProductStatus::DRAFT
    ): self {
        $product = new self($id, $sku, $name, $categoryId, $basePrice, $status);
        $product->recordThat(new ProductCreated($id, $sku, $name));

        return $product;
    }

    public function updateDetails(string $name, Sku $sku, ?CategoryId $categoryId, Money $basePrice): void
    {
        $this->assertNotArchived();

        if (trim($name) === '') {
            throw DomainValidationException::forEmptyName();
        }

        $this->name = $name;
        $this->sku = $sku;
        $this->categoryId = $categoryId;
        $this->basePrice = $basePrice;
    }

    public function addVariant(
        VariantId $variantId,
        Sku $sku,
        Size $size,
        Color $color,
        ?Money $price = null
    ): ProductVariant {
        $this->assertNotArchived();

        foreach ($this->variants as $existingVariant) {
            if ($existingVariant->hasCombination($size, $color)) {
                throw DomainValidationException::forDuplicateVariant($size->value, $color->name);
            }
        }

        $variant = ProductVariant::create($variantId, $sku, $size, $color, $price);
        $this->variants[$variantId->value] = $variant;

        $this->recordThat(new VariantAddedToProduct($this->id, $variantId, $sku));

        return $variant;
    }

    public function removeVariant(VariantId $variantId): void
    {
        $this->assertNotArchived();
        unset($this->variants[$variantId->value]);
    }

    /**
     * @return array<string, ProductVariant>
     */
    public function variants(): array
    {
        return $this->variants;
    }

    /**
     * @param  list<ProductVariant>  $variants
     */
    public function setVariants(array $variants): void
    {
        $this->variants = [];
        foreach ($variants as $variant) {
            $this->variants[$variant->id->value] = $variant;
        }
    }

    public function attachMedia(ProductMedia $mediaItem): void
    {
        $this->assertNotArchived();
        $this->media[] = $mediaItem;
    }

    /**
     * @return list<ProductMedia>
     */
    public function media(): array
    {
        return $this->media;
    }

    /**
     * @param  list<ProductMedia>  $mediaList
     */
    public function setMedia(array $mediaList): void
    {
        $this->media = $mediaList;
    }

    public function activate(ProductCanBeActivatedSpecification $specification): void
    {
        if ($this->status->isActive()) {
            return;
        }

        $check = $specification->check($this);
        if (! $check['isSatisfied']) {
            throw DomainValidationException::forCannotActivate($check['reason'] ?? 'Specification failed');
        }

        $prev = $this->status;
        $this->status = ProductStatus::ACTIVE;
        $this->recordThat(new ProductStatusChanged($this->id, $prev, $this->status));
    }

    public function archive(): void
    {
        if ($this->status->isArchived()) {
            return;
        }

        $prev = $this->status;
        $this->status = ProductStatus::ARCHIVED;
        $this->recordThat(new ProductStatusChanged($this->id, $prev, $this->status));
    }

    public function setDraft(): void
    {
        if ($this->status->isDraft()) {
            return;
        }

        $prev = $this->status;
        $this->status = ProductStatus::DRAFT;
        $this->recordThat(new ProductStatusChanged($this->id, $prev, $this->status));
    }

    public function transitionTo(ProductStatus $targetStatus, ?ProductCanBeActivatedSpecification $spec = null): void
    {
        match ($targetStatus) {
            ProductStatus::ACTIVE => $this->activate($spec ?? new ProductCanBeActivatedSpecification),
            ProductStatus::ARCHIVED => $this->archive(),
            ProductStatus::DRAFT => $this->setDraft(),
        };
    }

    private function assertNotArchived(): void
    {
        if ($this->status->isArchived()) {
            throw DomainValidationException::forArchivedModification();
        }
    }

    protected function recordThat(object $event): void
    {
        $this->recordedEvents[] = $event;
    }

    /**
     * @return list<object>
     */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }
}
