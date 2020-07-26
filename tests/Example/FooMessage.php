<?php

declare(strict_types=1);

namespace Prometheus\Messenger\Tests\Example;

class FooMessage
{
    /**
     * @var string
     */
    private $bar;

    public function __construct(string $bar)
    {
        $this->bar = $bar;
    }

    public function getBar(): string
    {
        return $this->bar;
    }
}
