<?php

declare(strict_types=1);

namespace App\Application\Catalog\Commands;

use App\Application\Catalog\Ports\EventBusPort;
use App\Domain\Catalog\Model\ValueObjects\ProductId;
use App\Domain\Catalog\Model\ValueObjects\ProductStatus;
use App\Domain\Catalog\Repositories\ProductRepositoryInterface;
use App\Domain\Catalog\Specifications\ProductCanBeActivatedSpecification;

final readonly class BulkChangeProductStatusCommandHandler
{
    public function __construct(
        private ProductRepositoryInterface $repository,
        private EventBusPort $eventBus,
        private ProductCanBeActivatedSpecification $canBeActivatedSpec
    ) {}

    /**
     * @return array{successCount: int, errors: array<string, string>}
     */
    public function handle(BulkChangeProductStatusCommand $command): array
    {
        $targetStatus = ProductStatus::from($command->targetStatus);
        $successCount = 0;
        $errors = [];

        foreach ($command->productIds as $idString) {
            try {
                $productId = ProductId::fromString($idString);
                $product = $this->repository->findById($productId);

                if (! $product) {
                    $errors[$idString] = "Product [{$idString}] not found.";

                    continue;
                }

                $product->transitionTo($targetStatus, $this->canBeActivatedSpec);
                $this->repository->save($product);
                $this->eventBus->dispatchAll($product->releaseEvents());

                $successCount++;
            } catch (\Throwable $e) {
                $errors[$idString] = $e->getMessage();
            }
        }

        return [
            'successCount' => $successCount,
            'errors' => $errors,
        ];
    }
}
