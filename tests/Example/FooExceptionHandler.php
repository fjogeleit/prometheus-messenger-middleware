<?php

declare(strict_types=1);

namespace PrometheusMiddleware\Tests\Example;

class FooExceptionHandler
{
    /**
     * @param FooMessage $message
     *
     * @throws \Exception
     */
    public function __invoke(FooMessage $message): void
    {
        throw new \Exception('Boom');
    }
}
