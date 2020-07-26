<?php

declare(strict_types=1);

namespace Prometheus\Messenger\Tests\Example;

class FooMessageHandler
{
    public function __invoke(FooMessage $message): void
    {
    }
}
