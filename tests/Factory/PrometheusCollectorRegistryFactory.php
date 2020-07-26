<?php

declare(strict_types=1);

namespace Prometheus\Messenger\Tests\Factory;

use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;

class PrometheusCollectorRegistryFactory
{
    public static function create()
    {
        return new CollectorRegistry(new InMemory());
    }
}
