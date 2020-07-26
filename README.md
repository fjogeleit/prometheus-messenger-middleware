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
            messenger.bus.commands:
                middleware:
                    - 'PrometheusMiddleware\PrometheusMiddleware'
```
