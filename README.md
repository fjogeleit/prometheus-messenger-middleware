# Prometheus Messenger Middleware

![CodeStyle](https://github.com/fjogeleit/prometheus-messenger-middleware/workflows/CodeStyle/badge.svg)

A Simple Middleware to Monitor your Symfony Messenger Component with two Prometheus Counter Metrics per MessageBus.

* Counter overall executed Messages
* Counter failed Messages

## Dependencies
* endclothing/prometheus_client_php
* symfony/messenger

## Installation

```bash
composer require fjogeleit/prometheus-messenger-middleware
```

## Usage

Configure this Middleware to your MessageBus

### Symfony Basic Example

#### Register your Services

```yaml
services:
    Prometheus\Storage\InMemory: ~
    Prometheus\CollectorRegistry: ['@Prometheus\Storage\InMemory']

    PrometheusMiddleware\PrometheusMiddleware: ~
```

#### Configure Middleware

```yaml
framework:
    messenger:
        buses:
            message.bus.commands:
                middleware:
                    - 'PrometheusMiddleware\PrometheusMiddleware'
```

Generated Example Counter Response with the default Labels "message" as full qualified MessageName and "label" as Message ClassName

```text
# HELP message_bus_commands_messenger Executed Messages
# TYPE message_bus_commands_messenger counter
message_bus_commands_messenger{message="PrometheusMiddleware\\\\Tests\\\\Example\\\\FooMessage",label="FooMessage"} 2
message_bus_commands_messenger{message="PrometheusMiddleware\\\\Tests\\\\Example\\\\FooMessage",label="BarMessage"} 1
```
## Advanced Usage

You can also configure your own Labels and provide the related Values

### Configure your own labels as additional service argument

```yaml
services:
    ...

    PrometheusMiddleware\PrometheusMiddleware:
      arguments:
        $labels: ['message', 'label','value']
        
```

### Create a LabelValueProvider

Create your own LabelValueProvider by implementing the `LabelValueProviderInterface`

```php
<?php

declare(strict_types=1);

namespace PrometheusMiddleware\Tests\Example\LabelValueProvider;

use PrometheusMiddleware\LabelValueProviderInterface;
use PrometheusMiddleware\Tests\Example\FooMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;

class FooLabelValueProvider implements LabelValueProviderInterface
{
    public function __invoke(Envelope $envelope, StackInterface $stack): array
    {
        /** @var FooMessage $message */
        $message = $envelope->getMessage();

        return [
            \get_class($message),
            substr(strrchr(get_class($message), '\\'), 1),
            $message->getBar()
        ];
    }
}
```
Add your new LabelValueProvider to your ServiceConfiguration

```yaml
services:
    ...
    PrometheusMiddleware\Tests\Example\FooLabelValueProvider: ~

    PrometheusMiddleware\PrometheusMiddleware:
      arguments:
        $labels: ['message', 'label','value']
        $labelValueProvider: '@PrometheusMiddleware\Tests\Example\FooLabelValueProvider'
```
You can also provide custom labels for the ErrorCounter with the `ErrorLabelValueProviderInterface`

```php
<?php

declare(strict_types=1);

namespace PrometheusMiddleware\Tests\Example\LabelValueProvider;

use PrometheusMiddleware\ErrorLabelValueProviderInterface;
use PrometheusMiddleware\Tests\Example\FooMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;

class FooExceptionLabelValueProvider implements ErrorLabelValueProviderInterface
{
    public function __invoke(Envelope $envelope, StackInterface $stack, \Throwable $exception): array
    {
        /** @var FooMessage $message */
        $message = $envelope->getMessage();

        return [
            \get_class($message),
            substr(strrchr(get_class($message), '\\'), 1),
            $exception->getMessage()
        ];
    }
}
```
And the updated ServiceConfiguration

```yaml
services:
    ...
    PrometheusMiddleware\Tests\Example\FooExceptionLabelValueProvider: ~

    PrometheusMiddleware\PrometheusMiddleware:
      arguments:
        $errorLabels: ['message', 'label','value']
        $errorLabelValueProvider: '@PrometheusMiddleware\Tests\Example\LabelValueProvider'
```

## Full PrometheusMiddleware API

| Property                        | Required         | Description
|---------------------------------|------------------|-------------------------------------------------------------------|
| CollectorRegistry $collectorRegistry     | required         | Underlying CollectorRegistry to generate and persist your metrics |
| string $metricName              | optional         | Default `message`, used as "name" for your `Counter`              |
| string $helpText                | optional         | Default `Executed Messages`, used as `Counter` help text  |
| array $labels                   | optional         | Default `['message', 'label']`, default provided `Counter` labels  |
| LabelValueProviderInterface $provider | optional         | Default `DefaultLabelValueProvider`, provides the `Counter` label related values  |
| string $errorHelpText           | optional         | Default `Failed Messages`, used as Error `Counter` help text  |
| array $errorLabels              | optional         | Default `['message', 'label']`, default provided Error `Counter` labels  |
| ErrorLabelValueProviderInterface $errorLabelValueProvider | optional         | Default `DefaultErrorLabelValueProvider`, provides the Error `Counter` label related values  |
