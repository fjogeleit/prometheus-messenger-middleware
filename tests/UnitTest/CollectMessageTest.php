<?php

declare(strict_types=1);

namespace PrometheusMiddleware\Tests\UnitTest;

use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use PrometheusMiddleware\PrometheusMiddleware;
use PrometheusMiddleware\Tests\Example\FooExceptionHandler;
use PrometheusMiddleware\Tests\Example\FooMessage;
use PrometheusMiddleware\Tests\Example\FooMessageHandler;
use PrometheusMiddleware\Tests\Factory\MessageBusFactory;
use PrometheusMiddleware\Tests\Factory\PrometheusCollectorRegistryFactory;
use Symfony\Component\Messenger\Middleware\AddBusNameStampMiddleware;

class CollectMessageTest extends TestCase
{
    /**
     * @var CollectorRegistry
     */
    private $collectorRegistry;

    private const BUS_NAME = 'message_bus';
    private const METRIC_NAME = 'messenger';

    protected function setUp(): void
    {
        $this->collectorRegistry = PrometheusCollectorRegistryFactory::create();
    }

    public function testCollectMessageSuccessfully(): void
    {
        $messageBus = MessageBusFactory::create(
            [FooMessage::class => [new FooMessageHandler()]],
            new AddBusNameStampMiddleware(self::BUS_NAME),
            new PrometheusMiddleware($this->collectorRegistry, self::METRIC_NAME)
        );

        $messageBus->dispatch(new FooMessage('Bar'));

        $counter = $this->collectorRegistry->getCounter(self::BUS_NAME, self::METRIC_NAME);

        $this->assertEquals(self::BUS_NAME . '_' . self::METRIC_NAME, $counter->getName());
        $this->assertEquals(['message', 'label'], $counter->getLabelNames());

        $metrics = $this->collectorRegistry->getMetricFamilySamples();
        $samples = $metrics[0]->getSamples();

        $this->assertEquals(1, $samples[0]->getValue());
        $this->assertEquals([FooMessage::class, 'FooMessage'], $samples[0]->getLabelValues());
    }

    public function testCollectMessageExceptionSuccessfully(): void
    {
        $messageBus = MessageBusFactory::create(
            [FooMessage::class => [new FooExceptionHandler()]],
            new AddBusNameStampMiddleware(self::BUS_NAME),
            new PrometheusMiddleware($this->collectorRegistry, self::METRIC_NAME)
        );

        try {
            $messageBus->dispatch(new FooMessage('Bar'));
        } catch (\Throwable $exception) {
        }

        $counter = $this->collectorRegistry->getCounter(self::BUS_NAME, self::METRIC_NAME . '_error');

        $this->assertEquals(self::BUS_NAME . '_' . self::METRIC_NAME . '_error', $counter->getName());
        $this->assertEquals(['message', 'label'], $counter->getLabelNames());

        $metrics = $this->collectorRegistry->getMetricFamilySamples();
        $samples = $metrics[1]->getSamples();

        $this->assertEquals(1, $samples[0]->getValue());
        $this->assertEquals([FooMessage::class, 'FooMessage'], $samples[0]->getLabelValues());
    }
}
