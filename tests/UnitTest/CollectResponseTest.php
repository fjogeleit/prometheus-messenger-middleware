<?php

declare(strict_types=1);

namespace PrometheusMiddleware\Tests\UnitTest;

use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use PrometheusMiddleware\PrometheusMiddleware;
use PrometheusMiddleware\Tests\Example\FooMessage;
use PrometheusMiddleware\Tests\Example\FooMessageHandler;
use PrometheusMiddleware\Tests\Example\LabelValueProvider\FooLabelValueProvider;
use PrometheusMiddleware\Tests\Factory\MessageBusFactory;
use PrometheusMiddleware\Tests\Factory\PrometheusCollectorRegistryFactory;
use Prometheus\RenderTextFormat;
use Symfony\Component\Messenger\Middleware\AddBusNameStampMiddleware;

class CollectResponseTest extends TestCase
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
                ['message', 'name', 'value'],
                new FooLabelValueProvider()
            )
        );

        $messageBus->dispatch(new FooMessage('Bar'));
        $messageBus->dispatch(new FooMessage('Bar'));
        $messageBus->dispatch(new FooMessage('Baz'));

        $renderer = new RenderTextFormat();
        $result = $renderer->render($this->collectorRegistry->getMetricFamilySamples());

        $this->assertStringContainsString(
            trim('
# HELP message_bus_messenger 
# TYPE message_bus_messenger counter
message_bus_messenger{message="PrometheusMiddleware\\\\Tests\\\\Example\\\\FooMessage",name="FooMessage",value="Bar"} 2
message_bus_messenger{message="PrometheusMiddleware\\\\Tests\\\\Example\\\\FooMessage",name="FooMessage",value="Baz"} 1'
            ),
            $result
        );
    }
}
