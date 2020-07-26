<?php

declare(strict_types=1);

namespace PrometheusMiddleware\Tests\Factory;

use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;

class MessageBusFactory
{
    public static function create(array $routing, MiddlewareInterface ...$middleware)
    {
        return new MessageBus(array_merge(
            $middleware,
            [new HandleMessageMiddleware(new HandlersLocator($routing))]
        ));
    }
}
