<?php

declare(strict_types=1);

namespace App\Application\Catalog\Commands;

use App\Application\Catalog\Ports\EventBusPort;
use App\Domain\Catalog\Model\ValueObjects\ProductId;
use App\Domain\Catalog\Model\ValueObjects\ProductStatus;
use App\Domain\Catalog\Repositories\ProductRepositoryInterface;
use App\Domain\Catalog\Specifications\ProductCanBeActivatedSpecification;
use InvalidArgumentException;

final readonly class ChangeProductStatusCommandHandler
{
    public function __construct(
        private ProductRepositoryInterface $repository,
        private EventBusPort $eventBus,
        private ProductCanBeActivatedSpecification $canBeActivatedSpec
    ) {}

    public function handle(ChangeProductStatusCommand $command): void
    {
        $productId = ProductId::fromString($command->productId);
        $product = $this->repository->findById($productId);

        if (! $product) {
            throw new InvalidArgumentException("Product with ID [{$command->productId}] not found.");
        }

        $targetStatus = ProductStatus::from($command->targetStatus);
        $product->transitionTo($targetStatus, $this->canBeActivatedSpec);

        $this->repository->save($product);
        $this->eventBus->dispatchAll($product->releaseEvents());
    }
}
