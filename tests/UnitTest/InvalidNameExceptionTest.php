<?php

declare(strict_types=1);

namespace PrometheusMiddleware\Tests\UnitTest;

use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use PrometheusMiddleware\Exception\InvalidNameException;
use PrometheusMiddleware\PrometheusMiddleware;
use PrometheusMiddleware\Tests\Example\FooMessage;
use PrometheusMiddleware\Tests\Example\FooMessageHandler;
use PrometheusMiddleware\Tests\Factory\MessageBusFactory;
use PrometheusMiddleware\Tests\Factory\PrometheusCollectorRegistryFactory;
use Symfony\Component\Messenger\Middleware\AddBusNameStampMiddleware;

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
            new AddBusNameStampMiddleware('invalid#hashtag#character'),
            new PrometheusMiddleware(
                $this->collectorRegistry,
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
            new AddBusNameStampMiddleware('message_bus'),
            new PrometheusMiddleware(
                $this->collectorRegistry,
                'invalid.dot.in.metric_name'
            )
        );

        $messageBus->dispatch(new FooMessage('Bar'));
    }
}
