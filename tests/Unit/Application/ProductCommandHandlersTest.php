<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use App\Application\Catalog\Commands\BulkChangeProductStatusCommand;
use App\Application\Catalog\Commands\BulkChangeProductStatusCommandHandler;
use App\Application\Catalog\Commands\ChangeProductStatusCommand;
use App\Application\Catalog\Commands\ChangeProductStatusCommandHandler;
use App\Application\Catalog\Commands\CreateProductCommand;
use App\Application\Catalog\Commands\CreateProductCommandHandler;
use App\Application\Catalog\Ports\EventBusPort;
use App\Domain\Catalog\Model\Product;
use App\Domain\Catalog\Model\ValueObjects\ProductId;
use App\Domain\Catalog\Model\ValueObjects\Sku;
use App\Domain\Catalog\Repositories\ProductRepositoryInterface;
use App\Domain\Catalog\Specifications\ProductCanBeActivatedSpecification;
use PHPUnit\Framework\TestCase;

final class InMemoryProductRepository implements ProductRepositoryInterface
{
    /** @var array<string, Product> */
    public array $storage = [];

    public function save(Product $product): void
    {
        $this->storage[$product->id->value] = $product;
    }

    public function findById(ProductId $id): ?Product
    {
        return $this->storage[$id->value] ?? null;
    }

    public function findBySku(Sku $sku): ?Product
    {
        foreach ($this->storage as $product) {
            if ($product->sku->equals($sku)) {
                return $product;
            }
        }

        return null;
    }

    public function delete(ProductId $id): void
    {
        unset($this->storage[$id->value]);
    }
}

final class DummyEventBus implements EventBusPort
{
    public array $dispatched = [];

    public function dispatch(object $event): void
    {
        $this->dispatched[] = $event;
    }

    public function dispatchAll(array $events): void
    {
        foreach ($events as $event) {
            $this->dispatched[] = $event;
        }
    }
}

final class ProductCommandHandlersTest extends TestCase
{
    private InMemoryProductRepository $repository;

    private DummyEventBus $eventBus;

    private ProductCanBeActivatedSpecification $spec;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryProductRepository;
        $this->eventBus = new DummyEventBus;
        $this->spec = new ProductCanBeActivatedSpecification;
    }

    public function test_create_product_command_handler(): void
    {
        $handler = new CreateProductCommandHandler($this->repository, $this->eventBus, $this->spec);

        $command = new CreateProductCommand(
            name: 'Cargo Pants',
            sku: 'CARGO-01',
            categoryId: 'cat-pants',
            basePrice: 79.90,
            currency: 'EUR',
            variants: [
                ['sku' => 'CARGO-01-32-KHA', 'size' => '32', 'color_name' => 'Khaki', 'color_hex' => '#F0E68C', 'price' => null],
                ['sku' => 'CARGO-01-34-BLK', 'size' => '34', 'color_name' => 'Black', 'color_hex' => '#000000', 'price' => 84.90],
            ],
            media: [
                ['url' => 'https://example.com/cargo.jpg', 'alt_text' => 'Cargo Pants Front', 'is_primary' => true],
            ],
            status: 'draft'
        );

        $productId = $handler->handle($command);

        $savedProduct = $this->repository->findById($productId);
        $this->assertNotNull($savedProduct);
        $this->assertSame('Cargo Pants', $savedProduct->name);
        $this->assertCount(2, $savedProduct->variants());
        $this->assertCount(1, $savedProduct->media());
        $this->assertTrue($savedProduct->status->isDraft());
        $this->assertNotEmpty($this->eventBus->dispatched);
    }

    public function test_change_status_and_bulk_change_status_handlers(): void
    {
        $createHandler = new CreateProductCommandHandler($this->repository, $this->eventBus, $this->spec);

        $p1Id = $createHandler->handle(new CreateProductCommand(
            name: 'P1',
            sku: 'SKU-P1',
            categoryId: 'cat-1',
            basePrice: 10.0,
            variants: [['sku' => 'V1', 'size' => 'M', 'color_name' => 'Blue']],
            status: 'draft'
        ));

        $p2Id = $createHandler->handle(new CreateProductCommand(
            name: 'P2',
            sku: 'SKU-P2',
            categoryId: 'cat-1',
            basePrice: 20.0,
            variants: [['sku' => 'V2', 'size' => 'L', 'color_name' => 'Green']],
            status: 'draft'
        ));

        // Single status change to active
        $statusHandler = new ChangeProductStatusCommandHandler($this->repository, $this->eventBus, $this->spec);
        $statusHandler->handle(new ChangeProductStatusCommand($p1Id->value, 'active'));
        $this->assertTrue($this->repository->findById($p1Id)->status->isActive());

        // Bulk status change to archived
        $bulkHandler = new BulkChangeProductStatusCommandHandler($this->repository, $this->eventBus, $this->spec);
        $result = $bulkHandler->handle(new BulkChangeProductStatusCommand([$p1Id->value, $p2Id->value], 'archived'));

        $this->assertSame(2, $result['successCount']);
        $this->assertTrue($this->repository->findById($p1Id)->status->isArchived());
        $this->assertTrue($this->repository->findById($p2Id)->status->isArchived());
    }
}
