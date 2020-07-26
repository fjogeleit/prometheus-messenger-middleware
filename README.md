# Prometheus Messenger Middleware

A Simple Middleware to Monitor your Symfony Messenger Component with Prometheus

## Dependencies
* endclothing/prometheus_client_php
* symfony/messenger

## Installation

```bash
composer require fjogeleit/prometheus-messenger-middleware
```

## Usage

Configure this Middleware to your MessageBus

### Symfony Example

#### Register your CollectorRegistry as Service

```yaml
services:
    Prometheus\CollectorRegistry: ['@Prometheus\Storage\Redis']
```

#### Configure as Middleware

```yaml
framework:
    messenger:
        buses:
            messenger.bus.commands:
                middleware:
                    - 'Prometheus\Messenger\PrometheusMiddleware'
```
