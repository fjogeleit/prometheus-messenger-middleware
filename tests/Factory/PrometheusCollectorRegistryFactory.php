<?php

declare(strict_types=1);

namespace PrometheusMiddleware\Tests\Factory;

use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;

class PrometheusCollectorRegistryFactory
{
    public static function create()
    {
        return new CollectorRegistry(new InMemory());
    }
}
