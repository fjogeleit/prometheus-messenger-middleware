<?php

declare(strict_types=1);

namespace PrometheusMiddleware\Tests\UnitTest;

use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use PrometheusMiddleware\PrometheusMiddleware;
use PrometheusMiddleware\Tests\Example\FooExceptionHandler;
use PrometheusMiddleware\Tests\Example\FooMessage;
use PrometheusMiddleware\Tests\Example\FooMessageHandler;
use PrometheusMiddleware\Tests\Example\LabelValueProvider\FooExceptionLabelValueProvider;
use PrometheusMiddleware\Tests\Example\LabelValueProvider\FooLabelValueProvider;
use PrometheusMiddleware\Tests\Factory\MessageBusFactory;
use PrometheusMiddleware\Tests\Factory\PrometheusCollectorRegistryFactory;
use Symfony\Component\Messenger\Middleware\AddBusNameStampMiddleware;

class CollectCustomLabelsTest extends TestCase
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
            new PrometheusMiddleware(
                $this->collectorRegistry,
                self::METRIC_NAME,
                '',
                ['command', 'name', 'value'],
                new FooLabelValueProvider()
            )
        );

        $messageBus->dispatch(new FooMessage('Bar'));

        $counter = $this->collectorRegistry->getCounter(self::BUS_NAME, self::METRIC_NAME);

        $this->assertEquals(self::BUS_NAME . '_' . self::METRIC_NAME, $counter->getName());
        $this->assertEquals(['command', 'name', 'value'], $counter->getLabelNames());

        $metrics = $this->collectorRegistry->getMetricFamilySamples();
        $samples = $metrics[0]->getSamples();

        $this->assertEquals(1, $samples[0]->getValue());
        $this->assertEquals([FooMessage::class, 'FooMessage', 'Bar'], $samples[0]->getLabelValues());
    }

    public function testCollectMessageExceptionSuccessfully(): void
    {
        $messageBus = MessageBusFactory::create(
            [FooMessage::class => [new FooExceptionHandler()]],
            new AddBusNameStampMiddleware(self::BUS_NAME),
            new PrometheusMiddleware(
                $this->collectorRegistry,
                self::METRIC_NAME,
                '',
                null,
                null,
                '',
                ['command', 'name', 'exception'],
                new FooExceptionLabelValueProvider()
            )
        );

        try {
            $messageBus->dispatch(new FooMessage('Bar'));
        } catch (\Throwable $exception) {
        }

        $counter = $this->collectorRegistry->getCounter(self::BUS_NAME, self::METRIC_NAME . '_error');

        $this->assertEquals(self::BUS_NAME . '_' . self::METRIC_NAME . '_error', $counter->getName());
        $this->assertEquals(['command', 'name', 'exception'], $counter->getLabelNames());

        $metrics = $this->collectorRegistry->getMetricFamilySamples();
        $samples = $metrics[1]->getSamples();

        $this->assertEquals(1, $samples[0]->getValue());
        $this->assertEquals([FooMessage::class, 'FooMessage', 'Boom'], $samples[0]->getLabelValues());
    }
}
