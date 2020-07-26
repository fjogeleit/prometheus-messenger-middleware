<?php

declare(strict_types=1);

namespace Prometheus\Messenger\Tests\UnitTest;

use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\Messenger\Exception\InvalidNameException;
use Prometheus\Messenger\PrometheusMiddleware;
use Prometheus\Messenger\Tests\Example\FooMessage;
use Prometheus\Messenger\Tests\Example\FooMessageHandler;
use Prometheus\Messenger\Tests\Factory\MessageBusFactory;
use Prometheus\Messenger\Tests\Factory\PrometheusCollectorRegistryFactory;

class InvalidNameExceptionTest extends TestCase
{
    /**
     * @var CollectorRegistry
     */
    private $collectorRegistry;

    protected function setUp(): void
    {
        $this->collectorRegistry = PrometheusCollectorRegistryFactory::create();
    }

    public function testInvalidCharacterInTheBusName(): void
    {
        $this->expectException(InvalidNameException::class);

        $messageBus = MessageBusFactory::create(
            [FooMessage::class => [new FooMessageHandler()]],
            new PrometheusMiddleware(
                $this->collectorRegistry,
                'invalid#hashtag#character',
                'valid_metric_name'
            )
        );

        $messageBus->dispatch(new FooMessage('Bar'));
    }

    public function testInvalidCharacterInTheMetricsName(): void
    {
        $this->expectException(InvalidNameException::class);

        $messageBus = MessageBusFactory::create(
            [FooMessage::class => [new FooMessageHandler()]],
            new PrometheusMiddleware(
                $this->collectorRegistry,
                'message_bus',
                'invalid.dot.in.metric_name'
            )
        );

        $messageBus->dispatch(new FooMessage('Bar'));
    }
}
