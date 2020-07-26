<?php

declare(strict_types=1);

namespace PrometheusMiddleware\Tests\Example;

class FooMessageHandler
{
    public function __invoke(FooMessage $message): void
    {
    }
}
