<?php

declare(strict_types=1);

namespace Prometheus\Messenger\Tests\UnitTest;

use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\Messenger\PrometheusMiddleware;
use Prometheus\Messenger\Tests\Example\FooMessage;
use Prometheus\Messenger\Tests\Example\FooMessageHandler;
use Prometheus\Messenger\Tests\Example\LabelValueProvider\FooLabelValueProvider;
use Prometheus\Messenger\Tests\Factory\MessageBusFactory;
use Prometheus\Messenger\Tests\Factory\PrometheusCollectorRegistryFactory;
use Prometheus\RenderTextFormat;

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
            new PrometheusMiddleware(
                $this->collectorRegistry,
                self::BUS_NAME,
                self::METRIC_NAME,
                '',
                ['command', 'name', 'value'],
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
message_bus_messenger{command="Prometheus\\\\Messenger\\\\Tests\\\\Example\\\\FooMessage",name="FooMessage",value="Bar"} 2
message_bus_messenger{command="Prometheus\\\\Messenger\\\\Tests\\\\Example\\\\FooMessage",name="FooMessage",value="Baz"} 1'
            ),
            $result
        );
    }
}
