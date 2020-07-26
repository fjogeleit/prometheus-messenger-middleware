<?php

declare(strict_types=1);

namespace Prometheus\Messenger\Tests\UnitTest;

use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\Messenger\PrometheusMiddleware;
use Prometheus\Messenger\Tests\Example\FooExceptionHandler;
use Prometheus\Messenger\Tests\Example\FooMessage;
use Prometheus\Messenger\Tests\Example\FooMessageHandler;
use Prometheus\Messenger\Tests\Factory\MessageBusFactory;
use Prometheus\Messenger\Tests\Factory\PrometheusCollectorRegistryFactory;

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
            new PrometheusMiddleware($this->collectorRegistry, self::BUS_NAME, self::METRIC_NAME)
        );

        $messageBus->dispatch(new FooMessage('Bar'));

        $counter = $this->collectorRegistry->getCounter(self::BUS_NAME, self::METRIC_NAME);

        $this->assertEquals(self::BUS_NAME . '_' . self::METRIC_NAME, $counter->getName());
        $this->assertEquals(['command', 'label'], $counter->getLabelNames());

        $metrics = $this->collectorRegistry->getMetricFamilySamples();
        $samples = $metrics[0]->getSamples();

        $this->assertEquals(1, $samples[0]->getValue());
        $this->assertEquals([FooMessage::class, 'FooMessage'], $samples[0]->getLabelValues());
    }

    public function testCollectMessageExceptionSuccessfully(): void
    {
        $messageBus = MessageBusFactory::create(
            [FooMessage::class => [new FooExceptionHandler()]],
            new PrometheusMiddleware($this->collectorRegistry, self::BUS_NAME, self::METRIC_NAME)
        );

        try {
            $messageBus->dispatch(new FooMessage('Bar'));
        } catch (\Throwable $exception) {
        }

        $counter = $this->collectorRegistry->getCounter(self::BUS_NAME, self::METRIC_NAME . '_error');

        $this->assertEquals(self::BUS_NAME . '_' . self::METRIC_NAME . '_error', $counter->getName());
        $this->assertEquals(['command', 'label'], $counter->getLabelNames());

        $metrics = $this->collectorRegistry->getMetricFamilySamples();
        $samples = $metrics[1]->getSamples();

        $this->assertEquals(1, $samples[0]->getValue());
        $this->assertEquals([FooMessage::class, 'FooMessage'], $samples[0]->getLabelValues());
    }
}
