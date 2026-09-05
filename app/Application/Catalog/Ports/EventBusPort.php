<?php

declare(strict_types=1);

namespace App\Application\Catalog\Ports;

interface EventBusPort
{
    public function dispatch(object $event): void;

    /**
     * @param  list<object>  $events
     */
    public function dispatchAll(array $events): void;
}
