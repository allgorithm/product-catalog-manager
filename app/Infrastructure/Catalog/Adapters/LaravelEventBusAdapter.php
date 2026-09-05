<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog\Adapters;

use App\Application\Catalog\Ports\EventBusPort;
use Illuminate\Support\Facades\Event;

final class LaravelEventBusAdapter implements EventBusPort
{
    public function dispatch(object $event): void
    {
        Event::dispatch($event);
    }

    public function dispatchAll(array $events): void
    {
        foreach ($events as $event) {
            Event::dispatch($event);
        }
    }
}
